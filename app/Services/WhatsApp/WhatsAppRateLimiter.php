<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppMessageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class WhatsAppRateLimiter
{
    private const CACHE_NEXT_ALLOWED_AT = 'whatsapp_rate_limiter_next_allowed_at';

    private const CACHE_BATCH_PAUSE_UNTIL = 'whatsapp_rate_limiter_batch_pause_until';

    private const CACHE_LAST_BATCH_PAUSE_MARKER = 'whatsapp_rate_limiter_last_batch_pause_count';

    public function __construct(private readonly WhatsAppSafetySettings $safetySettings) {}

    public function settings(): array
    {
        return $this->safetySettings->settings();
    }

    public function status(): array
    {
        $settings = $this->settings();
        $hourlySent = $this->sentCountSince(now()->subHour());
        $dailySent = $this->sentCountSince(Carbon::today());
        $delayMin = max(0, $settings['base_delay'] - (int) round($settings['base_delay'] * ($settings['jitter_percent'] / 100)));
        $delayMax = max($delayMin, $settings['base_delay'] + (int) round($settings['base_delay'] * ($settings['jitter_percent'] / 100)));
        $hourlyPercent = $settings['hourly_limit'] > 0 ? min(100, round(($hourlySent / $settings['hourly_limit']) * 100)) : 100;
        $dailyPercent = $settings['daily_limit'] > 0 ? min(100, round(($dailySent / $settings['daily_limit']) * 100)) : 100;
        $limitCheck = $this->checkLimits();

        return [
            'enabled' => $settings['enabled'],
            'allowed' => $limitCheck['allowed'] ?? false,
            'reason' => $limitCheck['reason'] ?? null,
            'settings' => $settings,
            'hourly_sent' => $hourlySent,
            'daily_sent' => $dailySent,
            'hourly_percent' => $hourlyPercent,
            'daily_percent' => $dailyPercent,
            'delay_min_seconds' => $delayMin,
            'delay_max_seconds' => $delayMax,
            'risk_level' => $this->riskLevel($settings, $hourlyPercent, $dailyPercent, $limitCheck),
            'checked_at' => now(),
        ];
    }

    public function checkLimits(): array
    {
        $settings = $this->settings();
        if (! $settings['enabled']) {
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

        if (! $settings['enabled']) {
            return ['allowed' => true, 'waited_seconds' => 0, 'reason' => 'Rate limiter disabled'];
        }

        if (! ($limitCheck['allowed'] ?? false)) {
            return array_merge($limitCheck, [
                'rate_limited' => true,
                'waited_seconds' => 0,
            ]);
        }

        $waited = 0;
        $now = time();
        $nextAllowedAt = (int) Cache::get(self::CACHE_NEXT_ALLOWED_AT, 0);

        if ($nextAllowedAt > $now) {
            $wait = min(120, $nextAllowedAt - $now);
            sleep($wait);
            $waited += $wait;

            $limitCheck = $this->checkLimits();
            $settings = $limitCheck['settings'];
            if (! ($limitCheck['allowed'] ?? false)) {
                return array_merge($limitCheck, [
                    'rate_limited' => true,
                    'waited_seconds' => $waited,
                ]);
            }
        }

        $batchPause = $this->batchPauseResult($limitCheck, $settings);
        if ($batchPause !== null) {
            return $batchPause;
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

    private function batchPauseResult(array $limitCheck, array $settings): ?array
    {
        $now = time();
        $pauseUntil = (int) Cache::get(self::CACHE_BATCH_PAUSE_UNTIL, 0);

        if ($pauseUntil > $now) {
            return [
                'allowed' => false,
                'rate_limited' => true,
                'reason' => 'Safety pause after a WhatsApp message batch.',
                'retry_after_seconds' => $pauseUntil - $now,
                'settings' => $settings,
                'waited_seconds' => 0,
            ];
        }

        $sentToday = (int) ($limitCheck['daily_sent'] ?? 0);
        $pauseEvery = (int) $settings['batch_pause_every'];
        $pauseMarker = Carbon::today()->format('Y-m-d').':'.$sentToday;
        $lastPauseMarker = (string) Cache::get(self::CACHE_LAST_BATCH_PAUSE_MARKER, '');

        if (
            $pauseEvery <= 0
            || $sentToday <= 0
            || $sentToday % $pauseEvery !== 0
            || $lastPauseMarker === $pauseMarker
        ) {
            return null;
        }

        $pauseMin = min($settings['batch_pause_min_seconds'], $settings['batch_pause_max_seconds']);
        $pauseMax = max($settings['batch_pause_min_seconds'], $settings['batch_pause_max_seconds']);
        $pauseSeconds = $pauseMax > 0 ? random_int($pauseMin, $pauseMax) : 0;

        Cache::put(self::CACHE_LAST_BATCH_PAUSE_MARKER, $pauseMarker, now()->addDays(2));

        if ($pauseSeconds <= 0) {
            return null;
        }

        Cache::put(
            self::CACHE_BATCH_PAUSE_UNTIL,
            $now + $pauseSeconds,
            now()->addSeconds($pauseSeconds + 60)
        );

        return [
            'allowed' => false,
            'rate_limited' => true,
            'reason' => "Safety pause after {$sentToday} WhatsApp messages.",
            'retry_after_seconds' => $pauseSeconds,
            'settings' => $settings,
            'waited_seconds' => 0,
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

    private function sentCountSince($since): int
    {
        return WhatsAppMessageLog::query()
            ->where('status', 'sent')
            ->where('updated_at', '>=', $since)
            ->count();
    }

    private function riskLevel(array $settings, int $hourlyPercent, int $dailyPercent, array $limitCheck): string
    {
        if (! $settings['enabled']) {
            return 'disabled';
        }

        if (! ($limitCheck['allowed'] ?? false)) {
            return 'paused';
        }

        if ($hourlyPercent >= 80 || $dailyPercent >= 80) {
            return 'warning';
        }

        return 'safe';
    }
}
