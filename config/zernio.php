<?php

/**
 * Zernio WhatsApp transport configuration.
 *
 * Driver switch: 'openwa' (default, production) or 'zernio' (test/production).
 * Set WHATSAPP_DRIVER=zernio in .env to activate Zernio transport.
 */
return [
    'driver' => env('WHATSAPP_DRIVER', 'openwa'),

    // Zernio API settings
    'base_url' => env('ZERNIO_BASE_URL', 'https://zernio.com/api/v1'),
    'api_key' => env('ZERNIO_API_KEY', ''),
    'account_id' => env('ZERNIO_ACCOUNT_ID', ''),

    // WhatsApp Business Account ID (from Zernio dashboard)
    'waba_id' => env('ZERNIO_WABA_ID', ''),

    // Sandbox mode: uses /whatsapp/sandbox/* endpoints (free, 50 msgs/day)
    // Set to false for real WABA production sending
    'sandbox' => env('ZERNIO_SANDBOX', true),

    // Webhook secret for X-Zernio-Signature verification (set in dashboard)
    'webhook_secret' => env('ZERNIO_WEBHOOK_SECRET', ''),

    // Template names (Meta-approved, set after approval)
    'receipt_template' => env('ZERNIO_RECEIPT_TEMPLATE', 'payment_receipt_v3'),
    'reminder_template' => env('ZERNIO_REMINDER_TEMPLATE', ''),
];
