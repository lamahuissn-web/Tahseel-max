<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppMessageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WhatsAppRateLimiter
{
    private const CACHE_NEXT_ALLOWED_AT = 'whatsapp_rate_limiter_next_allowed_at';

    public function settings(): array
    {
        $baseDelay = (int) $this->configValue('whatsapp_rate_base_delay', $this->configValue('whatsapp_auto_delay', 10));
        $jitterPercent = (int) $this->configValue('whatsapp_rate_jitter_percent', 40);
        $hourlyLimit = (int) $this->configValue('whatsapp_rate_hourly_limit', 60);
        $dailyLimit = (int) $this->configValue('whatsapp_rate_daily_limit', 300);
        $batchPauseEvery = (int) $this->configValue('whatsapp_rate_batch_pause_every', 25);
        $batchPauseMin = (int) $this->configValue('whatsapp_rate_batch_pause_min_seconds', 180);
        $batchPauseMax = (int) $this->configValue('whatsapp_rate_batch_pause_max_seconds', 420);

        return [
            'enabled' => $this->configValue('whatsapp_rate_limiter_enabled', '1') === '1',
            'base_delay' => max(0, min($baseDelay, 120)),
            'jitter_percent' => max(0, min($jitterPercent, 90)),
            'hourly_limit' => max(1, min($hourlyLimit, 1000)),
            'daily_limit' => max(1, min($dailyLimit, 5000)),
            'batch_pause_every' => max(0, min($batchPauseEvery, 500)),
            'batch_pause_min_seconds' => max(0, min($batchPauseMin, 3600)),
            'batch_pause_max_seconds' => max(0, min($batchPauseMax, 3600)),
        ];
    }

    public function checkLimits(): array
    {
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return ['allowed' => true, 'reason' => null, 'settings' => $settings];
        }

        $hourlySent = WhatsAppMessageLog::query()
            ->where('status', 'sent')
            ->where('updated_at', '>=', now()->subHour())
            ->count();

        if ($hourlySent >= $settings['hourly_limit']) {
            return [
                'allowed' => false,
                'reason' => "Safety pause: hourly WhatsApp cap reached ({$hourlySent}/{$settings['hourly_limit']}). Pending messages will resume later.",
                'retry_after_seconds' => 3600,
                'settings' => $settings,
            ];
        }

        $dailySent = WhatsAppMessageLog::query()
            ->where('status', 'sent')
            ->where('updated_at', '>=', Carbon::today())
            ->count();

        if ($dailySent >= $settings['daily_limit']) {
            return [
                'allowed' => false,
                'reason' => "Safety pause: daily WhatsApp cap reached ({$dailySent}/{$settings['daily_limit']}). Pending messages will resume tomorrow.",
                'retry_after_seconds' => now()->diffInSeconds(Carbon::tomorrow()),
                'settings' => $settings,
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'hourly_sent' => $hourlySent,
            'daily_sent' => $dailySent,
            'settings' => $settings,
        ];
    }

    public function waitBeforeSend(array $context = []): array
    {
        $limitCheck = $this->checkLimits();
        $settings = $limitCheck['settings'];

        if (!$settings['enabled']) {
            return ['allowed' => true, 'waited_seconds' => 0, 'reason' => 'Rate limiter disabled'];
        }

        if (!($limitCheck['allowed'] ?? false)) {
            return array_merge($limitCheck, [
                'rate_limited' => true,
                'waited_seconds' => 0,
            ]);
        }

        $waited = 0;
        $now = time();
        $nextAllowedAt = (int) Cache::get(self::CACHE_NEXT_ALLOWED_AT, 0);

        if ($nextAllowedAt > $now) {
            $wait = min($nextAllowedAt - $now, 120);
            sleep($wait);
            $waited += $wait;
        }

        $sentInBatch = (int) ($context['sent_in_batch'] ?? 0);
        if (
            $settings['batch_pause_every'] > 0
            && $sentInBatch > 0
            && $sentInBatch % $settings['batch_pause_every'] === 0
        ) {
            $pauseMin = min($settings['batch_pause_min_seconds'], $settings['batch_pause_max_seconds']);
            $pauseMax = max($settings['batch_pause_min_seconds'], $settings['batch_pause_max_seconds']);
            $batchPause = $pauseMax > 0 ? random_int($pauseMin, $pauseMax) : 0;
            if ($batchPause > 0) {
                sleep($batchPause);
                $waited += $batchPause;
            }
        }

        $delay = $this->randomDelaySeconds($settings);
        Cache::put(self::CACHE_NEXT_ALLOWED_AT, time() + $delay, now()->addMinutes(10));

        return [
            'allowed' => true,
            'waited_seconds' => $waited,
            'next_delay_seconds' => $delay,
            'hourly_sent' => $limitCheck['hourly_sent'] ?? null,
            'daily_sent' => $limitCheck['daily_sent'] ?? null,
        ];
    }

    public function randomDelaySeconds(?array $settings = null): int
    {
        $settings = $settings ?? $this->settings();
        $base = (int) $settings['base_delay'];
        if ($base <= 0) {
            return 0;
        }

        $jitter = (int) round($base * ($settings['jitter_percent'] / 100));
        $min = max(0, $base - $jitter);
        $max = max($min, $base + $jitter);

        return random_int($min, $max);
    }

    private function configValue(string $key, $default = null)
    {
        $value = DB::table('app_config')->where('key', $key)->value('value');
        return $value !== null ? $value : $default;
    }
}
