<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\OpportunityDTO;
use App\Enum\OpportunityViewTypeEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\MoveOpportunityRequest;
use App\Http\Requests\Tenant\OpportunityRequest;
use App\Http\Resources\Tenant\Opportunity\OpportunityPipelineResource;
use App\Http\Resources\Tenant\Opportunity\OpportunityResource;
use App\Services\Tenant\OpportunityService;
use App\Services\Tenant\StageService;
use App\Services\Tenant\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OpportunityController extends Controller
{
    public function __construct(
        protected OpportunityService $opportunityService,
        protected readonly StageService $stageService,
        protected readonly WorkflowService $workflowService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters = array_filter($filters, fn ($value) => ! is_null($value) && $value !== '');
        $pipeline_id = Arr::get($filters, 'workflow_id');
        $view_type = in_array($request->view_type, OpportunityViewTypeEnum::values()) ? $request->view_type : OpportunityViewTypeEnum::PIPELINE_VIEW->value;
        if ($view_type == OpportunityViewTypeEnum::PIPELINE_VIEW->value && $pipeline_id) {
            return $this->pipelinePageView($filters);
        } else {
            return $this->listAndGridPageView($filters);
        }

    }

    public function statics() {}

    public function show($id)
    {
        $withRelations = ['stage', 'customer:id,first_name,last_name,phone', 'user:id,name', 'workflow:id,name'];
        $opportunity = $this->opportunityService->findById(id: $id, withRelation: $withRelations);

        return OpportunityResource::make($opportunity);
    }

    /**
     * @throws \Throwable
     */
    public function store(OpportunityRequest $request)
    {
        $opportunityDTO = OpportunityDTO::fromRequest($request);
        $this->opportunityService->create($opportunityDTO);

        return ApiResponse::success(message: 'Opportunity created successfully.');
    }

    public function destroy($opportunity)
    {
        $this->opportunityService->delete($opportunity);

        return ApiResponse::success(message: 'Opportunity deleted successfully.');
    }

    private function pipelinePageView($filters, $limit = 15)
    {
        $withRelations = ['stage', 'customer:id,first_name,last_name,phone,status,source', 'user:id,name,email,phone'];
        $opportunities = $this->opportunityService->paginate(filters: $filters, withRelations: $withRelations, perPage: $limit);
        $stages = $this->stageService->getStagesByWorkflowId(Arr::get($filters, 'workflow_id'));
        $workflow = $this->workflowService->findById($filters['workflow_id']);

        return new OpportunityPipelineResource($workflow, $stages, $opportunities);
    }

    private function listAndGridPageView($filters, $limit = 15)
    {
        $withRelations = ['stage', 'customer:id,first_name,last_name,phone,status,source', 'user:id,name', 'workflow'];
        $opportunities = $this->opportunityService->paginate(filters: $filters, withRelations: $withRelations, perPage: $limit);

        return OpportunityResource::collection($opportunities);
    }

    /**
     * Move opportunity with resource response
     */
    public function moveOpportunity(MoveOpportunityRequest $request)
    {
        try {
            $this->opportunityService->move(opportunityId: $request->opportunity_id, newStageId: $request->stage_id);

            return ApiResponse::success(message: 'Opportunity moved successfully.');
        } catch (BadRequestException $e) {
            return ApiResponse::error(message: $e->getMessage());
        }
    }
}
