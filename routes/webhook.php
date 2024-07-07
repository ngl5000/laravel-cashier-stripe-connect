<?php

use Illuminate\Support\Facades\Route;
use Ngl5000\CashierConnect\Http\Controllers;

Route::post('/connectWebhook', [Controllers\WebhookController::class, 'handleWebhook'])->name('stripeConnect.webhook');
