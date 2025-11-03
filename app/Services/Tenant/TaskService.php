<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\TaskDTO;
use App\Enum\TaskStatusEnum;
use App\Models\Tenant\Filters\TaskFilters;
use App\Models\Tenant\Task;
use App\Services\BaseService;
use App\Services\UploadFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskService extends BaseService
{
    public function __construct(private UploadFileService $uploadFileService) {}

    protected function getFilterClass(): ?string
    {
        return TaskFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Task::query();
    }

    public function paginate(array $filters = [], $limit = 15): LengthAwarePaginator
    {
        return $this->getQuery($filters)->with([
            'user:id,name,phone',
            'customer:id,first_name,last_name,phone',
            'media',
        ])->paginate($limit);
    }

    public function create(TaskDTO $dto): Task
    {
        return DB::connection('tenant')->transaction(function () use ($dto) {
            $task = $this->baseQuery()->create($dto->toArray());
            $this->uploadFileService->assignMediaToModel(media_ids: $dto->media_ids, model: $task, collection_name: 'tasks');

            return $task;
        });

    }

    public function update(Task|int $task, TaskDTO $dto): Task
    {
        if (is_int($task)) {
            $task = parent::findById($task);
        }
        $task->update($dto->toArray());

        return $task;
    }

    public function delete(Task|int $task): ?bool
    {
        if (is_int($task)) {
            $task = parent::findById($task);
        }

        return $task->delete();
    }

    public function changeStatus(int $task_id, $status)
    {
        $taskModel = $this->findById($task_id);
        if ($taskModel->status == $status) {
            return;
        }
        $taskModel->status = $status;
        if ($status == TaskStatusEnum::COMPLETED->value) {
            $taskModel->completed_at = now();
        }
        $taskModel->save();
    }

    public function statics()
    {
        // Dynamically get enum cases
        $statuses = TaskStatusEnum::cases();
        // Build a query with dynamic CASE counts
        $query = $this->baseQuery()
            ->selectRaw('COUNT(*) as TOTAL_TASKS');

        foreach ($statuses as $status) {
            $query->selectRaw(
                "SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as TOTAL_{$status->name}",
                [$status->value]
            );
        }

        return $query->first();

    }
}
