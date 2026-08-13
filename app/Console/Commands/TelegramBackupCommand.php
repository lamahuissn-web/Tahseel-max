<?php

namespace App\Console\Commands;

use App\Services\TelegramApiClient;
use App\Services\TelegramConfig;
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

        if (!TelegramConfig::backupEnabled() && !$force) {
            $this->info('Telegram backup is disabled. Use --force to override.');
            return 0;
        }

        $lastSent = (int) TelegramConfig::get('telegram_backup_last_sent', 0);

        if (!$force && !$this->isTimeToSend($lastSent)) {
            return 0;
        }

        $dbConfig = config('database.connections.mysql');
        $backupFile = storage_path('app/telegram_backup.sql');
        $gzFile = $backupFile . '.gz';
        $credentialsFile = tempnam(storage_path('app'), '.telegram-mysqldump-');

        if ($credentialsFile === false) {
            Log::error('Telegram backup: could not create temporary credentials file');
            $this->error('Could not prepare database backup');
            return 1;
        }

        chmod($credentialsFile, 0600);
        file_put_contents($credentialsFile, sprintf(
            "[client]\nhost=%s\nuser=%s\npassword=%s\n",
            $dbConfig['host'] ?? '127.0.0.1',
            $dbConfig['username'] ?? 'root',
            $dbConfig['password'] ?? ''
        ));

        $dumpCmd = sprintf(
            'mysqldump --defaults-extra-file=%s --single-transaction --routines --triggers --events --hex-blob --default-character-set=utf8mb4 %s > %s 2>/dev/null',
            escapeshellarg($credentialsFile),
            escapeshellarg($dbConfig['database'] ?? 'tahseel'),
            escapeshellarg($backupFile)
        );

        exec($dumpCmd, $output, $exitCode);
        @unlink($credentialsFile);
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

        $gzData = gzencode((string) file_get_contents($backupFile), 9);
        if ($gzData === false || file_put_contents($gzFile, $gzData) === false) {
            @unlink($backupFile);
            @unlink($gzFile);
            Log::error('Telegram backup: compression failed');
            $this->error('Backup compression failed');
            return 1;
        }
        @unlink($backupFile);

        $fileSize = filesize($gzFile);
        if ($fileSize > 50 * 1024 * 1024) {
            Log::warning("Telegram backup: file too large ({$fileSize} bytes), skipping send");
            @unlink($gzFile);
            $this->warn('Backup file exceeds 50MB Telegram limit');
            return 1;
        }

        $caption = sprintf(
            '📦 نسخة احتياطية لقاعدة البيانات — %s | الحجم: %s',
            Carbon::now()->format('Y-m-d H:i'),
            $this->formatBytes($fileSize)
        );

        $api = app(TelegramApiClient::class);
        $sent = $api->sendDocument($gzFile, $caption);

        @unlink($gzFile);

        if ($sent) {
            \App\Models\AppConfig::updateOrCreate(
                ['key' => 'telegram_backup_last_sent'],
                ['value' => (string) time()]
            );
            TelegramConfig::clearCache();
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

        $frequency = TelegramConfig::get('telegram_backup_frequency', 'daily');
        $backupTime = TelegramConfig::get('telegram_backup_time', '02:00');
        $customCron = TelegramConfig::get('telegram_backup_custom_cron');

        return match ($frequency) {
            'hourly' => time() - $lastSent >= 3600,
            'every_2_hours' => time() - $lastSent >= 2 * 3600,
            'every_4_hours' => time() - $lastSent >= 4 * 3600,
            'every_6_hours' => time() - $lastSent >= 6 * 3600,
            'every_12_hours' => time() - $lastSent >= 12 * 3600,
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
