<?php

namespace App\Services\Telegram;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Spec 023 — the single builder for every Telegram operator message.
 *
 * Owner-approved layout (variant 1, 2026-11-04):
 *
 *     💰 دفعة عبر التطبيق — ام سليم العلي تذكير
 *     🧾 #19598 · 📅 الشهر 10/2026 · 💵 15.00 $ · 👤 محل الصفدي
 *     🕓 16:12 · 04/11/2026
 *
 * Line 1 carries the event, the channel it came from (web / app / system) and
 * the headline entity. Line 2 carries the facts as emoji-prefixed segments
 * joined by `·`, the amount always bold. Line 3 is the moment.
 *
 * The first `field()` becomes the headline; later ones become segments, so a
 * call site never has to know which fact is the headline. `headline()` overrides
 * it when the first fact is not a name (transfers). Every dynamic value is
 * escaped here, once.
 */
class TelegramMessage
{
    public const SEPARATOR = '·';

    /** A single segment must stay readable on a phone. */
    private const VALUE_MAX = 46;

    /** event key => [emoji, title]; a payment's title states its channel. */
    private const EVENTS = [
        'invoice_paid' => ['💵', 'دفعة عبر الويب'],
        'mobile_invoice_paid' => ['💰', 'دفعة عبر التطبيق'],
        'invoice_balance_edited' => ['✏️', 'تعديل رصيد فاتورة'],
        'invoice_created' => ['🆕', 'فاتورة جديدة'],
        'invoice_deleted' => ['🗑', 'حذف فاتورة'],
        'invoice_redone' => ['↩️', 'تراجع عن دفعة'],
        'client_added' => ['👤', 'عميل جديد'],
        'client_updated' => ['✏️', 'تعديل بيانات عميل'],
        'client_deleted' => ['🗑', 'حذف عميل'],
        'client_status_changed' => ['🚦', 'تغيّر حالة عميل'],
        'expense_added' => ['💸', 'مصروف جديد'],
        'transfer_created' => ['🔁', 'حوالة بين حسابين'],
        'transfer_redone' => ['↩️', 'تراجع عن حوالة'],
        'admin_added' => ['🛡', 'مشرف جديد'],
        'overdue_reminder' => ['⏰', 'فاتورة متأخرة'],
    ];

    /** field label => segment emoji (the headline always uses the event emoji). */
    private const FIELD_EMOJI = [
        'العميل' => '👤',
        'المحصّل' => '👤',
        'الفاتورة' => '🧾',
        'الاستحقاق' => '📅',
        'الفترة' => '📅',
        'الشهر' => '📅',
        'البند' => '📦',
        'نوع الاشتراك' => '📦',
        'ملاحظة' => '📝',
        'البيان' => '📝',
        'الرصيد قبل' => '💰',
        'الرصيد بعد' => '💰',
        'من حساب' => '🏦',
        'إلى حساب' => '🏦',
        'الصلاحية' => '🎖',
        'البريد' => '✉️',
        'الحالة' => '🔀',
        'المشرف' => '🛡',
    ];

    private string $event;

    private ?string $headline = null;

    /** @var array<int, array{emoji: string, text: string}> */
    private array $segments = [];

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

    /** Force the headline when the first fact is not the entity being reported. */
    public function headline(mixed $value): self
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        if ($value !== '') {
            $this->headline = self::escape(self::trimValue($value));
        }

        return $this;
    }

    /**
     * Register a fact: the first one becomes the headline, the rest become
     * `emoji value` segments on the details line.
     */
    public function field(string $label, mixed $value, ?string $extra = null): self
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        if ($value === '') {
            return $this; // never render an empty or "null" segment
        }

        $text = self::trimValue($value);

        $extra = $extra === null ? '' : trim($extra);
        if ($extra !== '') {
            $text .= ' ' . self::trimValue($extra);
        }

        if ($this->headline === null) {
            $this->headline = self::escape($text);

            return $this;
        }

        $this->segments[] = [
            'emoji' => self::FIELD_EMOJI[$label] ?? '•',
            'text' => self::escape($text),
        ];

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

        $this->amountLine = '💵 <b>' . $line . '</b>';

        return $this;
    }

    public function actor(?string $actor): self
    {
        $actor = $actor === null ? '' : trim($actor);
        $this->actor = $actor === '' ? null : self::escape(self::trimValue($actor));

        return $this;
    }

    public function at(?DateTimeInterface $moment = null): self
    {
        $this->at = $moment === null
            ? Carbon::now()
            : Carbon::instance($moment)->setTimezone(config('app.timezone'));

        return $this;
    }

    /** Adds the due date as a `📅 dd/mm (+N يوم)` segment. */
    public function due(mixed $dueDate, ?DateTimeInterface $paidAt = null, string $label = 'الاستحقاق'): self
    {
        if ($dueDate === null || $dueDate === '') {
            return $this;
        }

        $due = Carbon::parse($dueDate);
        $late = self::daysLate($due, $paidAt);

        $extra = match (true) {
            $late === null => null,
            $late > 0 => '(+' . $late . ' يوم)',
            $late === 0 => '(في الموعد)',
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

        $headline = $emoji . ' ' . $title;

        if ($this->headline !== null && $this->headline !== '') {
            $headline .= ' — ' . $this->headline;
        }

        $segments = [];

        foreach ($this->segments as $segment) {
            $segments[] = $segment['emoji'] . ' ' . $segment['text'];
        }

        if ($this->amountLine !== null) {
            $segments[] = $this->amountLine;
        }

        if ($this->actor !== null) {
            $segments[] = '👤 ' . $this->actor;
        }

        $lines = [$headline];

        if ($segments !== []) {
            $lines[] = implode(' ' . self::SEPARATOR . ' ', $segments);
        }

        $moment = $this->at ?? Carbon::now();
        $lines[] = '🕓 ' . $moment->format('H:i') . ' ' . self::SEPARATOR . ' ' . $moment->format('d/m/Y');

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
