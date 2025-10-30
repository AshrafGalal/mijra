<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\WorkflowDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\WorkflowRequest;
use App\Http\Resources\Tenant\WorkflowResource;
use App\Services\Tenant\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(protected WorkflowService $pipelineService) {}

    public function index(Request $request)
    {
        $filters = $request->all();

        return WorkflowResource::collection($this->pipelineService->getPipelines(filters: $filters, countRelations: ['stages']));
    }

    public function show($id)
    {
        $pipeline = $this->pipelineService->findById(id: $id, withRelation: ['stages']);

        return ApiResponse::success(data: WorkflowResource::make($pipeline));
    }

    public function store(WorkflowRequest $request)
    {
        $dto = WorkflowDTO::fromRequest($request);
        $this->pipelineService->create($dto);

        return ApiResponse::success(message: 'Pipeline created successfully.');
    }

    public function update(WorkflowRequest $request, $pipeline)
    {
        $dto = WorkflowDTO::fromRequest($request);
        $this->pipelineService->update(pipeline: $pipeline, dto: $dto);

        return ApiResponse::success(message: 'Pipeline updated successfully.');
    }

    public function destroy($pipeline)
    {
        $this->pipelineService->delete($pipeline);

        return ApiResponse::success(message: 'Pipeline deleted successfully.');
    }
}
