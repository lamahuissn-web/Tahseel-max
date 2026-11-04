<?php

namespace Tests\Unit;

use App\Services\Telegram\TelegramMessage;
use Tests\TestCase;

/**
 * Spec 023 — canonical anatomy of every Telegram operator message.
 *
 * These assertions are the contract: title line, separator, one field per line,
 * bold amount, footer with time + actor, optional inline button, escaped values.
 */
class TelegramMessageTest extends TestCase
{
    private function paid(): TelegramMessage
    {
        return TelegramMessage::make('invoice_paid')
            ->field('العميل', 'ابو محمود سلمون W.K')
            ->field('الفاتورة', '#19000', 'الاستحقاق 20/10 (+15 يوم)')
            ->amount('25.00', '$')
            ->actor('قاسم محمد عامر')
            ->at(new \DateTimeImmutable('2026-11-04 15:42:00'));
    }

    public function test_anatomy_is_title_separator_fields_amount_and_footer(): void
    {
        $lines = explode("\n", $this->paid()->toPayload()['text']);

        $this->assertSame('💵 دفعة مستلمة', $lines[0], 'title must carry the event emoji');
        $this->assertSame('──────────────', $lines[1], 'separator line follows the title');
        $this->assertSame('العميل: ابو محمود سلمون W.K', $lines[2]);
        $this->assertSame('الفاتورة: #19000 · الاستحقاق 20/10 (+15 يوم)', $lines[3]);
        $this->assertSame('<b>25.00 $</b>', $lines[4], 'amount is its own bold line');
        $this->assertSame('15:42 · 04/11/2026 · قاسم محمد عامر', $lines[5], 'footer is time · date · actor');
        $this->assertCount(6, $lines, 'no stray blank lines');
    }

    public function test_every_dynamic_value_is_html_escaped(): void
    {
        $payload = TelegramMessage::make('client_added')
            ->field('العميل', 'Ali <b>&</b> Sons')
            ->field('ملاحظة', '5 > 3 & "x"')
            ->actor('أبو & علي')
            ->toPayload();

        $this->assertStringNotContainsString('<b>&</b>', $payload['text']);
        $this->assertStringContainsString('Ali &lt;b&gt;&amp;&lt;/b&gt; Sons', $payload['text']);
        $this->assertStringContainsString('5 &gt; 3 &amp; &quot;x&quot;', $payload['text']);
        $this->assertStringContainsString('أبو &amp; علي', $payload['text']);
    }

    public function test_builder_markup_is_not_escaped(): void
    {
        // the only markup that survives is the one the builder adds itself
        $this->assertStringContainsString('<b>25.00 $</b>', $this->paid()->toPayload()['text']);
    }

    public function test_null_and_empty_fields_are_dropped(): void
    {
        $lines = explode("\n", TelegramMessage::make('client_updated')
            ->field('العميل', 'زبون')
            ->field('الهاتف', null)
            ->field('ملاحظة', '')
            ->toPayload()['text']);

        foreach ($lines as $line) {
            $this->assertStringNotContainsString('null', $line);
            $this->assertStringNotContainsString('الهاتف', $line);
            $this->assertStringNotContainsString('ملاحظة', $line);
        }
    }

    public function test_long_values_are_trimmed_to_the_line_budget(): void
    {
        $long = str_repeat('طويل جداً ', 20);
        $text = TelegramMessage::make('client_updated')
            ->field('العميل', $long)
            ->toPayload()['text'];

        $fieldLine = collect(explode("\n", $text))->first(fn ($l) => str_starts_with($l, 'العميل: '));
        $this->assertNotNull($fieldLine);
        $this->assertStringEndsWith('…', $fieldLine);
        $this->assertLessThanOrEqual(48, mb_strlen($fieldLine), 'a field line must stay readable on a phone');
    }

    public function test_link_renders_as_an_inline_button(): void
    {
        $payload = $this->paid()->link('https://bills.ispnet.online/ar/admin/invoice_details/19000')->toPayload();

        $this->assertSame(
            [[['text' => 'فتح السجل', 'url' => 'https://bills.ispnet.online/ar/admin/invoice_details/19000']]],
            $payload['reply_markup']['inline_keyboard']
        );
    }

    public function test_no_link_means_no_keyboard(): void
    {
        $this->assertArrayNotHasKey('reply_markup', $this->paid()->toPayload());
    }

    public function test_missing_title_throws_instead_of_sending(): void
    {
        $this->expectException(\LogicException::class);
        TelegramMessage::make('event_that_does_not_exist')->toPayload();
    }

    public function test_event_catalogue_covers_the_eighteen_call_sites(): void
    {
        $expected = [
            'invoice_paid' => '💵',
            'mobile_invoice_paid' => '📱',
            'invoice_balance_edited' => '✏️',
            'invoice_created' => '🧾',
            'invoice_deleted' => '🗑',
            'invoice_redone' => '↩️',
            'client_added' => '👤',
            'client_updated' => '👤',
            'client_deleted' => '🗑',
            'client_status_changed' => '🔀',
            'expense_added' => '💸',
            'transfer_created' => '🔁',
            'transfer_redone' => '↩️',
            'admin_added' => '🛡',
            'overdue_reminder' => '⏰',
        ];

        foreach ($expected as $event => $emoji) {
            $title = TelegramMessage::make($event)->field('x', 'y')->toPayload()['text'];
            $this->assertStringStartsWith($emoji.' ', $title, "event {$event} must render its emoji");
        }
    }
}
