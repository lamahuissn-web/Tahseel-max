<?php

/**
 * SPIKE: Zernio API configuration (test only).
 */
return [
    // SPIKE driver switch: 'openwa' (default) or 'zernio' (test only)
    'driver' => env('WHATSAPP_DRIVER', 'openwa'),
    'base_url' => env('ZERNIO_BASE_URL', 'https://zernio.com/api/v1'),
    'api_key' => env('ZERNIO_API_KEY', ''),
    'account_id' => env('ZERNIO_ACCOUNT_ID', ''),
];
