<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\WhatsAppControlCenterController;
use App\Services\WhatsApp\CollectorReminderService;
use App\Models\WhatsAppMessageLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollectorReminderSendCommand extends Command
{
    protected $signature = 'whatsapp:collector-reminder-send
                            {--force : Send even if already sent today}';

    protected $description = 'Auto-send daily collector reminders via WhatsApp Queue';

    public function handle(): int
    {
        $this->info('Collector Reminder: checking settings...');

        $settingsRaw = DB::table('app_config')->where('key', 'whatsapp_collector_settings')->value('value');
        $settings = $settingsRaw ? json_decode($settingsRaw, true) : [];

        $enabled = $settings['enabled'] ?? false;
        if (!$enabled) {
            $this->warn('Collector reminders are disabled in settings.');
            return Command::SUCCESS;
        }

        $sendTime = $settings['send_time'] ?? '08:00';
        $currentTime = now()->format('H:i');

        // Allow running any time if forced, otherwise check approximate time match
        if (!$this->option('force')) {
            $currentMin = now()->format('H:i');
            if ($currentMin !== $sendTime) {
                $this->warn("Scheduled time is {$sendTime}, current time is {$currentMin}. Use --force to send anyway.");
                return Command::SUCCESS;
            }
        }

        // Check last send
        $lastSendRaw = DB::table('app_config')->where('key', 'whatsapp_collector_last_send')->value('value');
        $lastSend = $lastSendRaw ? json_decode($lastSendRaw, true) : null;

        if (!$this->option('force') && $lastSend && ($lastSend['date'] ?? null) === now()->toDateString()) {
            $this->warn('Already sent today. Use --force to send again.');
            return Command::SUCCESS;
        }

        $rulesRaw = DB::table('app_config')->where('key', 'whatsapp_collector_rules')->value('value');
        $rules = $rulesRaw ? json_decode($rulesRaw, true) : [];

        if (empty($rules)) {
            $this->warn('No collector rules configured.');
            return Command::SUCCESS;
        }

        $this->info('Building preview...');
        $preview = CollectorReminderService::buildPreview($rules, $settings);

        $batchId = (string) Str::uuid();
        $queued = 0;
        $skipped = [];

        $skipEmpty = $settings['skip_empty_collectors'] ?? true;

        foreach ($preview['groups'] as $group) {
            if (($group['customer_count'] ?? 0) <= 0) {
                if ($skipEmpty) {
                    continue;
                }
                // Even if empty, send a "no customers today" message? No, skip.
                continue;
            }

            if (empty($group['phone'])) {
                $skipped[] = ($group['name'] ?: 'Collector') . ': missing WhatsApp phone';
                continue;
            }

            foreach (CollectorReminderService::buildMessages($group) as $message) {
                WhatsAppMessageLog::create([
                    'client_id' => null,
                    'client_name' => $group['name'],
                    'phone' => $group['phone'],
                    'message' => $message,
                    'template_type' => 'collector_reminder',
                    'status' => 'pending',
                    'error' => null,
                    'sent_by' => 'system:collector_reminder|batch:' . $batchId,
                ]);
                $queued++;
            }
        }

        if ($queued > 0) {
            $delay = (int) (DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value') ?? 10);
            $this->startQueuedBatchProcessor($batchId, $delay);

            // Record last send only when messages were actually queued.
            DB::table('app_config')->updateOrInsert(
                ['key' => 'whatsapp_collector_last_send'],
                [
                    'value' => json_encode([
                        'date' => now()->toDateString(),
                        'time' => now()->toTimeString(),
                        'batch_id' => $batchId,
                        'queued' => $queued,
                        'source' => 'cron',
                    ], JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->info("Queued: {$queued}, Skipped: " . count($skipped));

        return Command::SUCCESS;
    }

    private function startQueuedBatchProcessor(string $batchId, int $delaySeconds): void
    {
        $pidFile = storage_path("framework/cache/whatsapp_batch_{$batchId}.pid");
        file_put_contents($pidFile, '1');

        $artisanPath = defined('ARTISAN_PATH') ? ARTISAN_PATH : base_path('artisan');
        $phpBinary = '/usr/bin/php';

        $batchArg = escapeshellarg($batchId);
        $delayArg = (int) max(0, $delaySeconds);
        $logFile = '/tmp/whatsapp-batch-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $batchId) . '.log';

        $command = "nohup {$phpBinary} {$artisanPath} whatsapp:process-pending {$batchArg} --delay={$delayArg} > " . escapeshellarg($logFile) . " 2>&1 &";
        exec($command);
    }
}
