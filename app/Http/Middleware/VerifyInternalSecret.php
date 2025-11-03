<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization'); // Bearer TOKEN
        if (! $token || $token !== 'Bearer '.config('services.whatsapp.api_secret_token')) {
            return ApiResponse::unauthorized();
        }

        return $next($request);
    }
}
