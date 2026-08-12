<?php

namespace App\Services;

use App\Models\AppConfig;

/** Centralized, cached reader for Telegram app_config values. */
class TelegramConfig
{
    private static ?array $values = null;

    public static function all(): array
    {
        if (self::$values === null) {
            self::$values = AppConfig::query()
                ->where(function ($query) {
                    $query->where('key', 'like', 'telegram_%')
                        ->orWhere('key', 'in', ['telegram_enabled']);
                })
                ->pluck('value', 'key')
                ->toArray();
        }

        return self::$values;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function enabled(): bool
    {
        return self::get('telegram_enabled', '0') === '1';
    }

    public static function backupEnabled(): bool
    {
        return self::get('telegram_backup_enabled', '0') === '1';
    }

    public static function token(): ?string
    {
        return self::get('telegram_bot_token') ?: null;
    }

    public static function chatId(): ?string
    {
        return self::get('telegram_chat_id') ?: null;
    }

    /**
     * Returns the bot's allowed private/group chat IDs and user IDs.
     * Empty means deny all bot data lookups (fail closed).
     */
    public static function allowedChatIds(): array
    {
        $raw = (string) self::get('telegram_allowed_chat_ids', '');
        $ids = preg_split('/[\s,;]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter($ids, static fn ($id) => preg_match('/^-?\d+$/', $id))));
    }

    public static function isAllowedChatId(mixed $chatId): bool
    {
        return $chatId !== null && in_array((string) $chatId, self::allowedChatIds(), true);
    }

    public static function botUsername(): string
    {
        return self::get('telegram_bot_username') ?: 'mikr313bot';
    }

    public static function eventEnabled(?string $eventType): bool
    {
        if (!$eventType) return true;
        return self::get('telegram_notify_' . $eventType, '1') !== '0';
    }

    public static function clearCache(): void
    {
        self::$values = null;
    }
}
