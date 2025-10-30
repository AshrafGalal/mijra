<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\GroupDTO;
use App\Models\Tenant\Group;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GroupService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Group::query();
    }

    public function groups(array $filters = []): Collection
    {
        return $this->getQuery($filters)
            ->get();
    }

    /**
     * @throws \Throwable
     */
    public function create(GroupDTO $groupDTO): Group
    {
        return $this->getQuery()->create($groupDTO->toArray());
    }

    /**
     * @throws \Throwable
     */
    public function update(Group|int $group, GroupDTO $groupDTO): void
    {
        if (is_int($group)) {
            $group = parent::findById($group);
        }
        $group->update($groupDTO->toArray());
    }

    public function delete(Group|int $group): ?bool
    {
        if (is_int($group)) {
            $group = parent::findById($group);
        }

        return $group->delete();
    }
}
