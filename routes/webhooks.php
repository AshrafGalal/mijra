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

// TikTok Messaging Webhooks
Route::prefix('tiktok')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\Webhooks\TikTokWebhookController::class, 'verify'])->name('webhooks.tiktok.verify');
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\TikTokWebhookController::class, 'handle'])->name('webhooks.tiktok.handle');
});

// Salla E-Commerce Webhooks
Route::prefix('salla')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\SallaWebhookController::class, 'handle'])->name('webhooks.salla.handle');
});

// WooCommerce E-Commerce Webhooks
Route::prefix('woocommerce')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\WooCommerceWebhookController::class, 'handle'])->name('webhooks.woocommerce.handle');
});

// Google Business Messages Webhooks
Route::prefix('google-business')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\GoogleBusinessMessagesWebhookController::class, 'handle'])->name('webhooks.gmb.handle');
});

// Pymob Payment Webhooks
Route::prefix('pymob')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\PymobWebhookController::class, 'handle'])->name('webhooks.pymob.handle');
});

// Moyasar Payment Webhooks
Route::prefix('moyasar')->group(function () {
    Route::post('/', [\App\Http\Controllers\Api\Webhooks\MoyasarWebhookController::class, 'handle'])->name('webhooks.moyasar.handle');
});

