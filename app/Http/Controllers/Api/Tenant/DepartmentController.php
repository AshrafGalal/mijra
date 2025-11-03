<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\DepartmentDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DepartmentRequest;
use App\Http\Resources\Tenant\DepartmentResource;
use App\Services\Tenant\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(protected DepartmentService $departmentService) {}

    public function index(Request $request)
    {
        $filters = $request->all();

        return DepartmentResource::collection($this->departmentService->list(filters: $filters));
    }

    public function show($id)
    {
        $department = $this->departmentService->findById($id);

        return ApiResponse::success(data: DepartmentResource::make($department));
    }

    public function store(DepartmentRequest $request)
    {
        $dto = DepartmentDTO::fromRequest($request);

        $this->departmentService->create($dto);

        return ApiResponse::success(message: 'Department created successfully.');
    }

    public function update(DepartmentRequest $request, $department)
    {
        $dto = DepartmentDTO::fromRequest($request);
        $this->departmentService->update($department, $dto);

        return ApiResponse::success(message: 'Department updated successfully.');
    }

    public function destroy($department)
    {
        $this->departmentService->delete($department);

        return ApiResponse::success(message: 'Department deleted successfully.');
    }
}
