<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single Telegram Bot API HTTP layer.
 *
 * Replaces the 5 duplicated raw-curl blocks that existed across the helper
 * file and TelegramBotService. Uses Laravel's Http facade: proper SSL,
 * timeouts, retries — no curl boilerplate anywhere else.
 */
class TelegramApiClient
{
    protected const BASE_URL = 'https://api.telegram.org';

    protected ?string $token = null;

    public function __construct(?string $token = null)
    {
        // A null token means "read the current configured token per request".
        // This matters for the long-running poll command when settings change.
        $this->token = $token;
    }

    public function token(): ?string
    {
        return $this->token ?: TelegramConfig::token();
    }

    /** Send a text message. Returns decoded response array or false. */
    public function sendMessage(string $chatId, string $text, string $parseMode = 'HTML'): array|false
    {
        return $this->post('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]);
    }

    /** Send a file (multipart). Returns decoded response array or false. */
    public function sendDocument(string $filePath, string $caption = ''): array|false
    {
        if (!file_exists($filePath) || !$this->token() || !TelegramConfig::chatId()) {
            return false;
        }

        try {
            $response = Http::timeout(120)
                ->attach('document', fopen($filePath, 'r'), basename($filePath))
                ->post($this->url('sendDocument'), [
                    'chat_id' => TelegramConfig::chatId(),
                    'caption' => $caption,
                ]);
        } catch (\Throwable $e) {
            Log::error('Telegram sendDocument exception: ' . $e->getMessage());
            return false;
        }

        return $this->successfulResponse($response);
    }

    /** Poll for updates. Returns array of updates (empty on failure). */
    public function getUpdates(int $offset = 0): array
    {
        if (!$this->token()) {
            return [];
        }

        try {
            $response = Http::timeout(10)->get($this->url('getUpdates'), [
                'offset' => $offset,
                'timeout' => 5,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Telegram getUpdates connection error: ' . $e->getMessage());
            return [];
        } catch (\Throwable $e) {
            Log::error('Telegram getUpdates exception: ' . $e->getMessage());
            return [];
        }

        $result = $this->successfulResponse($response);
        return is_array($result) ? ($result['result'] ?? []) : [];
    }

    /** Answer an inline query. Returns true on success. */
    public function answerInlineQuery(string $inlineQueryId, array $results): bool
    {
        return $this->post('answerInlineQuery', [
            'inline_query_id' => $inlineQueryId,
            'results' => json_encode($results),
            'cache_time' => 5,
        ]) !== false;
    }

    protected function post(string $method, array $data): array|false
    {
        if (!$this->token()) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post($this->url($method), $data);
        } catch (ConnectionException $e) {
            Log::error('Telegram ' . $method . ' connection error: ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            Log::error('Telegram ' . $method . ' exception: ' . $e->getMessage());
            return false;
        }

        return $this->successfulResponse($response);
    }

    protected function successfulResponse(\Illuminate\Http\Client\Response $response): array|false
    {
        if ($response->failed() || $response->json('ok') !== true) {
            Log::error('Telegram API failed: ' . $response->body());
            return false;
        }

        return $response->json();
    }

    protected function url(string $method): string
    {
        return self::BASE_URL . '/bot' . $this->token() . '/' . $method;
    }
}
