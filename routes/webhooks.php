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

// Future webhook endpoints
// Route::prefix('facebook')->group(function () {
//     Route::get('/', [FacebookWebhookController::class, 'verify']);
//     Route::post('/', [FacebookWebhookController::class, 'handle']);
// });

// Route::prefix('instagram')->group(function () {
//     Route::get('/', [InstagramWebhookController::class, 'verify']);
//     Route::post('/', [InstagramWebhookController::class, 'handle']);
// });



