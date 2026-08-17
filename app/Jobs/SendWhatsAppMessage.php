<?php

namespace App\Jobs;

use App\Models\Admin\Invoice;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppBatchService;
use App\Services\WhatsApp\WhatsAppQueueState;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendWhatsAppMessage implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 0;

    public int $timeout = 650;

    public int $uniqueFor = 172800;

    public readonly int $retryDeadline;

    public readonly string $attemptToken;

    public function __construct(
        public readonly int $messageLogId,
        ?int $retryDeadline = null,
        ?string $attemptToken = null
    ) {
        $this->retryDeadline = $retryDeadline ?? now()->addDay()->getTimestamp();
        $this->attemptToken = $attemptToken ?? (string) Str::uuid();
    }

    public function uniqueId(): string
    {
        return (string) $this->messageLogId;
    }

    public function backoff(): array
    {
        return [60, 120, 300, 600];
    }

    public function retryUntil(): DateTimeInterface
    {
        return Carbon::createFromTimestamp($this->retryDeadline);
    }

    public function handle(WhatsAppService $service): void
    {
        $messageLog = WhatsAppMessageLog::query()->with('batch')->find($this->messageLogId);

        if (! $messageLog || in_array($messageLog->status, ['sent', 'cancelled'], true)) {
            return;
        }

        if ($messageLog->batch && (
            $messageLog->batch->archived_at !== null
            || in_array($messageLog->batch->status, ['cancelling', 'cancelled'], true)
        )) {
            return;
        }

        if (app(WhatsAppQueueState::class)->paused()) {
            $this->release(60);

            return;
        }

        if ($messageLog->status === 'sending') {
            $this->resolveAmbiguousDelivery($messageLog);

            return;
        }

        if (! (bool) ($service->status()['connected'] ?? false)) {
            $this->retryLater($messageLog, 'WhatsApp is temporarily disconnected', 300);

            return;
        }

        $claimed = app(WhatsAppBatchService::class)->claimForDelivery(
            $messageLog->id,
            $this->attemptToken
        );
        if (! $claimed) {
            return;
        }

        $this->deliver($claimed, $service);
    }

    private function deliver(WhatsAppMessageLog $messageLog, WhatsAppService $service): void
    {
        $sendResult = $service->send($messageLog->phone, $messageLog->message, [
            'rate_context' => ['source' => 'database-queue'],
        ]);

        if (($sendResult['rate_limited'] ?? false) === true) {
            $this->retryRateLimitedMessage($messageLog, $sendResult);

            return;
        }

        if (($sendResult['success'] ?? false) !== true) {
            if (($sendResult['ambiguous_delivery'] ?? false) === true) {
                $this->markFailed(
                    $messageLog,
                    'Ambiguous OpenWA transport result; automatic resend suppressed to avoid a duplicate. '
                        .($sendResult['error'] ?? '')
                );

                return;
            }

            if ($this->isPermanentFailure($sendResult)) {
                $this->markFailed($messageLog, $sendResult['error'] ?? 'Permanent OpenWA failure');

                return;
            }

            $this->recordTransientFailure($messageLog, $sendResult);
        }

        // Store provider message ID for webhook delivery tracking (Zernio wamid)
        if (! empty($sendResult['message_id']) && is_null($messageLog->provider_message_id)) {
            $messageLog->update(['provider_message_id' => $sendResult['message_id']]);
        }

        if (! app(WhatsAppBatchService::class)->transitionClaimedMessage(
            $messageLog->id,
            $this->attemptToken,
            'sent'
        )) {
            return;
        }
        if ($messageLog->template_type !== 'receipt') {
            $this->markInvoicesNotified($messageLog);
        }
    }

    private function retryRateLimitedMessage(WhatsAppMessageLog $messageLog, array $sendResult): void
    {
        $this->retryLater(
            $messageLog,
            $sendResult['error'] ?? 'Paused by WhatsApp safety limiter',
            (int) ($sendResult['retry_after_seconds'] ?? 60)
        );
    }

    private function recordTransientFailure(WhatsAppMessageLog $messageLog, array $sendResult): never
    {
        $error = $sendResult['error'] ?? 'OpenWA send failed';
        app(WhatsAppBatchService::class)->transitionClaimedMessage(
            $messageLog->id,
            $this->attemptToken,
            'pending',
            $error
        );

        throw new RuntimeException($error);
    }

    private function isPermanentFailure(array $sendResult): bool
    {
        $statusCode = (int) ($sendResult['status_code'] ?? 0);

        return $statusCode >= 400
            && $statusCode < 500
            && ! in_array($statusCode, [408, 409, 425, 429], true);
    }

    private function resolveAmbiguousDelivery(WhatsAppMessageLog $messageLog): void
    {
        if ($messageLog->updated_at?->greaterThan(now()->subMinutes(10))) {
            $this->release(60);

            return;
        }

        $this->markFailed(
            $messageLog,
            'Ambiguous delivery after worker interruption; automatic resend suppressed to avoid a duplicate.'
        );
    }

    private function markFailed(WhatsAppMessageLog $messageLog, string $error): void
    {
        app(WhatsAppBatchService::class)->transitionClaimedMessage(
            $messageLog->id,
            $this->attemptToken,
            'failed',
            $error
        );
    }

    public function failed(?Throwable $exception): void
    {
        if (app(WhatsAppQueueState::class)->paused()) {
            WhatsAppMessageLog::query()
                ->whereKey($this->messageLogId)
                ->where('status', 'pending')
                ->update(['updated_at' => now()]);

            return;
        }

        app(WhatsAppBatchService::class)->expireMessage(
            $this->messageLogId,
            $this->attemptToken,
            $exception?->getMessage() ?? 'WhatsApp retry deadline reached'
        );
    }

    private function retryLater(WhatsAppMessageLog $messageLog, string $error, int $delay): void
    {
        $transitioned = app(WhatsAppBatchService::class)->transitionClaimedMessage(
            $messageLog->id,
            $this->attemptToken,
            'pending',
            $error
        );
        if (! $transitioned) {
            WhatsAppMessageLog::query()
                ->whereKey($messageLog->id)
                ->where('status', 'pending')
                ->whereNull('delivery_token')
                ->update(['error' => $error, 'updated_at' => now()]);
        }
        $this->release(max(15, $delay));
    }

    private function markInvoicesNotified(WhatsAppMessageLog $messageLog): void
    {
        $invoiceIds = array_filter(array_map('intval', $messageLog->invoice_ids ?? []));

        if ($messageLog->invoice_id) {
            $invoiceIds[] = (int) $messageLog->invoice_id;
        }

        if ($invoiceIds !== []) {
            Invoice::query()->whereIn('id', array_unique($invoiceIds))->update(['last_notified_at' => now()]);
        }
    }
}
