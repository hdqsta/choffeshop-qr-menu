<?php

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Payment Gateway Webhook Endpoints (Midtrans, Xendit, Manual)
|
*/

Route::post('webhooks/payment/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('api.webhooks.payment');
