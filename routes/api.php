<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Api\Tenant\AttributeController;
use App\Http\Controllers\Api\Tenant\CategoryController;
use App\Http\Controllers\Api\Tenant\CustomerController;
use App\Http\Controllers\Api\Tenant\CustomerFeedbackController;
use App\Http\Controllers\Api\Tenant\DepartmentController;
use App\Http\Controllers\Api\Tenant\EmailVerificationController;
use App\Http\Controllers\Api\Tenant\FeedbackCategoryController;
use App\Http\Controllers\Api\Tenant\GroupController;
use App\Http\Controllers\Api\Tenant\OpportunityController;
use App\Http\Controllers\Api\Tenant\ProductController;
use App\Http\Controllers\Api\Tenant\RoleController;
use App\Http\Controllers\Api\Tenant\Settings\AssignmentSettingController;
use App\Http\Controllers\Api\Tenant\Settings\CurrencySettingController;
use App\Http\Controllers\Api\Tenant\Settings\MailSettingController;
use App\Http\Controllers\Api\Tenant\Settings\RegionalSettingController;
use App\Http\Controllers\Api\Tenant\StageController;
use App\Http\Controllers\Api\Tenant\TaskController;
use App\Http\Controllers\Api\Tenant\TemplateController;
use App\Http\Controllers\Api\Tenant\UserController;
use App\Http\Controllers\Api\Tenant\WorkflowController;
use App\Http\Controllers\Api\Tenant\WorkHourController;
use App\Http\Controllers\UploadFileController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'tenant', 'setTimeZone', 'locale']], function () {
    Route::group(['middleware' => 'skipTenantParameter'], function () {

        // Email verification routes
        Route::prefix('email')->group(function () {
            Route::post('verify', [EmailVerificationController::class, 'verify']);
        });

        Route::group(['middleware' => 'ensureEmailVerified'], function () {
            Route::put('locale', [UserController::class, 'updateLocale']);
            Route::get('customers/statics', [CustomerController::class, 'statics']);
            Route::apiResource('customers', CustomerController::class);

            Route::get('templates/prepare-parameters', [TemplateController::class, 'getParameters']);
            Route::apiResource('templates', TemplateController::class);

            Route::post('upload', UploadFileController::class);
            Route::get('permissions', [RoleController::class, 'permissionsList']);
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('groups', GroupController::class);
            Route::apiResource('departments', DepartmentController::class);
            Route::apiResource('pipelines', WorkflowController::class);
            Route::get('pipelines/{pipeline}/stages', [StageController::class, 'index']);
            Route::post('stages/move', [StageController::class, 'move']);
            Route::apiResource('stages', StageController::class)->except(['index']);
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('users', UserController::class);
            Route::get('tasks/statics', [TaskController::class, 'statics']);
            Route::apiResource('tasks', TaskController::class);
            Route::post('tasks/{task}/status', [TaskController::class, 'changeStatus']);

            // Feedback Categories
            Route::apiResource('feedback-categories', FeedbackCategoryController::class);

            // Customer Feedback
            Route::get('customer-feedback/statics', [CustomerFeedbackController::class, 'statics']);
            Route::apiResource('customer-feedback', CustomerFeedbackController::class);

            // round robin assignment settings
            Route::group(['prefix' => 'settings'], function () {
                Route::get('assignment-settings', [AssignmentSettingController::class, 'index']);
                Route::patch('assignment-settings', [AssignmentSettingController::class, 'update']);
                Route::get('mail-settings', [MailSettingController::class, 'index']);
                Route::patch('mail-settings', [MailSettingController::class, 'update']);
                Route::get('work-hours', [WorkHourController::class, 'index']);
                Route::post('work-hours', [WorkHourController::class, 'saveWorkHour']);
                Route::post('work-hours/{work_hour}', [WorkHourController::class, 'toggleDayClosedFlag']);
                Route::get('currency', [CurrencySettingController::class, 'index']);
                Route::post('currency', [CurrencySettingController::class, 'update']);

                Route::get('regional-settings', [RegionalSettingController::class, 'index']);
                Route::post('regional-settings', [RegionalSettingController::class, 'update']);

                Route::apiResource('attributes', AttributeController::class);
                Route::patch('attributes/{attribute_id}/value', [AttributeController::class, 'createAttributeValue']);
            });

            Route::post('opportunities/move', [OpportunityController::class, 'moveOpportunity']);
            Route::apiResource('opportunities', OpportunityController::class)->except(['update']);
            Route::apiResource('products', ProductController::class);

        });

    });

    Route::fallback(function () {
        return ApiResponse::notFound(message: 'Requested Url not found');
    });
})->where(['tenant' => '^(?!landlord$).*']);
// // ✅ Handle unknown landlord routes
// Route::any('{any}', function () {
//    return ApiResponse::notFound(message: 'Requested Url not found');
// })->where('any', '.*');
