<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppService
{
    protected string $apiVersion = 'v22.0';
    protected string $baseUrl;
    protected string $phoneNumberId;
    protected string $accessToken;
    protected string $wabaId;

    public function __construct()
    {
        $this->baseUrl = 'https://graph.facebook.com/' . $this->apiVersion;
        $this->phoneNumberId = config('app.meta_whatsapp_phone_number_id', env('META_PHONE_NUMBER_ID', ''));
        $this->accessToken = config('app.meta_whatsapp_token', env('META_API_TOKEN', ''));
        $this->wabaId = config('app.meta_whatsapp_waba_id', env('META_WABA_ID', ''));
    }

    /**
     * Check if Meta API is configured (has credentials).
     */
    public function isConfigured(): bool
    {
        return !empty($this->phoneNumberId) && !empty($this->accessToken);
    }

    /**
     * Test connectivity by fetching the phone number info from Meta.
     * Returns status array similar to WhatsAppService::status().
     */
    public function status(): array
    {
        if (!$this->isConfigured()) {
            return [
                'reachable' => false,
                'connected' => false,
                'phone' => null,
                'status' => 'not_configured',
                'message' => 'Meta WhatsApp API is not configured. Set META_PHONE_NUMBER_ID and META_API_TOKEN in .env',
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->get("{$this->baseUrl}/{$this->phoneNumberId}");

            $data = $response->json();
            $statusCode = $response->status();

            if (!$response->successful()) {
                return [
                    'reachable' => false,
                    'connected' => false,
                    'phone' => null,
                    'status' => 'error',
                    'message' => $data['error']['message'] ?? ('Meta API HTTP ' . $statusCode),
                ];
            }

            // Successful response means the token + phone number ID are valid
            $displayPhone = $data['display_phone_number'] ?? $this->phoneNumberId;
            $verifiedName = $data['verified_name'] ?? '';

            return [
                'reachable' => true,
                'connected' => true,
                'phone' => $displayPhone,
                'status' => 'ready',
                'message' => "Meta API ready — {$verifiedName} ({$displayPhone})",
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp status check failed: ' . $e->getMessage());
            return [
                'reachable' => false,
                'connected' => false,
                'phone' => null,
                'status' => 'unreachable',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a test text message to a specific phone number.
     * Text messages only work within a 24-hour customer service window.
     * For initial testing, you can reply to a message from that number first.
     */
    public function sendText(string $phone, string $message): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Meta WhatsApp API is not configured.',
            ];
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone)) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            $data = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && isset($data['messages'][0]['id'])) {
                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'],
                ];
            }

            $errorMsg = $data['error']['message'] ?? $data['error']['error_user_msg'] ?? ('Meta API HTTP ' . $statusCode);
            Log::warning('Meta WhatsApp sendText failed', [
                'phone' => substr($cleanPhone, 0, 5) . '***',
                'status_code' => $statusCode,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'error' => $errorMsg,
                'status_code' => $statusCode,
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp sendText exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a pre-approved template message to a specific phone number.
     * This is the primary method for Utility messages (bill reminders).
     * Templates must be registered in Meta Business Platform first.
     */
    public function sendTemplate(
        string $phone,
        string $templateName,
        string $language = 'ar',
        array $parameters = []
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Meta WhatsApp API is not configured.',
            ];
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone)) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanPhone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language,
                ],
            ],
        ];

        // Add body parameters if provided
        if (!empty($parameters)) {
            $components = [];
            $bodyParams = [];
            foreach ($parameters as $param) {
                $bodyParams[] = ['type' => 'text', 'text' => $param];
            }
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParams,
            ];
            $body['template']['components'] = $components;
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $body);

            $data = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && isset($data['messages'][0]['id'])) {
                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'],
                ];
            }

            $errorMsg = $data['error']['message'] ?? $data['error']['error_user_msg'] ?? ('Meta API HTTP ' . $statusCode);
            Log::warning('Meta WhatsApp sendTemplate failed', [
                'phone' => substr($cleanPhone, 0, 5) . '***',
                'template' => $templateName,
                'status_code' => $statusCode,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'error' => $errorMsg,
                'status_code' => $statusCode,
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp sendTemplate exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get a list of registered templates from Meta WABA.
     */
    public function getTemplates(): array
    {
        if (!$this->isConfigured() || empty($this->wabaId)) {
            return [
                'success' => false,
                'error' => 'Meta WhatsApp API not configured or WABA ID missing.',
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->get("{$this->baseUrl}/{$this->wabaId}/message_templates", [
                    'fields' => 'name,status,category,language',
                ]);

            $data = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $data['error']['message'] ?? 'Failed to fetch templates',
                ];
            }

            return [
                'success' => true,
                'templates' => $data['data'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Meta WhatsApp getTemplates failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
