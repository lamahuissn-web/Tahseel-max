<?php

namespace App\Services\Telegram;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Spec 023 — the single builder for every Telegram operator message.
 *
 * Anatomy (fixed, enforced by tests):
 *
 *     <emoji> <title>
 *     ──────────────
 *     <label>: <value>[ · <extra>]
 *     <b><amount> <currency></b>
 *     <H:i> · <d/m/Y>[ · <actor>]
 *
 * Every dynamic value is escaped here, once, so no call site can produce a
 * message Telegram refuses to parse. Markup the builder adds itself (`<b>`) is
 * applied after escaping.
 */
class TelegramMessage
{
    public const SEPARATOR = '──────────────';

    /** A single field line must stay readable on a phone. */
    private const VALUE_MAX = 40;

    /** event key => [emoji, title] — the fifteen keys of the eighteen call sites. */
    private const EVENTS = [
        'invoice_paid' => ['💵', 'دفعة مستلمة'],
        'mobile_invoice_paid' => ['📱', 'دفعة من التطبيق'],
        'invoice_balance_edited' => ['✏️', 'تعديل رصيد فاتورة'],
        'invoice_created' => ['🧾', 'فاتورة جديدة'],
        'invoice_deleted' => ['🗑', 'حذف فاتورة'],
        'invoice_redone' => ['↩️', 'تراجع عن دفعة'],
        'client_added' => ['👤', 'عميل جديد'],
        'client_updated' => ['👤', 'تعديل بيانات عميل'],
        'client_deleted' => ['🗑', 'حذف عميل'],
        'client_status_changed' => ['🔀', 'تغيّر حالة عميل'],
        'expense_added' => ['💸', 'مصروف جديد'],
        'transfer_created' => ['🔁', 'حوالة بين حسابين'],
        'transfer_redone' => ['↩️', 'تراجع عن حوالة'],
        'admin_added' => ['🛡', 'مشرف جديد'],
        'overdue_reminder' => ['⏰', 'فاتورة متأخرة'],
    ];

    private string $event;

    /** @var array<int, string> */
    private array $fields = [];

    private ?string $amountLine = null;

    private ?string $actor = null;

    private ?Carbon $at = null;

    private function __construct(string $event)
    {
        $this->event = $event;
    }

    public static function make(string $event): self
    {
        return new self($event);
    }

    public function event(): string
    {
        return $this->event;
    }

    public static function knows(string $event): bool
    {
        return isset(self::EVENTS[$event]);
    }

    public function field(string $label, mixed $value, ?string $extra = null): self
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        if ($value === '') {
            return $this; // never render an empty or "null" row
        }

        $line = self::escape($label) . ': ' . self::escape(self::trimValue($value));

        $extra = $extra === null ? '' : trim($extra);
        if ($extra !== '') {
            $line .= ' · ' . self::escape(self::trimValue($extra));
        }

        $this->fields[] = $line;

        return $this;
    }

    public function amount(mixed $value, ?string $currency = null): self
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        if ($value === '') {
            return $this;
        }

        $line = self::escape($value);
        $currency = $currency === null ? '' : trim($currency);
        if ($currency !== '') {
            $line .= ' ' . self::escape($currency);
        }

        $this->amountLine = '<b>' . $line . '</b>';

        return $this;
    }

    public function actor(?string $actor): self
    {
        $actor = $actor === null ? '' : trim($actor);
        $this->actor = $actor === '' ? null : $actor;

        return $this;
    }

    public function at(?DateTimeInterface $moment = null): self
    {
        $this->at = $moment === null
            ? Carbon::now()
            : Carbon::instance($moment)->setTimezone(config('app.timezone'));

        return $this;
    }

    /**
     * Renders the due-date field with the real distance to the payment:
     * «الاستحقاق: 20/10 · +15 يوم».
     */
    public function due(mixed $dueDate, ?DateTimeInterface $paidAt = null, string $label = 'الاستحقاق'): self
    {
        if ($dueDate === null || $dueDate === '') {
            return $this;
        }

        $due = Carbon::parse($dueDate);
        $late = self::daysLate($due, $paidAt);

        $extra = match (true) {
            $late === null => null,
            $late > 0 => '+' . $late . ' يوم',
            $late === 0 => 'في الموعد',
            default => null,
        };

        return $this->field($label, $due->format('d/m'), $extra);
    }

    /** Whole days between the due date and the payment day (positive = late). */
    public static function daysLate(mixed $dueDate, ?DateTimeInterface $paidAt = null): ?int
    {
        if ($dueDate === null || $dueDate === '') {
            return null;
        }

        $due = Carbon::parse($dueDate)->startOfDay();
        $paid = $paidAt === null ? Carbon::now() : Carbon::instance($paidAt);

        return (int) $due->diffInDays($paid->copy()->startOfDay(), false);
    }

    /**
     * @return array{text: string}
     *
     * @throws LogicException when the event is unknown — a typo must fail in CI,
     *                        never produce a silent message or a silent no-send.
     */
    public function toPayload(): array
    {
        if (! isset(self::EVENTS[$this->event])) {
            throw new LogicException("Unknown Telegram event [{$this->event}].");
        }

        [$emoji, $title] = self::EVENTS[$this->event];

        $lines = [];
        $lines[] = $emoji . ' ' . $title;
        $lines[] = self::SEPARATOR;

        foreach ($this->fields as $field) {
            $lines[] = $field;
        }

        if ($this->amountLine !== null) {
            $lines[] = $this->amountLine;
        }

        $lines[] = $this->footer();

        return ['text' => implode("\n", $lines)];
    }

    /**
     * Send through the shared helper (which owns the enabled/event gates, the
     * retry and the failure log).
     */
    public function send(?string $eventType = null): bool
    {
        return sendTelegramNotification($this, $eventType ?? $this->event);
    }

    private function footer(): string
    {
        $moment = $this->at ?? Carbon::now();

        $footer = $moment->format('H:i') . ' · ' . $moment->format('d/m/Y');

        if ($this->actor !== null) {
            $footer .= ' · ' . self::escape($this->actor);
        }

        return $footer;
    }

    private static function trimValue(string $value): string
    {
        if (mb_strlen($value) <= self::VALUE_MAX) {
            return $value;
        }

        return mb_substr($value, 0, self::VALUE_MAX - 1) . '…';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
