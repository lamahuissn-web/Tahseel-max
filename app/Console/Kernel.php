<?php

namespace App\Console;

use App\Console\Commands\AddNewInvoices;
use App\Console\Commands\AutoBackupCommand;
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
