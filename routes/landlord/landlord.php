<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Api\CountryCodeController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\Landlord\ActivationCodeController;
use App\Http\Controllers\Api\Landlord\AdminAuthController;
use App\Http\Controllers\Api\Landlord\AdminController;
use App\Http\Controllers\Api\Landlord\Auth\AuthController;
use App\Http\Controllers\Api\Landlord\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Landlord\Auth\RegisterController;
use App\Http\Controllers\Api\Landlord\Auth\RegisterWithActivationCodeController;
use App\Http\Controllers\Api\Landlord\Auth\SendVerificationCodeController;
use App\Http\Controllers\Api\Landlord\DiscountCodeController;
use App\Http\Controllers\Api\Landlord\FeatureController;
use App\Http\Controllers\Api\Landlord\OAuth\FacebookController;
use App\Http\Controllers\Api\Landlord\OAuth\GoogleAuthController;
use App\Http\Controllers\Api\Landlord\OAuth\ShopifyController;
use App\Http\Controllers\Api\Landlord\PayoutSourceController;
use App\Http\Controllers\Api\Landlord\PlanController;
use App\Http\Controllers\Api\Landlord\RoleController;
use App\Http\Controllers\Api\Landlord\Settings\MailSettingController;
use App\Http\Controllers\Api\Landlord\SourceController;
use App\Http\Controllers\Api\Landlord\Stripe\StripeWebhookController;
use App\Http\Controllers\Api\Landlord\Subscription\InvoiceController;
use App\Http\Controllers\Api\Landlord\Subscription\SubscriptionController;
use App\Http\Controllers\Api\Landlord\TenantController;
use App\Http\Controllers\Api\Landlord\UserController;
use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\TimeZoneController;
use Illuminate\Support\Facades\Route;

Route::get('locales', LocaleController::class);
Route::get('country-code', CountryCodeController::class);
Route::get('currencies', CurrencyController::class);
Route::get('timezones', TimeZoneController::class);
Route::post('stripe/webhook', StripeWebhookController::class);
Route::get('active-plans', [PlanController::class, 'activePlans']);
Route::group(['middleware' => 'guest', 'prefix' => 'auth'], function () {
    Route::middleware('throttle:login')->group(function () {

        Route::post('login', AuthController::class);

        Route::post('admin/login', AdminAuthController::class);

        Route::post('free-trial', RegisterController::class)->name('landlord.auth.free-trial');

        Route::post('register-tenant', RegisterController::class);

        Route::post('signup-activation-code', RegisterWithActivationCodeController::class);
    });

    Route::group(['prefix' => 'google'], function () {
        Route::get('/', [GoogleAuthController::class, 'redirectToProvider']);
        Route::get('callback', [GoogleAuthController::class, 'authenticate']);
    });

    Route::prefix('facebook')->group(function () {
        Route::get('/', [FacebookController::class, 'redirectToProvider']);
        Route::get('callback', [FacebookController::class, 'callback']);
        Route::get('delete-data', [FacebookController::class, 'deleteData']);
        Route::get('deauthorize', [FacebookController::class, 'deAuthorize']);
    });

    Route::prefix('shopify')->group(function () {
        Route::get('/', [ShopifyController::class, 'redirectToProvider']);
        Route::get('callback', [ShopifyController::class, 'callback']);
    });

    Route::prefix('salla')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Landlord\OAuth\SallaController::class, 'redirectToProvider']);
        Route::get('callback', [\App\Http\Controllers\Api\Landlord\OAuth\SallaController::class, 'callback']);
    });

});

Route::prefix('auth')->group(function () {
    Route::post('send-verification-code', SendVerificationCodeController::class);
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);

});

// for tenant and shared tables for tenant section
Route::middleware(['auth:sanctum', 'users.only'])->group(function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::post('change-password', [UserController::class, 'changePassword']);

    Route::group(['prefix' => 'subscriptions'], function () {
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/{subscription_id}/renew', [SubscriptionController::class, 'renew']);
        Route::post('/upgrade', [SubscriptionController::class, 'upgrade']);
    });
    Route::get('discount-codes/{discount_code}/plans/{plan}', [DiscountCodeController::class, 'validateDiscountCode']);

});

Route::group(['middleware' => 'auth:landlord'], function () {
    Route::post('admins/{admin}/status', [AdminController::class, 'toggleStatus']);
    Route::get('admins/profile', [AdminController::class, 'profile']);
    Route::apiResource('admins', AdminController::class);

    Route::get('tenants/statics', [TenantController::class, 'statics']);
    Route::patch('tenants/{tenant_id}/toggle-status', [TenantController::class, 'toggleStatus']);
    Route::apiResource('tenants', TenantController::class);
    Route::get('plans/statics', [PlanController::class, 'statics']);
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('features', FeatureController::class)->only(['index']);
    Route::get('admins/profile', [AdminController::class, 'profile']);
    Route::put('locale', [AdminController::class, 'updateLocale']);

    Route::group(['prefix' => 'activation-codes'], function () {
        Route::get('/', [ActivationCodeController::class, 'index']);
        Route::get('/statics', [ActivationCodeController::class, 'statics']);
        Route::post('generate', [ActivationCodeController::class, 'store']);
        Route::delete('{activation_code}', [ActivationCodeController::class, 'delete']);
    });

    Route::group(['prefix' => 'source-collections'], function () {
        Route::get('/', [PayoutSourceController::class, 'index']);
        Route::post('/', [PayoutSourceController::class, 'createCollection']);
        Route::get('/{collection_id}', [PayoutSourceController::class, 'details']);
        Route::patch('/{collection_id}/collect', [PayoutSourceController::class, 'markCollected']);
        Route::patch('/{collection_id}/codes/collect', [PayoutSourceController::class, 'collectedSpaceficPayoutItem']);

    });

    Route::get('permissions', [RoleController::class, 'permissionsList']);

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('discount-codes', DiscountCodeController::class);

    Route::group(['prefix' => 'subscriptions'], function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::get('/statics', [SubscriptionController::class, 'statics']);
        Route::get('/{subscription_id}/invoices', [SubscriptionController::class, 'subscriptionInvoices']);
    });

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('{invoice_id}', [InvoiceController::class, 'show']);
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/mail', [MailSettingController::class, 'index']);
        Route::patch('mail', [MailSettingController::class, 'update']);
    });

    Route::apiResource('sources', SourceController::class);

});
// ✅ Handle unknown landlord routes
Route::any('{any}', function () {
    return ApiResponse::notFound(message: 'Requested Url not found');
})->where('any', '.*');
