<?php

namespace App\Http\Controllers\Admin;

use App\Models\WhatsAppMessageLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives Zernio WhatsApp delivery webhooks.
 *
 * Events handled:
 *  - message.delivered → mark log as 'sent' + delivered_at
 *  - message.read → update delivered_at (already sent)
 *  - message.failed → mark log as 'failed' + error
 *
 * Security: X-Zernio-Signature (HMAC-SHA256) verification.
 * Dedup: event id via X-Zernio-Event-Id header.
 */
class ZernioWebhookController
{
    /**
     * Handle incoming Zernio webhook.
     *
     * Must return 2xx within 5 seconds (Zernio requirement).
     * Process heavy work async if needed.
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify signature
        if (! $this->verifySignature($request)) {
            Log::warning('Zernio webhook: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 2. Extract event
        $payload = $request->all();
        $eventType = $payload['event'] ?? $payload['type'] ?? '';
        $eventId = $request->header('X-Zernio-Event-Id')
            ?? $request->header('X-Late-Event-Id')
            ?? $payload['id'] ?? null;

        if (empty($eventType)) {
            return response()->json(['status' => 'ignored', 'reason' => 'no event type']);
        }

        // 3. Dedup by event ID (at-least-once delivery)
        if ($eventId && $this->isDuplicateEvent($eventId)) {
            return response()->json(['status' => 'duplicate', 'event_id' => $eventId]);
        }

        // 4. Process event
        $result = $this->processEvent($eventType, $payload);

        Log::info('Zernio webhook processed', [
            'event' => $eventType,
            'event_id' => $eventId,
            'result' => $result,
        ]);

        return response()->json(['status' => 'ok', 'event' => $eventType]);
    }

    /**
     * Verify X-Zernio-Signature using HMAC-SHA256.
     */
    protected function verifySignature(Request $request): bool
    {
        $secret = config('zernio.webhook_secret', '');
        if (empty($secret)) {
            // No secret configured — skip verification (dev/sandbox mode)
            return true;
        }

        $signature = $request->header('X-Zernio-Signature')
            ?? $request->header('X-Late-Signature');

        if (empty($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Check if event was already processed (simple DB dedup).
     */
    protected function isDuplicateEvent(string $eventId): bool
    {
        // Use a simple cache-based dedup (5 min TTL)
        $key = 'zernio_webhook_'.md5($eventId);

        if (cache()->has($key)) {
            return true;
        }

        cache()->put($key, true, 300);

        return false;
    }

    /**
     * Process a Zernio webhook event.
     */
    protected function processEvent(string $eventType, array $payload): array
    {
        return match ($eventType) {
            'message.delivered' => $this->handleDelivered($payload),
            'message.read' => $this->handleRead($payload),
            'message.failed' => $this->handleFailed($payload),
            default => ['action' => 'ignored', 'event' => $eventType],
        };
    }

    /**
     * Handle message.delivered — mark as sent with delivered_at.
     */
    protected function handleDelivered(array $payload): array
    {
        $messageId = $this->extractMessageId($payload);
        if (! $messageId) {
            return ['action' => 'skip', 'reason' => 'no message id'];
        }

        $log = WhatsAppMessageLog::where('provider_message_id', $messageId)->first();
        if (! $log) {
            Log::info('Zernio webhook: delivered for unknown message', [
                'message_id' => $messageId,
            ]);

            return ['action' => 'not_found', 'message_id' => $messageId];
        }

        $log->update([
            'status' => 'sent',
            'delivered_at' => now(),
        ]);

        return ['action' => 'delivered', 'log_id' => $log->id];
    }

    /**
     * Handle message.read — update delivered_at (already sent).
     */
    protected function handleRead(array $payload): array
    {
        $messageId = $this->extractMessageId($payload);
        if (! $messageId) {
            return ['action' => 'skip', 'reason' => 'no message id'];
        }

        $log = WhatsAppMessageLog::where('provider_message_id', $messageId)->first();
        if (! $log) {
            return ['action' => 'not_found', 'message_id' => $messageId];
        }

        // Only update delivered_at if not already set
        if (is_null($log->delivered_at)) {
            $log->update(['delivered_at' => now()]);
        }

        return ['action' => 'read', 'log_id' => $log->id];
    }

    /**
     * Handle message.failed — mark as failed with error.
     */
    protected function handleFailed(array $payload): array
    {
        $messageId = $this->extractMessageId($payload);
        if (! $messageId) {
            return ['action' => 'skip', 'reason' => 'no message id'];
        }

        $log = WhatsAppMessageLog::where('provider_message_id', $messageId)->first();
        if (! $log) {
            Log::warning('Zernio webhook: failed for unknown message', [
                'message_id' => $messageId,
            ]);

            return ['action' => 'not_found', 'message_id' => $messageId];
        }

        $error = $payload['error']['message']
            ?? $payload['error']['code']
            ?? $payload['message']
            ?? 'Zernio delivery failed';

        $log->update([
            'status' => 'failed',
            'error' => $error,
        ]);

        return ['action' => 'failed', 'log_id' => $log->id, 'error' => $error];
    }

    /**
     * Extract WhatsApp message ID (wamid) from webhook payload.
     */
    protected function extractMessageId(array $payload): ?string
    {
        // Zernio webhook payload structure
        return $payload['message']['id']
            ?? $payload['data']['messageId']
            ?? $payload['messageId']
            ?? null;
    }
}
