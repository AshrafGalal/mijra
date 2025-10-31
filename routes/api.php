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
use App\Http\Controllers\Api\Tenant\ConversationController;
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

            // Conversations & Messages
            Route::get('conversations/statistics', [ConversationController::class, 'statistics']);
            Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages']);
            Route::post('conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);
            Route::post('conversations/{conversation}/assign', [ConversationController::class, 'assign']);
            Route::post('conversations/{conversation}/unassign', [ConversationController::class, 'unassign']);
            Route::patch('conversations/{conversation}/status', [ConversationController::class, 'updateStatus']);
            Route::post('conversations/{conversation}/mark-read', [ConversationController::class, 'markAsRead']);
            Route::post('conversations/{conversation}/notes', [ConversationController::class, 'addNote']);
            Route::post('conversations/{conversation}/tags', [ConversationController::class, 'addTags']);
            Route::delete('conversations/{conversation}/tags', [ConversationController::class, 'removeTags']);
            Route::apiResource('conversations', ConversationController::class)->only(['index', 'show']);

            // Campaigns
            Route::get('campaigns/statistics', [\App\Http\Controllers\Api\Tenant\CampaignController::class, 'statistics']);
            Route::post('campaigns/{campaign}/start', [\App\Http\Controllers\Api\Tenant\CampaignController::class, 'start']);
            Route::post('campaigns/{campaign}/pause', [\App\Http\Controllers\Api\Tenant\CampaignController::class, 'pause']);
            Route::post('campaigns/{campaign}/resume', [\App\Http\Controllers\Api\Tenant\CampaignController::class, 'resume']);
            Route::get('campaigns/{campaign}/analytics', [\App\Http\Controllers\Api\Tenant\CampaignController::class, 'analytics']);
            Route::apiResource('campaigns', \App\Http\Controllers\Api\Tenant\CampaignController::class);

            // Analytics
            Route::get('analytics/dashboard', [\App\Http\Controllers\Api\Tenant\AnalyticsController::class, 'dashboard']);
            Route::get('analytics/time-series', [\App\Http\Controllers\Api\Tenant\AnalyticsController::class, 'timeSeries']);
            Route::get('analytics/customer-lifecycle', [\App\Http\Controllers\Api\Tenant\AnalyticsController::class, 'customerLifecycle']);

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
