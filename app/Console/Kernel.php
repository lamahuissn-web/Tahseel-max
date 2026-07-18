<?php

namespace App\Console;

use App\Console\Commands\SendOverdueReminders;
use App\Console\Commands\WhatsAppRemindersCommand;
use App\Console\Commands\CollectorReminderSendCommand;
use App\Models\AppConfig;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        WhatsAppRemindersCommand::class,
        CollectorReminderSendCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // في حالة عدم وجود الإعدادات، لا تفعل شيئاً
        // }

        $schedule->command('telegram:send-backup')->everyMinute();
        $schedule->command(SendOverdueReminders::class)->dailyAt('08:00');

        // ── WhatsApp Automation Rules (new per-rule JSON config) ──
        try {
            $stored = AppConfig::where('key', 'whatsapp_automation_rules')->value('value');
            if ($stored) {
                $rules = json_decode($stored, true) ?? [];
            } else {
                // Fallback to old config if new one doesn't exist yet
                $oldEnabled = AppConfig::where('key', 'whatsapp_auto_enabled')->value('value');
                if ($oldEnabled == '1') {
                    $oldTime = AppConfig::where('key', 'whatsapp_auto_time')->value('value') ?? '09:00';
                    $schedule->command('whatsapp:reminders --send')->dailyAt($oldTime);
                }
                return;
            }

            // Schedule each enabled rule
            foreach ($rules as $ruleId => $rule) {
                if (!($rule['enabled'] ?? false)) {
                    continue;
                }

                $time = $rule['time'] ?? '09:00';
                $days = $rule['days'] ?? [0, 1, 2, 3, 4, 5, 6];

                $parts = explode(':', $time);
                $hour = (int) ($parts[0] ?? 9);
                $minute = (int) ($parts[1] ?? 0);

                if (count($days) === 7) {
                    // Every day → use dailyAt for cleaner schedule
                    $schedule->command("whatsapp:reminders --send --rule={$ruleId}")->dailyAt($time);
                } else {
                    // Specific days → use cron expression
                    // Our config: 0=سبت(Sat), 1=أحد(Sun), ..., 6=جمعة(Fri)
                    // Cron: 0=Sun, 1=Mon, ..., 6=Sat
                    // Our 0(Sat) → cron 6
                    // Our 1(Sun) → cron 0
                    // Our 2(Mon) → cron 1
                    // Our 3(Tue) → cron 2
                    // Our 4(Wed) → cron 3
                    // Our 5(Thu) → cron 4
                    // Our 6(Fri) → cron 5
                    // Mapping: cronDay = (ourDay + 6) % 7
                    $cronDays = array_map(fn($d) => ($d + 6) % 7, $days);
                    sort($cronDays);
                    $schedule->command("whatsapp:reminders --send --rule={$ruleId}")
                        ->cron("{$minute} {$hour} * * " . implode(',', array_unique($cronDays)));
                }
            }
        } catch (\Exception $e) {
            // Settings not available yet — skip
        }

        // ── Collector Reminder Auto-Send ──
        try {
            $crSettingsRaw = DB::table('app_config')->where('key', 'whatsapp_collector_settings')->value('value');
            if ($crSettingsRaw) {
                $crSettings = json_decode($crSettingsRaw, true);
                if (($crSettings['enabled'] ?? false) && !empty($crSettings['send_time'])) {
                    $schedule->command(CollectorReminderSendCommand::class)
                        ->dailyAt($crSettings['send_time']);
                }
            }
        } catch (\Exception $e) {
            // Config not ready yet
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
