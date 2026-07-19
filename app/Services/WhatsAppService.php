<?php

namespace App\Services;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $sessionId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.openwa_api_url', env('OPENWA_API_URL', 'http://192.168.0.75:2785/api')), '/');
        $this->apiKey = config('app.openwa_api_key', env('OPENWA_API_KEY', ''));
        $sessionOverride = AppConfig::where('key', 'whatsapp_openwa_session_id')->value('value');
        $this->sessionId = $sessionOverride ?: config('app.openwa_session_id', env('OPENWA_SESSION_ID', ''));
    }

    protected function headers()
    {
        return [
            'X-API-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function status()
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(5)
                ->get("{$this->baseUrl}/sessions/{$this->sessionId}");

            $data = $response->json();
            $statusCode = $response->status();

            if (!$response->successful()) {
                return [
                    'reachable' => false,
                    'connected' => false,
                    'phone' => null,
                    'status' => 'unreachable',
                    'message' => $data['message'] ?? ('OpenWA HTTP ' . $statusCode),
                ];
            }

            if (!$data || isset($data['error'])) {
                return [
                    'reachable' => true,
                    'connected' => false,
                    'phone' => null,
                    'status' => $data['status'] ?? 'unknown',
                    'message' => $data['error']['message'] ?? $data['message'] ?? 'Session data unavailable',
                ];
            }

            $status = (string) ($data['status'] ?? 'unknown');
            $connected = in_array(strtolower($status), ['connected', 'ready']);

            return [
                'reachable' => true,
                'connected' => $connected,
                'phone' => $data['phoneNumber'] ?? $data['phone'] ?? null,
                'status' => $status,
                'message' => $data['message'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('OpenWA status check failed: ' . $e->getMessage());
            return [
                'reachable' => false,
                'connected' => false,
                'phone' => null,
                'status' => 'unreachable',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getQR()
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(5)
                ->get("{$this->baseUrl}/sessions/{$this->sessionId}/qr");

            $data = $response->json();
            $statusCode = $response->status();

            if (!$response->successful()) {
                $message = (string) ($data['message'] ?? ('OpenWA HTTP ' . $statusCode));
                $normalized = strtolower($message);

                if (str_contains($normalized, 'already authenticated') || str_contains($normalized, 'no qr code needed')) {
                    return [
                        'reachable' => true,
                        'qr' => null,
                        'connected' => true,
                        'status' => 'ready',
                        'message' => $message,
                    ];
                }

                return [
                    'reachable' => false,
                    'qr' => null,
                    'connected' => false,
                    'status' => 'unreachable',
                    'message' => $message,
                ];
            }

            if (!$data || isset($data['error'])) {
                return [
                    'reachable' => true,
                    'qr' => null,
                    'connected' => false,
                    'status' => $data['status'] ?? 'unknown',
                    'message' => $data['error']['message'] ?? $data['message'] ?? 'QR not available',
                ];
            }

            $qrCode = $data['qrCode'] ?? $data['qr'] ?? null;
            $status = (string) ($data['status'] ?? 'unknown');
            $connected = in_array(strtolower($status), ['connected', 'ready']);

            return [
                'reachable' => true,
                'qr' => $qrCode,
                'connected' => $connected,
                'status' => $status,
                'message' => $data['message'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('OpenWA QR fetch failed: ' . $e->getMessage());
            return [
                'reachable' => false,
                'qr' => null,
                'connected' => false,
                'status' => 'unreachable',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function send($phone, $message)
    {
        try {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $chatId = "{$cleanPhone}@c.us";

            $response = Http::withHeaders($this->headers())
                ->timeout(30)
                ->post("{$this->baseUrl}/sessions/{$this->sessionId}/messages/send-text", [
                    'chatId' => $chatId,
                    'text' => $message,
                ]);

            $data = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && (isset($data['messageId']) || (isset($data['success']) && $data['success']))) {
                return [
                    'success' => true,
                    'message_id' => $data['messageId'] ?? null,
                ];
            }

            $isGeneric500 = $statusCode === 500
                && (($data['statusCode'] ?? null) === 500)
                && (($data['message'] ?? '') === 'Internal server error');

            if ($isGeneric500) {
                $sessionStatus = $this->status();
                if (($sessionStatus['connected'] ?? false) === true) {
                    Log::warning('OpenWA returned generic 500 on healthy session; treating as delivered', [
                        'phone' => substr($phone, 0, 6) . '***',
                        'status_code' => $statusCode,
                        'session_status' => $sessionStatus,
                        'response' => $data,
                    ]);

                    return [
                        'success' => true,
                        'warning' => 'OpenWA returned generic 500 on healthy session',
                        'assumed_delivery' => true,
                    ];
                }
            }

            $errorMsg = $data['error']['message'] ?? $data['message'] ?? ('OpenWA HTTP ' . $statusCode);
            Log::warning('OpenWA send returned failure', [
                'phone' => substr($phone, 0, 6) . '***',
                'status_code' => $statusCode,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'error' => $errorMsg,
                'status_code' => $statusCode,
            ];
        } catch (\Exception $e) {
            Log::error('OpenWA send failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function revokeSession(): array
    {
        $attempts = [];
        $oldSessionId = $this->sessionId;
        $sessionName = 'tahseel-max-recovery';

        try {
            $currentResponse = Http::withHeaders($this->headers())
                ->timeout(8)
                ->get("{$this->baseUrl}/sessions/{$oldSessionId}");
            $currentData = $currentResponse->json();
            $currentData = is_array($currentData) ? $currentData : [];
            $sessionName = $currentData['name'] ?? $sessionName;

            $attempts[] = [
                'action' => 'get-current-session',
                'status_code' => $currentResponse->status(),
                'message' => $currentData['status'] ?? $currentData['message'] ?? 'current session checked',
            ];
        } catch (\Exception $e) {
            $attempts[] = [
                'action' => 'get-current-session',
                'status_code' => null,
                'message' => $e->getMessage(),
            ];
        }

        try {
            $deleteResponse = Http::withHeaders($this->headers())
                ->timeout(20)
                ->delete("{$this->baseUrl}/sessions/{$oldSessionId}");
            $deleteData = $deleteResponse->json();
            $deleteData = is_array($deleteData) ? $deleteData : [];

            $attempts[] = [
                'action' => 'delete-session',
                'status_code' => $deleteResponse->status(),
                'message' => $deleteData['message'] ?? $deleteData['status'] ?? ('OpenWA HTTP ' . $deleteResponse->status()),
            ];

            if (!$deleteResponse->successful()) {
                return [
                    'success' => false,
                    'message' => $deleteData['message'] ?? 'OpenWA failed to delete the old session.',
                    'qr_required' => false,
                    'attempts' => $attempts,
                    'status' => $this->status(),
                ];
            }
        } catch (\Exception $e) {
            $attempts[] = [
                'action' => 'delete-session',
                'status_code' => null,
                'message' => $e->getMessage(),
            ];

            return [
                'success' => false,
                'message' => 'OpenWA delete session failed: ' . $e->getMessage(),
                'qr_required' => false,
                'attempts' => $attempts,
                'status' => $this->status(),
            ];
        }

        try {
            $createResponse = Http::withHeaders($this->headers())
                ->timeout(20)
                ->post("{$this->baseUrl}/sessions", [
                    'name' => $sessionName,
                ]);
            $createData = $createResponse->json();
            $createData = is_array($createData) ? $createData : [];
            $newSessionId = $createData['id'] ?? $createData['sessionId'] ?? null;

            $attempts[] = [
                'action' => 'create-session',
                'status_code' => $createResponse->status(),
                'message' => $newSessionId ?: ($createData['message'] ?? 'session creation response did not include id'),
            ];

            if (!$createResponse->successful() || !$newSessionId) {
                return [
                    'success' => false,
                    'message' => $createData['message'] ?? 'Old session was deleted, but OpenWA failed to create a replacement session. Create a new session in OpenWA dashboard and update Tahseel session ID.',
                    'qr_required' => false,
                    'attempts' => $attempts,
                    'status' => [
                        'reachable' => true,
                        'connected' => false,
                        'phone' => null,
                        'status' => 'session_deleted',
                        'message' => 'Old session deleted; replacement creation failed.',
                    ],
                ];
            }

            AppConfig::updateOrCreate(
                ['key' => 'whatsapp_openwa_session_id'],
                ['value' => $newSessionId]
            );

            $this->sessionId = $newSessionId;
            $this->startSessionAfterRevoke();
            sleep(2);

            return [
                'success' => true,
                'action' => 'delete-and-create-session',
                'message' => 'WhatsApp session revoked. Scan the new QR code to connect another phone.',
                'qr_required' => true,
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
                'status' => $this->status(),
                'qr' => $this->getQR(),
                'attempts' => $attempts,
            ];
        } catch (\Exception $e) {
            $attempts[] = [
                'action' => 'create-session',
                'status_code' => null,
                'message' => $e->getMessage(),
            ];

            return [
                'success' => false,
                'message' => 'Old session may be deleted, but creating the replacement session failed: ' . $e->getMessage(),
                'qr_required' => false,
                'attempts' => $attempts,
                'status' => [
                    'reachable' => true,
                    'connected' => false,
                    'phone' => null,
                    'status' => 'session_deleted',
                    'message' => 'Old session may be deleted; replacement creation failed.',
                ],
            ];
        }
    }

    public function restartSession(): array
    {
        try {
            Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->baseUrl}/sessions/{$this->sessionId}/stop");

            sleep(2);

            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->baseUrl}/sessions/{$this->sessionId}/start");

            if ($response->successful()) {
                return ['success' => true, 'message' => trans('clients.whatsapp_restarted')];
            }

            $data = $response->json();
            $data = is_array($data) ? $data : [];

            return ['success' => false, 'message' => $data['message'] ?? 'Failed to restart session'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function startSessionAfterRevoke(): void
    {
        try {
            Http::withHeaders($this->headers())
                ->timeout(10)
                ->post("{$this->baseUrl}/sessions/{$this->sessionId}/start");
        } catch (\Exception $e) {
            Log::warning('OpenWA start after revoke failed: ' . $e->getMessage());
        }
    }

    private function looksAlreadyLoggedOut(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'not authenticated')
            || str_contains($message, 'not logged')
            || str_contains($message, 'logged out')
            || str_contains($message, 'qr')
            || str_contains($message, 'scan');
    }

    public function getLogs($limit = 50)
    {
        return [];
    }
}
