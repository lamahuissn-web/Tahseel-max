<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Zernio WhatsApp adapter — supports both sandbox and real WABA.
 *
 * Sandbox: /whatsapp/sandbox/* endpoints (free, 50 msgs/day, shared number)
 * Real WABA: /whatsapp/{wabaId}/messages (Meta-approved templates + free-form in 24h window)
 *
 * Key WhatsApp rules:
 *  - Free-form text ONLY within 24h of customer's last message
 *  - Outside 24h window: Meta-approved template REQUIRED
 *  - Templates billed by Meta directly to your WABA payment method
 */
class ZernioService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $accountId;
    protected string $wabaId;
    protected bool $sandbox;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('zernio.base_url'), '/');
        $this->apiKey = (string) config('zernio.api_key');
        $this->accountId = (string) config('zernio.account_id');
        $this->wabaId = (string) config('zernio.waba_id');
        $this->sandbox = (bool) config('zernio.sandbox', true);
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Check WhatsApp connection status.
     *
     * Sandbox: GET /whatsapp/sandbox/sessions
     * Real WABA: GET /v1/whatsapp/accounts (list connected WABAs)
     */
    public function status(): array
    {
        try {
            if ($this->sandbox) {
                return $this->sandboxStatus();
            }

            return $this->wabaStatus();
        } catch (\Exception $e) {
            Log::error('Zernio status check failed: '.$e->getMessage());

            return ['ok' => false, 'reachable' => false, 'error' => $e->getMessage()];
        }
    }

    protected function sandboxStatus(): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->get("{$this->baseUrl}/whatsapp/sandbox/sessions");

        if (! $response->successful()) {
            return ['ok' => false, 'reachable' => true, 'error' => $response->body()];
        }

        $data = $response->json();
        $session = $data['sessions'][0] ?? null;

        return [
            'ok' => is_array($session) && ($session['status'] ?? null) === 'active',
            'reachable' => true,
            'sandboxNumber' => $data['sandboxNumber'] ?? null,
            'session' => $session,
        ];
    }

    protected function wabaStatus(): array
    {
        // Check WABA accounts endpoint
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->get("{$this->baseUrl}/whatsapp/accounts");

        if (! $response->successful()) {
            return ['ok' => false, 'reachable' => true, 'error' => $response->body()];
        }

        $data = $response->json();
        $accounts = $data['data'] ?? $data['accounts'] ?? [];

        // Find our WABA
        $waba = null;
        foreach ($accounts as $account) {
            if (($account['id'] ?? $account['_id'] ?? '') === $this->wabaId) {
                $waba = $account;
                break;
            }
        }

        if (! $waba) {
            return [
                'ok' => false,
                'reachable' => true,
                'error' => 'WABA '.$this->wabaId.' not found in connected accounts',
            ];
        }

        $connected = ($waba['status'] ?? '') === 'connected'
            || ($waba['accountStatus'] ?? '') === 'ACTIVE';

        return [
            'ok' => $connected,
            'reachable' => true,
            'wabaId' => $this->wabaId,
            'phone' => $waba['phoneNumber'] ?? $waba['displayPhoneNumber'] ?? null,
            'status' => $waba['status'] ?? $waba['accountStatus'] ?? 'unknown',
            'waba' => $waba,
        ];
    }

    /**
     * Find the inbox conversation id for a phone (digits-only E.164 compare).
     * Used for free-form text within the 24h customer-service window.
     */
    public function findConversation(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone);
        $cursor = null;

        do {
            $url = "{$this->baseUrl}/inbox/conversations";
            if ($cursor) {
                $url .= '?cursor='.$cursor;
            }

            $response = Http::withHeaders($this->headers())->timeout(10)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            foreach (($data['data'] ?? []) as $conversation) {
                $participant = preg_replace('/\D+/', '', (string) ($conversation['participantId'] ?? ''));

                if ($participant === $phone) {
                    return $conversation['id'];
                }
            }

            $cursor = $data['pagination']['nextCursor'] ?? null;
        } while ($cursor);

        return null;
    }

    /**
     * Send free-form text inside the 24h customer-service window.
     *
     * Works when the customer has messaged us within the last 24 hours.
     * Outside this window, use sendTemplate() instead.
     *
     * @return array{ok: bool, messageId: ?string, error: ?string}
     */
    public function sendText(string $phone, string $message): array
    {
        $conversationId = $this->findConversation($phone);

        if (! $conversationId) {
            return [
                'ok' => false,
                'messageId' => null,
                'error' => 'No open 24h window/conversation for '.$phone
                    .' — a Meta-approved template is required to open one.',
            ];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(20)
                ->post("{$this->baseUrl}/inbox/conversations/{$conversationId}/messages", [
                    'accountId' => $this->accountId,
                    'message' => $message,
                ]);

            $data = $response->json();

            if (! $response->successful()) {
                return ['ok' => false, 'messageId' => null, 'error' => $data['error'] ?? $response->body()];
            }

            return ['ok' => true, 'messageId' => $data['data']['messageId'] ?? null, 'error' => null];
        } catch (\Exception $e) {
            Log::error('Zernio sendText failed: '.$e->getMessage());

            return ['ok' => false, 'messageId' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a Meta-approved WhatsApp template message.
     *
     * Used for business-initiated messages (reminders, receipts outside 24h window).
     * Templates must be pre-approved by Meta via the WABA.
     *
     * @param  string  $phone  E.164 phone number (e.g. "+96170781562")
     * @param  string  $templateName  Meta-approved template name (e.g. "payment_receipt")
     * @param  string  $language  Template language code (e.g. "ar", "en")
     * @param  array  $variables  Template body variables (e.g. ["10", "2026-08-17"])
     * @return array{ok: bool, messageId: ?string, error: ?string}
     */
    public function sendTemplate(string $phone, string $templateName, string $language = 'ar', array $variables = []): array
    {
        if ($this->sandbox) {
            return [
                'ok' => false,
                'messageId' => null,
                'error' => 'Template sending requires real WABA mode (ZERNIO_SANDBOX=false)',
            ];
        }

        if (empty($this->wabaId)) {
            return [
                'ok' => false,
                'messageId' => null,
                'error' => 'ZERNIO_WABA_ID not configured',
            ];
        }

        $cleanPhone = preg_replace('/^\+/', '', $phone);

        // Build template body parameters
        $bodyParams = [];
        foreach ($variables as $variable) {
            $bodyParams[] = ['type' => 'text', 'text' => (string) $variable];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $cleanPhone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (! empty($bodyParams)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ],
            ];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(20)
                ->post("{$this->baseUrl}/whatsapp/{$this->wabaId}/messages", $payload);

            $data = $response->json();

            if (! $response->successful()) {
                $error = $data['error']['message'] ?? $data['message'] ?? $data['error'] ?? $response->body();
                Log::warning('Zernio template send failed', [
                    'phone' => substr($phone, 0, 6).'***',
                    'template' => $templateName,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return ['ok' => false, 'messageId' => null, 'error' => (string) $error];
            }

            $messageId = $data['messages'][0]['id'] ?? $data['data']['messageId'] ?? null;

            return ['ok' => true, 'messageId' => $messageId, 'error' => null];
        } catch (\Exception $e) {
            Log::error('Zernio template send failed: '.$e->getMessage());

            return ['ok' => false, 'messageId' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Auto-detect whether to send free-form text or template based on 24h window.
     *
     * Tries free-form first; if no conversation exists, falls back to template.
     *
     * @param  string  $phone
     * @param  string  $message  Free-form message text
     * @param  string|null  $templateName  Template to use if outside 24h window
     * @param  string  $language
     * @param  array  $variables
     * @return array{ok: bool, messageId: ?string, method: string, error: ?string}
     */
    public function sendSmart(string $phone, string $message, ?string $templateName = null, string $language = 'ar', array $variables = []): array
    {
        // Try free-form text first (within 24h window)
        $result = $this->sendText($phone, $message);

        if ($result['ok']) {
            return [...$result, 'method' => 'text'];
        }

        // No open window — try template if available
        if ($templateName && ! $this->sandbox) {
            $templateResult = $this->sendTemplate($phone, $templateName, $language, $variables);

            if ($templateResult['ok']) {
                return [...$templateResult, 'method' => 'template'];
            }

            return [
                'ok' => false,
                'messageId' => null,
                'method' => 'none',
                'error' => 'Free-form failed: '.$result['error'].' | Template failed: '.$templateResult['error'],
            ];
        }

        return [
            'ok' => false,
            'messageId' => null,
            'method' => 'none',
            'error' => $result['error'],
        ];
    }
}
