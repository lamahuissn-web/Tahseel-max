<?php

namespace App\Console\Commands;

use App\Models\AppConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TelegramBackupCommand extends Command
{
    protected $signature = 'telegram:send-backup {--force : Skip all checks and send immediately}';
    protected $description = 'Send database backup to Telegram';

    public function handle()
    {
        $force = $this->option('force');

        $enabled = AppConfig::where('key', 'telegram_backup_enabled')->value('value');
        if ($enabled != '1' && !$force) {
            $this->info('Telegram backup is disabled. Use --force to override.');
            return 0;
        }

        $lastSent = (int) (AppConfig::where('key', 'telegram_backup_last_sent')->value('value') ?? 0);

        if (!$force && !$this->isTimeToSend($lastSent)) {
            return 0;
        }

        $dbName = env('DB_DATABASE', 'tahseel');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $backupFile = storage_path('app/telegram_backup.sql');
        $gzFile = $backupFile . '.gz';

        $dumpCmd = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s 2>/dev/null',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($backupFile)
        );

        exec($dumpCmd, $output, $exitCode);
        if ($exitCode !== 0) {
            Log::error('Telegram backup: mysqldump failed');
            $this->error('mysqldump failed');
            return 1;
        }

        if (!file_exists($backupFile)) {
            Log::error('Telegram backup: backup file not created');
            $this->error('Backup file not created');
            return 1;
        }

        $gzData = gzencode(file_get_contents($backupFile), 9);
        file_put_contents($gzFile, $gzData);
        unlink($backupFile);

        $fileSize = filesize($gzFile);
        if ($fileSize > 50 * 1024 * 1024) {
            Log::warning("Telegram backup: file too large ({$fileSize} bytes), skipping send");
            unlink($gzFile);
            $this->warn('Backup file exceeds 50MB Telegram limit');
            return 1;
        }

        $caption = sprintf(
            '📦 نسخة احتياطية لقاعدة البيانات — %s | الحجم: %s',
            Carbon::now()->format('Y-m-d H:i'),
            $this->formatBytes($fileSize)
        );

        $sent = sendTelegramDocument($gzFile, $caption);

        unlink($gzFile);

        if ($sent) {
            AppConfig::updateOrCreate(
                ['key' => 'telegram_backup_last_sent'],
                ['value' => (string) time()]
            );
            $this->info('Backup sent to Telegram successfully');
            Log::info('Telegram backup sent successfully');
        } else {
            $this->error('Failed to send backup to Telegram');
            Log::error('Telegram backup: failed to send document');
            return 1;
        }

        return 0;
    }

    private function isTimeToSend($lastSent)
    {
        if (!$lastSent) return true;

        $frequency = AppConfig::where('key', 'telegram_backup_frequency')->value('value') ?? 'daily';
        $backupTime = AppConfig::where('key', 'telegram_backup_time')->value('value') ?? '02:00';
        $customCron = AppConfig::where('key', 'telegram_backup_custom_cron')->value('value');

        return match ($frequency) {
            'hourly' => time() - $lastSent >= 3600,
            'daily' => $this->isDueDaily($lastSent, $backupTime),
            'weekly' => $this->isDueWeekly($lastSent, $backupTime),
            'monthly' => $this->isDueMonthly($lastSent, $backupTime),
            'custom' => $this->isDueCustom($lastSent, $customCron),
            default => time() - $lastSent >= 86400,
        };
    }

    private function isDueDaily($lastSent, $backupTime)
    {
        $todayTarget = Carbon::now()->setTimeFromTimeString($backupTime);
        $lastSentCarbon = Carbon::createFromTimestamp($lastSent);
        return Carbon::now()->gte($todayTarget) && $lastSentCarbon->lt($todayTarget);
    }

    private function isDueWeekly($lastSent, $backupTime)
    {
        $mondayTarget = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTimeFromTimeString($backupTime);
        $lastSentCarbon = Carbon::createFromTimestamp($lastSent);
        return Carbon::now()->gte($mondayTarget) && $lastSentCarbon->lt($mondayTarget);
    }

    private function isDueMonthly($lastSent, $backupTime)
    {
        $firstOfMonth = Carbon::now()->startOfMonth()->setTimeFromTimeString($backupTime);
        $lastSentCarbon = Carbon::createFromTimestamp($lastSent);
        return Carbon::now()->gte($firstOfMonth) && $lastSentCarbon->lt($firstOfMonth);
    }

    private function isDueCustom($lastSent, $customCron)
    {
        if (!$customCron) return false;
        try {
            $cronParts = explode(' ', $customCron);
            if (count($cronParts) < 5) return false;

            $minute = $cronParts[0];
            $hour = $cronParts[1];
            $dayOfMonth = $cronParts[2];
            $month = $cronParts[3];
            $dayOfWeek = $cronParts[4];

            $now = Carbon::now();
            if ($minute !== '*' && (int)$minute !== (int)$now->format('i')) return false;
            if ($hour !== '*' && (int)$hour !== (int)$now->format('H')) return false;
            if ($dayOfMonth !== '*' && (int)$dayOfMonth !== (int)$now->format('d')) return false;
            if ($month !== '*' && (int)$month !== (int)$now->format('n')) return false;
            if ($dayOfWeek !== '*' && (int)$dayOfWeek !== (int)$now->format('N')) return false;

            return time() - $lastSent >= 60;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
