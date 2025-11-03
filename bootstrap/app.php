<?php

use App\Exceptions\ApiExceptionHandler;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureTenantAccess;
use App\Http\Middleware\ResolveTenantUser;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetUserTimezone;
use App\Http\Middleware\SkipTenantParameter;
use App\Http\Middleware\UsersOnly;
use App\Http\Middleware\VerifyInternalSecret;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::prefix('api/landlord')
                ->group(base_path('routes/landlord/landlord.php'));

            Route::prefix('api/{tenant}')
                ->group(base_path('routes/api.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

        },
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware
            ->group('tenant', [
                NeedsTenant::class,
                EnsureTenantAccess::class,
                ResolveTenantUser::class,
            ])
            ->alias([
                'verifyInternalSecret' => VerifyInternalSecret::class,
                'ensureEmailVerified' => EnsureEmailIsVerified::class,
                'needsTenant' => NeedsTenant::class,
                'locale' => SetLocale::class,
                'users.only' => UsersOnly::class,
                'setTimeZone' => SetUserTimezone::class,
                'skipTenantParameter' => SkipTenantParameter::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Exception $e, Request $request) {
            return ApiExceptionHandler::handle($e, $request);
        });
    })->create();
