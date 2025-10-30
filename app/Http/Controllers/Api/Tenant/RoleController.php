<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\RoleDTO;
use App\Enum\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Services\Tenant\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(protected readonly RoleService $roleService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $roles = $this->roleService->roles();

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $roleDTO = RoleDTO::fromRequest($request);
        $this->roleService->create($roleDTO);

        return ApiResponse::success(message: __('app.role_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $withRelations = ['permissions'];
        $role = $this->roleService->findById(id: $id, withRelation: $withRelations);

        return ApiResponse::success(data: RoleResource::make($role));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, int $role)
    {
        $roleDTO = RoleDTO::fromRequest($request);
        $this->roleService->update(role: $role, roleDTO: $roleDTO);

        return ApiResponse::success(message: __('app.role_updated_successfully ✅'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->roleService->delete($id);

        return ApiResponse::success(message: 'Role deleted successfully ✅');

    }

    public function permissionsList()
    {
        $permissionGroups = collect(PermissionsEnum::cases())
            ->groupBy(fn ($permission) => $permission->getGroup())
            ->map(function ($group) {
                return $group->map(fn ($permission) => [
                    'name' => $permission->getLabel(),
                    'value' => $permission->value,
                ])->values(); // Reset keys
            });

        return ApiResponse::success(data: $permissionGroups);
    }
}
