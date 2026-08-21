<?php

namespace Tests\Unit;

use App\Services\WhatsAppMessageBuilder;
use PHPUnit\Framework\TestCase;

class WhatsAppMessageBuilderFormatAmountTest extends TestCase
{
    public function test_whole_numbers_drop_decimals(): void
    {
        $this->assertSame('25', WhatsAppMessageBuilder::formatAmount(25.00));
        $this->assertSame('105', WhatsAppMessageBuilder::formatAmount(105.00));
        $this->assertSame('10', WhatsAppMessageBuilder::formatAmount(10));
    }

    public function test_fractional_amounts_keep_two_decimals(): void
    {
        $this->assertSame('10.50', WhatsAppMessageBuilder::formatAmount(10.50));
        $this->assertSame('25.75', WhatsAppMessageBuilder::formatAmount(25.75));
    }
}