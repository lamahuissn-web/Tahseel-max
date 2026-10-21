<?php

namespace App\Services\Bonus;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Cache;

/**
 * Feature 021 — bonus settings reader.
 *
 * Every value is admin-editable at any time; nothing is hard-coded in the
 * calculation. Reads are cached per request but invalidated on write, so an
 * admin changing the grace period sees the effect immediately.
 */
class BonusSettings
{
    public const CACHE_KEY = 'bonus_settings';

    /** @var array<string,mixed>|null */
    private static ?array $memo = null;

    public static function flush(): void
    {
        self::$memo = null;
        Cache::forget(self::CACHE_KEY);
    }

    public static function all(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $rows = AppConfig::query()
            ->where('key', 'like', 'bonus\_%')
            ->pluck('value', 'key')
            ->toArray();

        self::$memo = $rows;

        return $rows;
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return $value === null || $value === '' ? $default : (int) $value;
    }

    public static function set(string $key, $value): void
    {
        AppConfig::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'updated_at' => now()]
        );

        self::flush();
    }

    public static function enabled(): bool
    {
        return self::bool('bonus_enabled', false);
    }

    public static function monthsRequired(): int
    {
        $months = self::int('bonus_months_required', 24);

        return $months > 0 ? $months : 24;
    }

    public static function graceDays(): int
    {
        return max(0, self::int('bonus_grace_days', 0));
    }

    public static function catchupPerCycle(): int
    {
        return max(0, self::int('bonus_catchup_per_cycle', 1));
    }

    public static function hardResetAfterMonths(): int
    {
        return max(0, self::int('bonus_hard_reset_after_months', 2));
    }

    public static function spiralAfterMonths(): int
    {
        return max(1, self::int('bonus_spiral_after_months', 2));
    }

    public static function countStartDate(): string
    {
        $value = (string) self::get('bonus_count_start_date', '2026-01-01');

        return $value !== '' ? $value : '2026-01-01';
    }

    public static function requireZeroBalance(): bool
    {
        return self::bool('bonus_require_zero_balance', true);
    }

    public static function autoApplyOnApprove(): bool
    {
        return self::bool('bonus_auto_apply_on_approve', true);
    }

    public static function invoiceValue(): float
    {
        return (float) self::get('bonus_invoice_value', 0);
    }

    public static function invoiceNotes(): string
    {
        return (string) self::get('bonus_invoice_notes', 'شهر مجاني - برنامج البونص');
    }

    /**
     * Client types included in the program. Satellite customers are excluded by
     * the owner's decision (2026-10-21); the list stays editable.
     *
     * @return array<int,string>
     */
    public static function clientTypes(): array
    {
        $raw = (string) self::get('bonus_client_types', 'internet');

        $types = array_values(array_filter(array_map('trim', explode(',', $raw))));

        return $types !== [] ? $types : ['internet'];
    }

    /**
     * Roles allowed to see and operate the bonus section: admin staff + management.
     */
    public static function allowedRoleIds(): array
    {
        return [1, 7];
    }
}
