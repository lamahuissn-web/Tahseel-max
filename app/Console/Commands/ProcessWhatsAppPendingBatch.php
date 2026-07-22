<?php

namespace App\Console\Commands;

use App\Models\Admin\Invoice;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class ProcessWhatsAppPendingBatch extends Command
{
    protected $signature = 'whatsapp:process-pending {batch : Batch UUID} {--delay=10 : Legacy option; centralized safety limiter controls actual pacing}';

    protected $description = 'Process a queued WhatsApp batch created from the control center preview.';

    public function handle()
    {
        $batchId = (string) $this->argument('batch');
        $batchSuffix = '|batch:' . $batchId;

        $logs = WhatsAppMessageLog::where('sent_by', 'like', '%' . $batchSuffix)
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No pending logs found for batch ' . $batchId);
            return Command::SUCCESS;
        }

        $service = app(WhatsAppService::class);
        $status = $service->status();

        if (!($status['connected'] ?? false)) {
            foreach ($logs as $log) {
                $log->update([
                    'status' => 'failed',
                    'error' => 'WhatsApp not connected when queue batch started',
                ]);
            }

            $this->error('WhatsApp not connected; marked batch as failed.');
            return Command::FAILURE;
        }

        foreach ($logs as $index => $log) {
            $log->update([
                'status' => 'sending',
                'error' => null,
            ]);

            $result = $service->send($log->phone, $log->message, [
                'rate_context' => [
                    'batch_id' => $batchId,
                    'sent_in_batch' => $index,
                ],
            ]);
            $success = isset($result['success']) && $result['success'] === true;

            if (($result['rate_limited'] ?? false) === true) {
                $log->update([
                    'status' => 'pending',
                    'error' => $result['error'] ?? 'Paused by WhatsApp safety rate limiter',
                ]);

                $this->warn($result['error'] ?? 'Paused by WhatsApp safety rate limiter. Remaining messages stay pending.');
                return Command::SUCCESS;
            }

            $log->update([
                'status' => $success ? 'sent' : 'failed',
                'error' => $success ? null : ($result['error'] ?? 'Unknown'),
            ]);

            if ($success && !empty($log->invoice_ids) && is_array($log->invoice_ids)) {
                Invoice::query()
                    ->whereIn('id', array_filter(array_map('intval', $log->invoice_ids)))
                    ->update(['last_notified_at' => now()]);
            }
        }

        $this->info('Batch processed: ' . $batchId);
        return Command::SUCCESS;
    }
}
