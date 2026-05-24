<?php

/*
 * This file is part of the PHPFlasher package.
 * (c) Younes KHOUBZA <younes.khoubza@gmail.com>
 */

return array(
    'scripts' => array(
        'cdn' => array(
            'https://cdn.jsdelivr.net/npm/@flasher/flasher-sweetalert@1.3.1/dist/flasher-sweetalert.min.js',
        ),
        'local' => array(
            env('ASSET_URL').'/vendor/flasher/flasher-sweetalert.min.js',
        ),
    ),
    'styles' => array(
        'cdn' => array(
            'https://cdn.jsdelivr.net/npm/@flasher/flasher-sweetalert@1.3.1/dist/flasher-sweetalert.min.css',
        ),
        'local' => array(
            env('ASSET_URL').'/vendor/flasher/flasher-sweetalert.min.css',
        ),
    ),
);
