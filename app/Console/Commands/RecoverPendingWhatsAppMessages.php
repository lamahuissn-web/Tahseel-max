<?php

namespace App\Console\Commands;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppMessageDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RecoverPendingWhatsAppMessages extends Command
{
    private const CURSOR_CACHE_KEY = 'whatsapp_recovery_last_message_id';

    protected $signature = 'whatsapp:recover-pending {--limit=100}';

    protected $description = 'Redispatch pending batch messages to the durable WhatsApp queue.';

    public function handle(WhatsAppMessageDispatcher $dispatcher): int
    {
        $limit = max(1, min((int) $this->option('limit'), 500));
        $cursor = (int) Cache::get(self::CURSOR_CACHE_KEY, 0);
        $query = WhatsAppMessageLog::query()
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->whereNotNull('batch_id')->whereHas('batch', function ($query): void {
                        $query->whereNull('archived_at')->whereNotIn('status', ['cancelling', 'cancelled']);
                    });
                })->orWhere(function ($query): void {
                    $query->whereNull('batch_id')->where('sent_by', 'like', '%|batch:%');
                });
            });
        $messages = (clone $query)
            ->where('id', '>', $cursor)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($messages->isEmpty() && $cursor > 0) {
            $messages = $query
                ->orderBy('id')
                ->limit($limit)
                ->get();
        }

        foreach ($messages as $message) {
            $dispatcher->dispatch($message);
        }

        if ($messages->isNotEmpty()) {
            Cache::forever(self::CURSOR_CACHE_KEY, $messages->last()->id);
        }

        $this->info("Redispatched {$messages->count()} pending batch message(s).");

        return self::SUCCESS;
    }
}
