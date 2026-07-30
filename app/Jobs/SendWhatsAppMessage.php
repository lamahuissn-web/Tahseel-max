<?php

namespace App\Jobs;

use App\Models\Admin\Invoice;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

class SendWhatsAppMessage implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 0;

    public int $timeout = 650;

    public int $uniqueFor = 172800;

    public readonly int $retryDeadline;

    public function __construct(
        public readonly int $messageLogId,
        ?int $retryDeadline = null
    ) {
        $this->retryDeadline = $retryDeadline ?? now()->addDay()->getTimestamp();
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
        $messageLog = WhatsAppMessageLog::query()->find($this->messageLogId);

        if (! $messageLog || $messageLog->status === 'sent') {
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

        $this->deliver($messageLog, $service);
    }

    private function deliver(WhatsAppMessageLog $messageLog, WhatsAppService $service): void
    {
        $messageLog->update(['status' => 'sending', 'error' => null]);
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

        $messageLog->update(['status' => 'sent', 'error' => null]);
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
        $messageLog->update(['status' => 'pending', 'error' => $error]);

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
        $messageLog->update(['status' => 'failed', 'error' => $error]);
    }

    public function failed(?Throwable $exception): void
    {
        WhatsAppMessageLog::query()
            ->whereKey($this->messageLogId)
            ->where('status', '!=', 'sent')
            ->update([
                'status' => 'failed',
                'error' => $exception?->getMessage() ?? 'WhatsApp retry deadline reached',
                'updated_at' => now(),
            ]);
    }

    private function retryLater(WhatsAppMessageLog $messageLog, string $error, int $delay): void
    {
        $messageLog->update(['status' => 'pending', 'error' => $error]);
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
