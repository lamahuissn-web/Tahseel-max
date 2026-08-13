<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppBatch;
use App\Models\WhatsAppMessageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WhatsAppBatchService
{
    public function resolveForMessage(WhatsAppMessageLog $message): ?WhatsAppBatch
    {
        if (! Schema::hasTable('whatsapp_batches') || ! Schema::hasColumn('whatsapp_message_logs', 'batch_id')) {
            return null;
        }

        if ($message->batch_id) {
            return $message->batch;
        }

        $uuid = $this->legacyUuid((string) $message->sent_by);
        if ($uuid === null) {
            return null;
        }

        $batch = WhatsAppBatch::query()->firstOrCreate(
            ['uuid' => $uuid],
            [
                'source' => $this->source((string) $message->sent_by),
                'title' => $this->title((string) $message->sent_by),
                'template_type' => $message->template_type,
                'status' => 'queued',
            ]
        );

        $message->update(['batch_id' => $batch->id]);

        return $batch;
    }

    public function claimForDelivery(int $messageId, string $attemptToken): ?WhatsAppMessageLog
    {
        return DB::transaction(function () use ($messageId, $attemptToken): ?WhatsAppMessageLog {
            $batchId = WhatsAppMessageLog::query()->whereKey($messageId)->value('batch_id');
            $batch = $batchId ? WhatsAppBatch::query()->lockForUpdate()->find($batchId) : null;
            $message = WhatsAppMessageLog::query()->lockForUpdate()->find($messageId);

            if (! $message || $message->status !== 'pending') {
                return null;
            }

            if (app(WhatsAppQueueState::class)->paused()) {
                return null;
            }

            if ($batch && (
                $batch->archived_at !== null
                || in_array($batch->status, ['cancelling', 'cancelled'], true)
            )) {
                return null;
            }

            $message->update([
                'status' => 'sending',
                'delivery_token' => $attemptToken,
                'error' => null,
            ]);
            if ($batch && $batch->status === 'queued') {
                $batch->update(['status' => 'running']);
            }

            return $message->fresh();
        });
    }

    public function cancelPreview(WhatsAppBatch $batch): array
    {
        return $this->counts($batch);
    }

    public function cancel(WhatsAppBatch $batch, ?int $actorId, ?string $reason = null): array
    {
        return DB::transaction(function () use ($batch, $actorId, $reason): array {
            $locked = WhatsAppBatch::query()->lockForUpdate()->findOrFail($batch->id);

            if (in_array($locked->status, ['completed', 'completed_with_errors'], true)) {
                throw new \DomainException('batch_not_cancellable');
            }

            if ($locked->cancelled_at === null) {
                $locked->update([
                    'status' => 'cancelling',
                    'cancelled_by' => $actorId,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);
                $locked->messages()->where('status', 'pending')->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);
                $locked->update(['status' => 'cancelled']);
            }

            return $this->counts($locked);
        });
    }

    public function retryMessage(WhatsAppMessageLog $message, bool $acknowledgeAmbiguous = false): WhatsAppMessageLog
    {
        return DB::transaction(function () use ($message, $acknowledgeAmbiguous): WhatsAppMessageLog {
            $batchId = WhatsAppMessageLog::query()->whereKey($message->id)->value('batch_id');
            $batch = $batchId ? WhatsAppBatch::query()->lockForUpdate()->find($batchId) : null;
            $locked = WhatsAppMessageLog::query()->lockForUpdate()->findOrFail($message->id);

            if ($locked->status !== 'failed') {
                throw new \DomainException('message_not_retryable');
            }
            if ($batch && ($batch->archived_at !== null || in_array($batch->status, ['cancelling', 'cancelled'], true))) {
                throw new \DomainException('batch_not_retryable');
            }
            if (! $acknowledgeAmbiguous && $this->isAmbiguous($locked)) {
                throw new \DomainException('ambiguous_acknowledgement_required');
            }

            if (! $batch) {
                $sentBy = (string) ($locked->sent_by ?: 'admin:resend');
                if (! str_contains($sentBy, '|batch:')) {
                    $sentBy .= '|batch:'.Str::uuid();
                    $locked->update(['sent_by' => $sentBy]);
                }
                $batch = $this->resolveForMessage($locked->fresh());
            }

            $locked->update(['status' => 'pending', 'delivery_token' => null, 'error' => null]);
            if ($batch) {
                $batch->update(['status' => 'queued']);
            }

            return $locked->fresh();
        });
    }

    public function retryFailed(WhatsAppBatch $batch, bool $acknowledgeAmbiguous = false)
    {
        return DB::transaction(function () use ($batch, $acknowledgeAmbiguous) {
            $lockedBatch = WhatsAppBatch::query()->lockForUpdate()->findOrFail($batch->id);
            if ($lockedBatch->archived_at !== null || in_array($lockedBatch->status, ['cancelling', 'cancelled'], true)) {
                throw new \DomainException('batch_not_retryable');
            }

            $query = $lockedBatch->messages()->where('status', 'failed')->orderBy('id')->lockForUpdate();
            if (! $acknowledgeAmbiguous) {
                $query->where(function ($query): void {
                    $query->whereNull('error')
                        ->orWhere(function ($query): void {
                            $query->where('error', 'not like', '%Ambiguous%')
                                ->where('error', 'not like', '%automatic resend suppressed%');
                        });
                });
            }

            $messages = $query->get();
            if ($messages->isNotEmpty()) {
                WhatsAppMessageLog::query()
                    ->whereIn('id', $messages->modelKeys())
                    ->where('status', 'failed')
                    ->update([
                        'status' => 'pending',
                        'delivery_token' => null,
                        'error' => null,
                        'updated_at' => now(),
                    ]);
                $lockedBatch->update(['status' => 'queued']);
            }

            return WhatsAppMessageLog::query()->whereIn('id', $messages->modelKeys())->orderBy('id')->get();
        });
    }

    public function archive(WhatsAppBatch $batch, ?int $actorId): WhatsAppBatch
    {
        return DB::transaction(function () use ($batch, $actorId): WhatsAppBatch {
            $locked = WhatsAppBatch::query()->lockForUpdate()->findOrFail($batch->id);
            $messages = $locked->messages()->orderBy('id')->lockForUpdate()->get();

            if (! in_array($locked->status, ['completed', 'completed_with_errors', 'cancelled'], true)
                || $messages->contains(fn (WhatsAppMessageLog $message) => in_array($message->status, ['pending', 'sending'], true))) {
                throw new \DomainException('batch_not_archivable');
            }

            if ($locked->archived_at === null) {
                $locked->update(['archived_at' => now(), 'archived_by' => $actorId]);
            }

            return $locked->fresh();
        });
    }

    public function transitionClaimedMessage(
        int $messageId,
        string $attemptToken,
        string $status,
        ?string $error = null
    ): bool
    {
        return DB::transaction(function () use ($messageId, $attemptToken, $status, $error): bool {
            $batchId = WhatsAppMessageLog::query()->whereKey($messageId)->value('batch_id');
            $batch = $batchId ? WhatsAppBatch::query()->lockForUpdate()->find($batchId) : null;
            $message = WhatsAppMessageLog::query()->lockForUpdate()->find($messageId);

            if (! $message
                || $message->status !== 'sending'
                || ! hash_equals((string) $message->delivery_token, $attemptToken)) {
                return false;
            }
            if ($status === 'pending' && $batch && ($batch->archived_at !== null
                || in_array($batch->status, ['cancelling', 'cancelled'], true))) {
                return false;
            }

            $message->update([
                'status' => $status,
                'delivery_token' => null,
                'error' => $error,
            ]);
            if ($batch) {
                $this->updateLockedLifecycle($batch);
            }

            return true;
        });
    }

    public function expireMessage(int $messageId, string $attemptToken, string $error): void
    {
        DB::transaction(function () use ($messageId, $attemptToken, $error): void {
            $batchId = WhatsAppMessageLog::query()->whereKey($messageId)->value('batch_id');
            $batch = $batchId ? WhatsAppBatch::query()->lockForUpdate()->find($batchId) : null;
            $message = WhatsAppMessageLog::query()->lockForUpdate()->find($messageId);

            if (! $message
                || $message->status !== 'sending'
                || ! hash_equals((string) $message->delivery_token, $attemptToken)) {
                return;
            }
            if ($batch && ($batch->archived_at !== null || in_array($batch->status, ['cancelling', 'cancelled'], true))) {
                return;
            }

            $message->update(['status' => 'failed', 'delivery_token' => null, 'error' => $error]);
            if ($batch) {
                $this->updateLockedLifecycle($batch);
            }
        });
    }

    public function updateLifecycleForMessage(int $messageId): void
    {
        $batchId = WhatsAppMessageLog::query()->whereKey($messageId)->value('batch_id');
        if (! $batchId) {
            return;
        }

        DB::transaction(function () use ($batchId): void {
            $batch = WhatsAppBatch::query()->lockForUpdate()->find($batchId);
            if (! $batch || in_array($batch->status, ['cancelling', 'cancelled'], true)) {
                return;
            }

            $batch->messages()->orderBy('id')->lockForUpdate()->get();
            $this->updateLockedLifecycle($batch);
        });
    }

    private function updateLockedLifecycle(WhatsAppBatch $batch): void
    {
        if (in_array($batch->status, ['cancelling', 'cancelled'], true)) {
            return;
        }
        $counts = $this->counts($batch);
        if ($counts['pending'] > 0 || $counts['sending'] > 0) {
            if ($batch->status === 'queued' && $counts['sending'] > 0) {
                $batch->update(['status' => 'running']);
            }
            return;
        }
        $batch->update(['status' => $counts['failed'] > 0 ? 'completed_with_errors' : 'completed']);
    }

    private function isAmbiguous(WhatsAppMessageLog $message): bool
    {
        $error = (string) $message->error;

        return str_contains($error, 'Ambiguous')
            || str_contains($error, 'automatic resend suppressed');
    }

    private function counts(WhatsAppBatch $batch): array
    {
        $counts = $batch->messages()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(['pending', 'sending', 'sent', 'failed', 'cancelled'])
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->put('total', (int) $counts->sum())
            ->all();
    }

    private function legacyUuid(string $sentBy): ?string
    {
        if (! preg_match('/(?:^|\|)batch:([^|]+)/', $sentBy, $matches)) {
            return null;
        }

        $value = trim($matches[1]);
        if ($value === '') {
            return null;
        }
        if (Str::isUuid($value)) {
            return $value;
        }

        $hash = hash('sha256', 'tahseel-whatsapp-batch:'.$value);

        return sprintf(
            '%s-%s-5%s-%s%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            dechex((hexdec($hash[16]) & 0x3) | 0x8),
            substr($hash, 17, 3),
            substr($hash, 20, 12)
        );
    }

    private function source(string $sentBy): string
    {
        return match (true) {
            str_contains($sentBy, 'autoreceipt') => 'autoreceipt',
            str_starts_with($sentBy, 'calendar:') || str_starts_with($sentBy, 'calendar|') => 'calendar',
            str_starts_with($sentBy, 'admin:automation') => 'automation',
            str_starts_with($sentBy, 'admin:') => 'manual',
            str_starts_with($sentBy, 'cron:') => 'cron',
            default => 'system',
        };
    }

    private function title(string $sentBy): string
    {
        return match ($this->source($sentBy)) {
            'autoreceipt' => 'إيصال دفع تلقائي',
            'calendar' => 'إرسال التقويم',
            'automation' => 'إرسال آلي',
            'manual' => 'إرسال يدوي',
            'cron' => 'إرسال مجدول',
            default => 'دفعة قديمة',
        };
    }
}
