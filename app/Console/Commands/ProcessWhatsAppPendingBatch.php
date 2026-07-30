<?php

namespace App\Console\Commands;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppMessageDispatcher;
use Illuminate\Console\Command;

class ProcessWhatsAppPendingBatch extends Command
{
    protected $signature = 'whatsapp:process-pending {batch : Batch UUID} {--delay=10 : Legacy option; centralized queue controls actual pacing}';

    protected $description = 'Dispatch a queued WhatsApp batch to the serialized database worker.';

    public function handle(WhatsAppMessageDispatcher $dispatcher): int
    {
        $batchId = (string) $this->argument('batch');
        $batchSuffix = '|batch:'.$batchId;

        $logs = WhatsAppMessageLog::query()
            ->where('sent_by', 'like', '%'.$batchSuffix)
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No pending logs found for batch '.$batchId);

            return Command::SUCCESS;
        }

        foreach ($logs as $log) {
            $dispatcher->dispatch($log);
        }

        $this->info('Batch dispatched to serialized WhatsApp queue: '.$batchId);

        return Command::SUCCESS;
    }
}
