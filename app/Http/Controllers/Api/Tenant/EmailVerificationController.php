<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Exceptions\VerificationCode\ActivationCodeException;
use App\Exceptions\VerificationCode\CodeNotFoundException;
use App\Exceptions\VerificationCode\MaxAttemptsExceededException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\VerifyEmailRequest;
use App\Models\Landlord\User;
use App\Services\Landlord\Actions\Auth\VerificationCodeService;
use App\Services\Tenant\UserService;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $verificationService,
        private readonly UserService $userService
    ) {}

    public function verify(VerifyEmailRequest $request)
    {
        try {
            $tenantUser = $this->userService->findByKey('email', $request->email);
            if (! $tenantUser) {
                return ApiResponse::error(
                    message: 'email not found',
                    code: 404
                );
            }

            if (! $tenantUser->email_verified_at) {
                $this->verificationService->verifyCode($tenantUser->email, 'email_verification', $request->code);

                $tenantUser->email_verified_at = now();

                $tenantUser->save();
            }

            // update landlord user
            User::where('id', $tenantUser->landlord_user_id)->update(['email_verified_at' => now()]);

            return ApiResponse::success(
                message: 'Email verified successfully'
            );

        } catch (CodeNotFoundException|ActivationCodeException|MaxAttemptsExceededException $exception) {
            return ApiResponse::error(message: $exception->getMessage(), code: $exception->getCode());
        }

    }
}
