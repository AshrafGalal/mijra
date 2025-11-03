<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\StageDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\MoveStageRequest;
use App\Http\Requests\Tenant\StageRequest;
use App\Http\Resources\Tenant\StageResource;
use App\Services\Tenant\StageService;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(protected StageService $stageService) {}

    public function index(Request $request, $pipeline)
    {
        $filters = $request->all();
        $filters['workflow_id'] = $pipeline;

        return StageResource::collection($this->stageService->getStages($filters));
    }

    public function show($id)
    {
        $stage = $this->stageService->findById($id);

        return ApiResponse::success(data: StageResource::make($stage));
    }

    public function store(StageRequest $request)
    {
        $dto = StageDTO::fromRequest($request);
        $this->stageService->create($dto);

        return ApiResponse::success(message: 'Stage created successfully.');
    }

    public function update(StageRequest $request, $stage)
    {
        $dto = StageDTO::fromRequest($request);
        $this->stageService->update(stage: $stage, dto: $dto);

        return ApiResponse::success(message: 'Stage updated successfully.');
    }

    public function destroy($pipeline)
    {
        $this->stageService->delete($pipeline);

        return ApiResponse::success(message: 'Stage deleted successfully.');
    }

    public function move(MoveStageRequest $request)
    {
        $this->stageService->move(stageId: $request->stage_id, direction: $request->direction);

        return ApiResponse::success(message: 'Stage sorted successfully.');
    }
}
