<?php

use App\Http\Controllers\Api\Webhooks\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Here are webhook routes for external platforms. These routes are
| publicly accessible and don't require authentication.
|
*/

// WhatsApp Business API Webhooks
Route::prefix('whatsapp')->group(function () {
    Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
    Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp.handle');
});

// Facebook Messenger Webhooks
Route::prefix('facebook')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\Webhooks\FacebookWebhookController::class, 'verify'])->name('webhooks.facebook.verify');
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\FacebookWebhookController::class, 'handle'])->name('webhooks.facebook.handle');
});

// Instagram Messaging Webhooks
Route::prefix('instagram')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\Webhooks\InstagramWebhookController::class, 'verify'])->name('webhooks.instagram.verify');
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\InstagramWebhookController::class, 'handle'])->name('webhooks.instagram.handle');
});

// Shopify E-Commerce Webhooks
Route::prefix('shopify')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\ShopifyWebhookController::class, 'handle'])->name('webhooks.shopify.handle');
});

