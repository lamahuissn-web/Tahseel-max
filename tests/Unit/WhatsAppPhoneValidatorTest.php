<?php

namespace Tests\Unit;

use App\Services\WhatsApp\WhatsAppPhoneValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Spec 019 — WhatsApp phone validation truth table.
 *
 * Pure unit tests: no DB, no queue, no HTTP. The validator must reject
 * unsendable numbers (e.g. '961000000') BEFORE any provider call.
 */
class WhatsAppPhoneValidatorTest extends TestCase
{
    public static function validProvider(): array
    {
        return [
            'local mobile 03' => ['03123456', '+9613123456'],
            'local mobile 70' => ['70123456', '+96170123456'],
            'local mobile 70 spaced' => ['70 123 456', '+96170123456'],
            'local mobile 76 dashed' => ['76-123-456', '+96176123456'],
            'local mobile 78' => ['78123456', '+96178123456'],
            'local mobile 79' => ['79123456', '+96179123456'],
            'local mobile 81' => ['81123456', '+96181123456'],
            'intl plus form' => ['+9613123456', '+9613123456'],
            'intl 00 form' => ['0096170123456', '+96170123456'],
            'intl bare 961' => ['96170123456', '+96170123456'],
            'noisy formatting' => ['+(961) 70-123-456', '+96170123456'],
            'already e164' => ['+96170123456', '+96170123456'],
        ];
    }

    public static function invalidProvider(): array
    {
        return [
            'the reported garbage number' => ['961000000'],
            'all zeros' => ['00000000'],
            'empty string' => [''],
            'whitespace only' => ['   '],
            'too short' => ['70123'],
            'too long' => ['70123456789'],
            'unknown prefix 12' => ['12123456'],
            'landline-style 01' => ['01123456'],
            'letters inside' => ['70abc456'],
            'random digits' => ['1234567'],
            'plus-only' => ['+'],
            'foreign number (Egypt)' => ['+201001234567'],
        ];
    }

    #[DataProvider('validProvider')]
    public function test_accepts_valid_lebanese_mobiles(string $raw, string $expectedE164): void
    {
        $result = WhatsAppPhoneValidator::normalize($raw);

        $this->assertTrue($result['valid'], "Expected '{$raw}' to be VALID, got reason: {$result['reason']}");
        $this->assertSame($expectedE164, $result['e164']);
    }

    #[DataProvider('invalidProvider')]
    public function test_rejects_invalid_numbers(string $raw): void
    {
        $result = WhatsAppPhoneValidator::normalize($raw);

        $this->assertFalse($result['valid'], "Expected '{$raw}' to be INVALID");
        $this->assertNull($result['e164']);
        $this->assertNotSame('', $result['reason'], 'Rejection must carry a human-readable reason');
    }
}
