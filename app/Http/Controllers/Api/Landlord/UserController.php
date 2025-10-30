<?php

namespace App\Http\Controllers\Api\Landlord;

use App\DTOs\Landlord\ChangePasswordDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ChangePasswordRequest;
use App\Http\Resources\Tenant\AuthUserResource;
use App\Services\Landlord\UserService;

// for tenant to handle shared table
class UserController extends Controller
{
    public function __construct(protected readonly UserService $userService) {}

    /**
     * Change the user's password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();
        $changePasswordDTO = new ChangePasswordDTO(user: $user, password: $request->new_password, logout_other_devices: $request->logout_other_devices);
        $this->userService->changePassword($changePasswordDTO);

        return ApiResponse::success(message: 'Password changed successfully.');
    }

    public function profile()
    {
        $user = auth()->user();

        return ApiResponse::success(data: AuthUserResource::make($user));
    }
}
