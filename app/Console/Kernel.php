<?php

namespace App\Console;

use App\Console\Commands\AddNewInvoices;
use App\Console\Commands\AutoBackupCommand;
use App\Console\Commands\SendOverdueReminders;
use App\Console\Commands\WhatsAppRemindersCommand;
use App\Models\AppConfig;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command(AddNewInvoices::class)->monthlyOn(1, '00:00');
        
        // جدولة النسخ الاحتياطي التلقائي بناءً على الإعدادات
        try {
            $autoBackupEnabled = AppConfig::where('key', 'auto_backup_enabled')->value('value');
            $backupFrequency = AppConfig::where('key', 'backup_frequency')->value('value');
            
            if ($autoBackupEnabled == '1') {
                $backupCommand = $schedule->command(AutoBackupCommand::class);
                
                switch ($backupFrequency) {
                    case 'daily':
                        $backupCommand->dailyAt('17:44'); // الساعة 2 صباحاً
                        break;
                    case 'weekly':
                        $backupCommand->weeklyOn(1, '02:00'); // يوم الاثنين الساعة 2 صباحاً
                        break;
                    case 'monthly':
                        $backupCommand->monthlyOn(1, '02:00'); // أول يوم من الشهر الساعة 2 صباحاً
                        break;
                    default:
                        $backupCommand->dailyAt('02:00');
                }
            }
        } catch (\Exception $e) {
            // في حالة عدم وجود الإعدادات، لا تفعل شيئاً
        }

        $schedule->command('telegram:send-backup')->everyMinute();
        $schedule->command(SendOverdueReminders::class)->dailyAt('08:00');

        try {
            $autoEnabled = AppConfig::where('key', 'whatsapp_auto_enabled')->value('value');
            $autoTime = AppConfig::where('key', 'whatsapp_auto_time')->value('value') ?? '09:00';
            $autoDays = AppConfig::where('key', 'whatsapp_auto_days')->value('value') ?? '1,2,3,4,5,6,7';

            if ($autoEnabled == '1') {
                $days = array_map('intval', array_filter(explode(',', $autoDays)));

                if (count($days) === 7) {
                    $schedule->command(WhatsAppRemindersCommand::class . ' --send')->dailyAt($autoTime);
                } elseif (!empty($days)) {
                    $dayMap = [1 => 6, 2 => 0, 3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5];
                    $cronDays = [];
                    foreach ($days as $d) {
                        if (isset($dayMap[$d])) {
                            $cronDays[] = $dayMap[$d];
                        }
                    }
                    if (!empty($cronDays)) {
                        $parts = explode(':', $autoTime);
                        $hour = (int) ($parts[0] ?? 9);
                        $minute = (int) ($parts[1] ?? 0);
                        $schedule->command(WhatsAppRemindersCommand::class . ' --send')
                            ->cron("{$minute} {$hour} * * " . implode(',', array_unique($cronDays)));
                    }
                }
            }
        } catch (\Exception $e) {
            // Settings not available yet — skip
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
