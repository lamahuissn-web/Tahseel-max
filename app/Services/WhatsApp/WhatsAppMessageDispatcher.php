<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\WhatsAppMessageLog;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Throwable;

class WhatsAppMessageDispatcher
{
    public function __construct(private readonly CacheRepository $cache) {}

    public function dispatch(WhatsAppMessageLog $messageLog): void
    {
        $messageLogId = $messageLog->id;

        DB::afterCommit(function () use ($messageLogId): void {
            $this->enqueue($messageLogId);
        });
    }

    private function enqueue(int $messageLogId): void
    {
        $job = (new SendWhatsAppMessage($messageLogId))
            ->onConnection('whatsapp_database')
            ->onQueue('whatsapp');
        $uniqueLock = new UniqueLock($this->cache);

        if (! $uniqueLock->acquire($job)) {
            return;
        }

        try {
            Queue::connection('whatsapp_database')->pushOn('whatsapp', $job);
        } catch (Throwable $exception) {
            $uniqueLock->release($job);
            Log::error('Failed to enqueue WhatsApp message; recovery will retry it', [
                'message_log_id' => $messageLogId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
