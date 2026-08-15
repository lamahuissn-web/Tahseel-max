<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SPIKE: Zernio API adapter (https://zernio.com/api/v1).
 *
 * Throwaway validation of the WhatsApp integration path for Tahseel.
 * Not wired into the control center — driven by `php artisan zernio:test`.
 *
 * Key facts proven by this spike:
 *  - Free-form text works only inside the 24h customer-service window
 *    (requires an existing conversation = customer replied recently).
 *  - Outside the window a Meta-approved template is required.
 *  - Outbound is per-account (ZERNIO_ACCOUNT_ID = sandbox account for now).
 */
class ZernioService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $accountId;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('zernio.base_url'), '/');
        $this->apiKey = (string) config('zernio.api_key');
        $this->accountId = (string) config('zernio.account_id');
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
     * Sandbox session status (spike uses the shared sandbox number).
     */
    public function status(): array
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Zernio status check failed: '.$e->getMessage());

            return ['ok' => false, 'reachable' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Find the inbox conversation id for a phone (digits-only E.164 compare).
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
            Log::error('Zernio send failed: '.$e->getMessage());

            return ['ok' => false, 'messageId' => null, 'error' => $e->getMessage()];
        }
    }
}
