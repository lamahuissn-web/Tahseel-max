<?php

namespace App\Services;

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
        $this->sessionId = config('app.openwa_session_id', env('OPENWA_SESSION_ID', ''));
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

            if (!$data || isset($data['error'])) {
                return ['connected' => false, 'phone' => null];
            }

            $status = $data['status'] ?? '';
            $connected = in_array(strtolower($status), ['connected', 'ready']);

            return [
                'connected' => $connected,
                'phone' => $data['phoneNumber'] ?? $data['phone'] ?? null,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            Log::error('OpenWA status check failed: ' . $e->getMessage());
            return ['connected' => false, 'phone' => null];
        }
    }

    public function getQR()
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(5)
                ->get("{$this->baseUrl}/sessions/{$this->sessionId}/qr");

            $data = $response->json();

            if (!$data || isset($data['error'])) {
                return ['qr' => null, 'connected' => false];
            }

            $qrCode = $data['qrCode'] ?? $data['qr'] ?? null;
            $status = $data['status'] ?? '';
            $connected = in_array(strtolower($status), ['connected', 'ready']);

            return [
                'qr' => $qrCode,
                'connected' => $connected,
            ];
        } catch (\Exception $e) {
            Log::error('OpenWA QR fetch failed: ' . $e->getMessage());
            return ['qr' => null, 'connected' => false];
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

            // OpenWA known bug: returns HTTP 500 with {"statusCode":500,"message":"Internal server error"}
            // even when the message IS sent successfully. Treat as success since messages are delivered.
            if ($statusCode === 500 && isset($data['statusCode']) && $data['statusCode'] === 500) {
                Log::warning('OpenWA returned 500 but message may have been sent', [
                    'phone' => substr($phone, 0, 6) . '***',
                    'response' => $data,
                ]);
                return ['success' => true, 'warning' => 'OpenWA returned 500 (message likely sent)'];
            }

            if (isset($data['messageId']) || (isset($data['success']) && $data['success'])) {
                return ['success' => true];
            }

            $errorMsg = $data['error']['message'] ?? $data['message'] ?? 'Unknown error';
            return ['success' => false, 'error' => $errorMsg];
        } catch (\Exception $e) {
            Log::error('OpenWA send failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLogs($limit = 50)
    {
        return [];
    }
}
