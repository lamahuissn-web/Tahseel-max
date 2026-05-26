<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url') ? 'http://127.0.0.1:3000' : 'http://127.0.0.1:3000';
    }

    public function status()
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/status");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp service status check failed: ' . $e->getMessage());
            return ['connected' => false, 'phone' => null];
        }
    }

    public function getQR()
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/qr");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp service QR fetch failed: ' . $e->getMessage());
            return ['qr' => null, 'connected' => false];
        }
    }

    public function send($phone, $message)
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/send", [
                'phone' => $phone,
                'message' => $message,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp send failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLogs($limit = 50)
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/logs", ['limit' => $limit]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp logs fetch failed: ' . $e->getMessage());
            return [];
        }
    }
}
