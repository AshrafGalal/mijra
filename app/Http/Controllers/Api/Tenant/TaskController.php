<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\TaskDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ChangeTaskStatusRequest;
use App\Http\Requests\Tenant\TaskRequest;
use App\Http\Resources\Tenant\TaskResource;
use App\Services\Tenant\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $limit = $request->input('limit', 15);

        return TaskResource::collection($this->taskService->paginate(filters: $filters, limit: $limit));
    }

    public function show($id)
    {
        $task = $this->taskService->findById(id: $id, withRelation: [
            'user:id,name,phone',
            'customer:id,first_name,last_name,phone',
            'media',
        ]);

        return ApiResponse::success(data: TaskResource::make($task));
    }

    public function store(TaskRequest $request)
    {
        $dto = TaskDTO::fromRequest($request);
        $this->taskService->create($dto);

        return ApiResponse::success(message: 'Task created successfully.');
    }

    public function update(TaskRequest $request, $task)
    {
        $dto = TaskDTO::fromRequest($request);
        $this->taskService->update(task: $task, dto: $dto);

        return ApiResponse::success(message: 'Task updated successfully.');
    }

    public function destroy($task)
    {
        $this->taskService->delete($task);

        return ApiResponse::success(message: 'Task deleted successfully.');
    }

    public function changeStatus(ChangeTaskStatusRequest $request, $task)
    {
        $this->taskService->changeStatus(task_id: $task, status: $request->status);

        return ApiResponse::success(message: 'Task status updated successfully.');
    }

    public function statics()
    {
        $statics = $this->taskService->statics();

        return ApiResponse::success(data: $statics);
    }
}
