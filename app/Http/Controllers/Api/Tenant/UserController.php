<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\TenantUserDTO;
use App\Exceptions\SubscriptionException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeLocalRequest;
use App\Http\Requests\Tenant\TenantUserRequest;
use App\Http\Resources\Tenant\UserResource;
use App\Services\Tenant\Actions\User\CreateTenantUserService;
use App\Services\Tenant\Actions\User\DeleteTenantUserService;
use App\Services\Tenant\Actions\User\UpdateTenantUserService;
use App\Services\Tenant\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(
        protected readonly CreateTenantUserService $createTenantUserService,
        protected readonly DeleteTenantUserService $deleteTenantUserService,
        protected readonly UserService $userService,
        protected readonly UpdateTenantUserService $updateTenantUserService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $limit = $request->limit ?? 15;

        $users = $this->userService->paginate(filters: $filters, limit: $limit);

        return UserResource::collection($users);
    }

    public function show($id)
    {
        $tenantUser = $this->userService->findById(id: $id, withRelation: ['roles', 'department']);

        return ApiResponse::success(data: UserResource::make($tenantUser));
    }

    public function store(TenantUserRequest $request)
    {
        try {
            $tenantUserDTO = TenantUserDTO::fromRequest($request);
            $tenantUserDTO->send_credential_email = true;
            $tenantUserDTO->email_verified_at = now();
            $this->createTenantUserService->handle($tenantUserDTO);

            return ApiResponse::success(message: 'User created successfully.');
        } catch (SubscriptionException $exception) {
            $this->rollbackTransactionForBothConnection();

            return ApiResponse::badRequest($exception->getMessage());
        } catch (\Exception $exception) {
            $this->rollbackTransactionForBothConnection();

            return ApiResponse::serverError($exception->getMessage());
        }

    }

    public function update(TenantUserRequest $request, $id)
    {
        try {
            $tenantUserDTO = TenantUserDTO::fromRequest($request);
            $this->updateTenantUserService->handle(tenantUserDTO: $tenantUserDTO, tenant_user_id: $id);

            return ApiResponse::success(message: 'User updated successfully.');
        } catch (\Exception $exception) {
            $this->rollbackTransactionForBothConnection();

            return ApiResponse::serverError($exception->getMessage());
        }

    }

    public function destroy($id)
    {
        $this->deleteTenantUserService->handle($id);

        return ApiResponse::success(message: 'User deleted successfully.');
    }

    public function updateLocale(ChangeLocalRequest $request)
    {
        $user = auth()->user();
        $user->locale = $request->input('locale');
        $user->save();

        return ApiResponse::success(message: 'Locale updated successfully.');
    }

    private function rollbackTransactionForBothConnection()
    {
        DB::connection('tenant')->rollBack();
        DB::connection('landlord')->rollBack();
    }
}
