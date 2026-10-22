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
    |
    | The manual gateway never generates a QRIS payload; it displays the
    | merchant's own static QRIS image (path under public/ or an absolute URL)
    | and the cashier confirms settlement from the cashier board. When
    | `qris_image` is empty the customer screen tells the guest to pay at the
    | cashier instead of showing a broken code.
    |
    */
    'manual' => [
        'qris_image' => env('MANUAL_QRIS_IMAGE', ''),
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
