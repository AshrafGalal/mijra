<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\WorkHourDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\WorkHourRequest;
use App\Http\Resources\Tenant\WorkHourResource;
use App\Services\Tenant\WorkHourService;
use Illuminate\Http\Request;

class WorkHourController extends Controller
{
    public function __construct(protected WorkHourService $workHourService) {}

    public function index(Request $request)
    {
        return ApiResponse::success(data: WorkHourResource::collection($this->workHourService->list()));
    }

    public function toggleDayClosedFlag($id)
    {
        $this->workHourService->toggleDayClosedFlag($id);

        return ApiResponse::success(message: 'work hours closed flag updated successfully.');
    }

    /**
     * @throws \Throwable
     */
    public function saveWorkHour(WorkHourRequest $request)
    {
        $dto = WorkHourDTO::fromRequest($request);
        $this->workHourService->saveWorkHours($dto);

        return ApiResponse::success(message: 'Work hours update successfully.');
    }
}
