<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RADIUS Integration Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the FreeRADIUS integration.
    |
    */

    // Database connection name for RADIUS
    'connection' => env('RADIUS_DB_CONNECTION', 'radius'),

    // Default bandwidth when no subscription match is found
    'default_bandwidth' => env('RADIUS_DEFAULT_BANDWIDTH', '10M/10M'),

    // Sync settings
    'sync' => [
        'enabled' => env('RADIUS_SYNC_ENABLED', true),
        'interval_seconds' => env('RADIUS_SYNC_INTERVAL', 120),
        'batch_size' => env('RADIUS_SYNC_BATCH', 100),
    ],

    // Attribute mapping: subscription name pattern => Mikrotik-Rate-Limit
    'bandwidth_map' => [
        '10M' => '10M/10M',
        '6M'  => '6M/6M',
        '3M'  => '3M/3M',
        '12M' => '30M/30M',
        '30$' => '30M/30M',
        '35$' => '35M/35M',
        '14M' => '14M/14M',
        '16M' => '16M/16M',
    ],
];
