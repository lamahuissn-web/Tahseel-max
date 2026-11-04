<?php

namespace Tests\Unit;

use App\Services\Telegram\TelegramMessage;
use Tests\TestCase;

/**
 * Spec 023 — the owner-approved card (variant 1, 2026-11-04):
 *
 *     💰 دفعة عبر التطبيق — ام سليم العلي تذكير
 *     🧾 #19598 · 📅 الشهر 10/2026 · 💵 15.00 $ · 👤 محل الصفدي
 *     🕓 16:12 · 04/11/2026
 *
 * Line 1 = event + channel + headline · line 2 = facts · line 3 = moment.
 */
class TelegramMessageTest extends TestCase
{
    private function paid(): TelegramMessage
    {
        return TelegramMessage::make('mobile_invoice_paid')
            ->field('العميل', 'ام سليم العلي تذكير')
            ->field('الفاتورة', '#19598')
            ->field('الشهر', '10/2026')
            ->amount('15.00', '$')
            ->actor('محل الصفدي')
            ->at(new \DateTimeImmutable('2026-11-04 16:12:00'));
    }

    public function test_the_owner_approved_card_is_rendered_exactly(): void
    {
        $this->assertSame(
            "💰 دفعة عبر التطبيق — ام سليم العلي تذكير\n"
            ."🧾 #19598 · 📅 10/2026 · 💵 <b>15.00 \$</b> · 👤 محل الصفدي\n"
            .'🕓 16:12 · 04/11/2026',
            $this->paid()->toPayload()['text']
        );
    }

    public function test_the_web_payment_says_so_in_the_title(): void
    {
        $text = TelegramMessage::make('invoice_paid')
            ->field('العميل', 'ابو محمود سلمون W.K')
            ->field('الفاتورة', '#19000')
            ->amount('25.00', '$')
            ->toPayload()['text'];

        $this->assertStringStartsWith('💵 دفعة عبر الويب — ابو محمود سلمون W.K', $text);
    }

    public function test_the_first_field_is_the_headline_and_the_rest_are_segments(): void
    {
        $lines = explode("\n", $this->paid()->toPayload()['text']);

        $this->assertStringContainsString('— ام سليم العلي تذكير', $lines[0]);
        $this->assertStringNotContainsString('ام سليم العلي', $lines[1], 'the headline is not repeated');
        $this->assertStringStartsWith('🧾 #19598', $lines[1]);
        $this->assertCount(3, $lines, 'headline + facts + moment');
    }

    public function test_amount_is_bold_and_the_moment_is_last(): void
    {
        $lines = explode("\n", $this->paid()->toPayload()['text']);

        $this->assertStringContainsString('💵 <b>15.00 $</b>', $lines[1]);
        $this->assertSame('🕓 16:12 · 04/11/2026', $lines[2]);
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
        $this->assertStringContainsString('<b>15.00 $</b>', $this->paid()->toPayload()['text']);
    }

    public function test_null_and_empty_fields_are_dropped(): void
    {
        $text = TelegramMessage::make('client_updated')
            ->field('العميل', 'زبون')
            ->field('الهاتف', null)
            ->field('ملاحظة', '')
            ->toPayload()['text'];

        $this->assertStringNotContainsString('null', $text);
        $this->assertStringNotContainsString('الهاتف', $text);
        $this->assertStringNotContainsString('ملاحظة', $text);
    }

    public function test_a_long_value_is_trimmed_and_still_ends_with_an_ellipsis(): void
    {
        $long = str_repeat('طويل جداً ', 20);
        $text = TelegramMessage::make('client_updated')
            ->field('العميل', $long)
            ->toPayload()['text'];

        $this->assertStringContainsString('…', $text);
        $this->assertLessThanOrEqual(120, mb_strlen(explode("\n", $text)[0]), 'the headline stays scannable');
    }

    public function test_due_date_segment_carries_the_delay(): void
    {
        $text = TelegramMessage::make('overdue_reminder')
            ->field('العميل', 'زبون')
            ->due('2026-10-20', new \DateTimeImmutable('2026-11-04'))
            ->toPayload()['text'];

        $this->assertStringContainsString('📅 20/10 (+15 يوم)', $text);
    }

    public function test_headline_override_keeps_the_first_field_as_a_segment(): void
    {
        $text = TelegramMessage::make('transfer_created')
            ->headline('الخزينة ⇢ الصندوق')
            ->field('من حساب', 'الخزينة')
            ->field('إلى حساب', 'الصندوق')
            ->amount('100.00', '$')
            ->toPayload()['text'];

        $this->assertStringStartsWith('🔁 حوالة بين حسابين — الخزينة ⇢ الصندوق', $text);
        $this->assertStringContainsString('🏦 الخزينة', $text);
    }

    public function test_no_message_carries_an_inline_button(): void
    {
        // owner decision 2026-11-04: clean formatting only, no links to tap
        $this->assertSame(['text'], array_keys($this->paid()->toPayload()));
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
            'mobile_invoice_paid' => '💰',
            'invoice_balance_edited' => '✏️',
            'invoice_created' => '🆕',
            'invoice_deleted' => '🗑',
            'invoice_redone' => '↩️',
            'client_added' => '👤',
            'client_updated' => '✏️',
            'client_deleted' => '🗑',
            'client_status_changed' => '🚦',
            'expense_added' => '💸',
            'transfer_created' => '🔁',
            'transfer_redone' => '↩️',
            'admin_added' => '🛡',
            'overdue_reminder' => '⏰',
        ];

        foreach ($expected as $event => $emoji) {
            $text = TelegramMessage::make($event)->field('x', 'y')->toPayload()['text'];
            $this->assertStringStartsWith($emoji.' ', $text, "event {$event} must render its emoji");
        }
    }
}
