<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\DB;

class WhatsAppSafetySettings
{
    public const MIN_BASE_DELAY = 6;

    public const MAX_BASE_DELAY = 120;

    public const MIN_JITTER_PERCENT = 10;

    public const MAX_JITTER_PERCENT = 50;

    public const MIN_HOURLY_LIMIT = 10;

    public const MAX_HOURLY_LIMIT = 60;

    public const MIN_DAILY_LIMIT = 20;

    public const MAX_DAILY_LIMIT = 300;

    public const MIN_BATCH_SIZE = 10;

    public const MAX_BATCH_SIZE = 50;

    public const MIN_BATCH_PAUSE = 180;

    public const MAX_BATCH_PAUSE = 3600;

    private const CONFIG_KEYS = [
        'preset' => 'whatsapp_rate_preset',
        'base_delay' => 'whatsapp_rate_base_delay',
        'jitter_percent' => 'whatsapp_rate_jitter_percent',
        'hourly_limit' => 'whatsapp_rate_hourly_limit',
        'daily_limit' => 'whatsapp_rate_daily_limit',
        'batch_pause_every' => 'whatsapp_rate_batch_pause_every',
        'batch_pause_min_seconds' => 'whatsapp_rate_batch_pause_min_seconds',
        'batch_pause_max_seconds' => 'whatsapp_rate_batch_pause_max_seconds',
    ];

    private const LEGACY_BASE_DELAY_KEY = 'whatsapp_auto_delay';

    private const LOCK_KEY = 'whatsapp_safety_settings_lock';

    public function presets(): array
    {
        return [
            'very_safe' => [
                'base_delay' => 15,
                'jitter_percent' => 40,
                'hourly_limit' => 40,
                'daily_limit' => 200,
                'batch_pause_every' => 20,
                'batch_pause_min_seconds' => 240,
                'batch_pause_max_seconds' => 480,
            ],
            'balanced' => [
                'base_delay' => 10,
                'jitter_percent' => 40,
                'hourly_limit' => 60,
                'daily_limit' => 300,
                'batch_pause_every' => 25,
                'batch_pause_min_seconds' => 180,
                'batch_pause_max_seconds' => 420,
            ],
        ];
    }

    public function limits(): array
    {
        return [
            'base_delay' => [self::MIN_BASE_DELAY, self::MAX_BASE_DELAY],
            'jitter_percent' => [self::MIN_JITTER_PERCENT, self::MAX_JITTER_PERCENT],
            'hourly_limit' => [self::MIN_HOURLY_LIMIT, self::MAX_HOURLY_LIMIT],
            'daily_limit' => [self::MIN_DAILY_LIMIT, self::MAX_DAILY_LIMIT],
            'batch_pause_every' => [self::MIN_BATCH_SIZE, self::MAX_BATCH_SIZE],
            'batch_pause_seconds' => [self::MIN_BATCH_PAUSE, self::MAX_BATCH_PAUSE],
        ];
    }

    public function settings(): array
    {
        $defaults = $this->presets()['balanced'];
        $stored = DB::table('app_config')
            ->whereIn('key', [...array_values(self::CONFIG_KEYS), self::LEGACY_BASE_DELAY_KEY])
            ->pluck('value', 'key');

        $preset = $stored->get(self::CONFIG_KEYS['preset']);
        $presetWasMissing = $preset === null;
        if ($preset === null) {
            $preset = $stored->except([self::CONFIG_KEYS['preset']])->isEmpty() ? 'balanced' : 'custom';
        } elseif (! in_array($preset, ['very_safe', 'balanced', 'custom'], true)) {
            return ['enabled' => true] + $this->normalize(['preset' => 'balanced'] + $defaults);
        }

        $candidate = ['preset' => $preset];
        foreach (self::CONFIG_KEYS as $name => $key) {
            if ($name !== 'preset') {
                $candidate[$name] = $name === 'base_delay'
                    ? $stored->get($key, $stored->get(self::LEGACY_BASE_DELAY_KEY, $defaults[$name]))
                    : $stored->get($key, $defaults[$name]);
            }
        }

        $normalized = $this->normalize($candidate);
        if ($presetWasMissing && $preset === 'custom') {
            foreach ($this->presets() as $presetName => $presetSettings) {
                if (array_diff_assoc($presetSettings, array_intersect_key($normalized, $presetSettings)) === []) {
                    $normalized['preset'] = $presetName;
                    break;
                }
            }
        }

        return ['enabled' => true] + $normalized;
    }

    public function save(array $validated, array $auditContext): array
    {
        return DB::transaction(function () use ($validated, $auditContext): array {
            $lockRow = DB::table('app_config')
                ->where('key', self::LOCK_KEY)
                ->lockForUpdate()
                ->first();
            if ($lockRow === null) {
                throw new \RuntimeException('WhatsApp safety settings lock row is missing. Run database migrations.');
            }

            $oldSettings = $this->settings();
            $preset = $validated['preset'];
            $candidate = $preset === 'custom' ? $validated : $this->presets()[$preset];
            $newSettings = ['enabled' => true] + $this->normalize(['preset' => $preset] + $candidate);

            $this->persist($newSettings, $auditContext['admin_id']);
            $this->recordAudit($oldSettings, $newSettings, $auditContext);

            return $newSettings;
        });
    }

    private function recordAudit(array $oldSettings, array $newSettings, array $context): void
    {
        DB::table('logs')->insert([
            'action' => 'whatsapp_safety_settings_updated',
            'description' => 'تم تحديث إعدادات التوقيت الآمن لإرسال WhatsApp.',
            'old_data' => json_encode($oldSettings, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'new_data' => json_encode($newSettings, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'user_id' => $context['admin_id'],
            'ip_address' => mb_strcut((string) $context['ip_address'], 0, 45, 'UTF-8'),
            'user_agent' => mb_strcut((string) $context['user_agent'], 0, 255, 'UTF-8'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function normalize(array $candidate): array
    {
        $preset = in_array($candidate['preset'] ?? null, ['very_safe', 'balanced', 'custom'], true)
            ? $candidate['preset']
            : 'custom';
        $jitterPercent = $this->safeInteger($candidate['jitter_percent'] ?? null, self::MIN_JITTER_PERCENT, self::MAX_JITTER_PERCENT, 40);
        $baseDelay = $this->safeBaseDelay($candidate['base_delay'] ?? null, $jitterPercent);
        $minimumPause = $this->safeInteger($candidate['batch_pause_min_seconds'] ?? null, self::MIN_BATCH_PAUSE, self::MAX_BATCH_PAUSE, 180);
        $maximumPause = $this->safeInteger($candidate['batch_pause_max_seconds'] ?? null, self::MIN_BATCH_PAUSE, self::MAX_BATCH_PAUSE, 420);
        if ($maximumPause < $minimumPause) {
            [$minimumPause, $maximumPause] = [180, 420];
        }

        return [
            'preset' => $preset,
            'base_delay' => $baseDelay,
            'jitter_percent' => $jitterPercent,
            'hourly_limit' => $this->safeInteger($candidate['hourly_limit'] ?? null, self::MIN_HOURLY_LIMIT, self::MAX_HOURLY_LIMIT, 60),
            'daily_limit' => $this->safeInteger($candidate['daily_limit'] ?? null, self::MIN_DAILY_LIMIT, self::MAX_DAILY_LIMIT, 300),
            'batch_pause_every' => $this->safeInteger($candidate['batch_pause_every'] ?? null, self::MIN_BATCH_SIZE, self::MAX_BATCH_SIZE, 25),
            'batch_pause_min_seconds' => $minimumPause,
            'batch_pause_max_seconds' => $maximumPause,
        ];
    }

    private function persist(array $settings, ?int $adminId): void
    {
        foreach (self::CONFIG_KEYS as $name => $key) {
            DB::table('app_config')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => (string) $settings[$name],
                    'updated_by' => $adminId,
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_rate_limiter_enabled'],
            ['value' => '1', 'updated_by' => $adminId, 'updated_at' => now()]
        );
        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_auto_delay'],
            ['value' => (string) $settings['base_delay'], 'updated_by' => $adminId, 'updated_at' => now()]
        );
    }

    private function safeInteger(mixed $value, int $minimum, int $maximum, int $default): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer !== false && $integer >= $minimum && $integer <= $maximum
            ? $integer
            : $default;
    }

    private function safeBaseDelay(mixed $value, int $jitterPercent): int
    {
        $baseDelay = $this->safeInteger($value, self::MIN_BASE_DELAY, self::MAX_BASE_DELAY, 10);

        while ($baseDelay - (int) round($baseDelay * ($jitterPercent / 100)) < 4) {
            $baseDelay++;
        }

        return $baseDelay;
    }
}
