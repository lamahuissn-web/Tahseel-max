<?php

return [
    'url' => env('SAS4_URL'),
    'username' => env('SAS4_USERNAME'),
    'password' => env('SAS4_PASSWORD'),
    'aes_key' => env('SAS4_AES_KEY'),
    'token_cache_minutes' => 55,
    'status_timeout_seconds' => env('SAS4_STATUS_TIMEOUT_SECONDS', 4),
];
