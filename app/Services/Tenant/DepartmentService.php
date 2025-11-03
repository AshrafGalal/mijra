<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\DepartmentDTO;
use App\Models\Tenant\Department;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;

class DepartmentService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Department::query();
    }

    public function create(DepartmentDTO $dto): Department
    {
        return $this->baseQuery()->create($dto->toArray());
    }

    public function update(Department|int $department, DepartmentDTO $dto): Department
    {
        if (is_int($department)) {
            $department = parent::findById($department);
        }
        $department->update($dto->toArray());

        return $department;
    }

    public function delete(Department|int $department): ?bool
    {
        if (is_int($department)) {
            $department = parent::findById($department);
        }

        return $department->delete();
    }

    public function paginate(?array $filters = [], int $limit = 15)
    {
        return $this->getQuery($filters)->paginate($limit);
    }

    public function list(?array $filters = [])
    {
        return $this->getQuery($filters)->get();
    }
}
