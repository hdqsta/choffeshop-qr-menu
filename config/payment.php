<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    | Supported: "manual", "midtrans", "xendit"
    */
    'default' => env('PAYMENT_GATEWAY', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Payment Expiration Time (Minutes)
    |--------------------------------------------------------------------------
    */
    'expiry_minutes' => (int) env('PAYMENT_EXPIRY_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Static Merchant QRIS (Fallback for Manual Gateway)
    |--------------------------------------------------------------------------
    */
    'manual' => [
        'qris_image' => env('MANUAL_QRIS_IMAGE', '/images/qris-static.png'),
        'merchant_name' => env('MERCHANT_NAME', 'Kedai Kopi Kita'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans Credentials
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Xendit Credentials
    |--------------------------------------------------------------------------
    */
    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY', ''),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', ''),
    ],
];
