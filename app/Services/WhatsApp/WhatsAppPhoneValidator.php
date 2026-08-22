<?php

namespace App\Services\WhatsApp;

/**
 * Spec 019 — Central Lebanese mobile phone validator for the WhatsApp pipeline.
 *
 * Single source of truth for "can this phone number possibly receive a WhatsApp
 * message on our deployments?" Both production servers serve Lebanese ISPs, so
 * the accepted forms are Lebanese mobile numbers:
 *   - prefix 3                -> 7-digit NSN (e.g. 03123456)
 *   - prefixes 70/71/76/78/79/81 -> 8-digit NSN (e.g. 70123456)
 * Accepts local (+leading 0), international (+961 / 00961 / 961) and noisy
 * formatting; normalizes to E.164 (+961…).
 *
 * NOTE: deliberately NOT a general international validator — the goal is to
 * fail fast on garbage like '961000000' before a provider call is wasted.
 * To support another country later, extend MOBILE_PATTERNS.
 */
class WhatsAppPhoneValidator
{
    /** Lebanese mobile prefixes -> expected NSN length after the prefix. */
    private const MOBILE_PATTERNS = [
        '3' => 6,             // 3 + 6 digits = 0312 3456 style (NSN=7)
        '70' => 6, '71' => 6, '76' => 6,
        '78' => 6, '79' => 6, '81' => 6, // 2 + 6 digits (NSN=8)
    ];

    /**
     * @return array{valid: bool, e164: ?string, reason: string}
     */
    public static function normalize(?string $raw): array
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return ['valid' => false, 'e164' => null, 'reason' => 'Phone number is empty'];
        }

        // Strip formatting noise and international markers.
        $digits = preg_replace('/[\s\.\-\(\)]+/', '', $raw);
        $digits = preg_replace('/^\+/', '', $digits);
        $digits = preg_replace('/^00/', '', $digits);

        if (! preg_match('/^\d+$/', $digits)) {
            return ['valid' => false, 'e164' => null, 'reason' => 'Phone number contains non-digit characters'];
        }

        // Reduce to national significant number (NSN).
        if (str_starts_with($digits, '961')) {
            $digits = substr($digits, 3);
        }
        $digits = ltrim($digits, '0');

        if ($digits === '' || ! preg_match('/^\d+$/', $digits)) {
            return ['valid' => false, 'e164' => null, 'reason' => 'No usable subscriber digits'];
        }

        foreach (self::MOBILE_PATTERNS as $prefix => $restLength) {
            if (str_starts_with($digits, $prefix) && strlen($digits) === strlen($prefix) + $restLength) {
                return [
                    'valid' => true,
                    'e164' => '+961'.$digits,
                    'reason' => 'OK',
                ];
            }
        }

        if (preg_match('/^0+$|^1+$/', $digits) || str_ends_with($digits, '000000')) {
            return ['valid' => false, 'e164' => null, 'reason' => 'Phone number looks like a placeholder/garbage number'];
        }

        return ['valid' => false, 'e164' => null, 'reason' => 'Not a recognized Lebanese mobile number (prefix/length wrong)'];
    }

    /**
     * Convenience gate: true when the raw value is empty OR fails normalization.
     */
    public static function isUnsendable(?string $raw): bool
    {
        return ! self::normalize($raw)['valid'];
    }
}
