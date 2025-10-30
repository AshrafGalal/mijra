<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\WorkHourDTO;
use App\Models\Tenant\WorkHour;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class WorkHourService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return WorkHour::query();
    }

    /**
     * @throws \Throwable
     */
    public function list(array $filters = []): Collection
    {
        return $this->getQuery($filters)->get();
    }

    /*
     * work_days data like this :
     * ['sunday'=>[attr]]
     */
    /**
     * @throws \Throwable
     */
    public function saveWorkHours(WorkHourDTO $workHourDTO): void
    {
        $workHours = $workHourDTO->workHours;
        if (empty($workHours)) {
            throw new BadRequestException('at lease on day is provided');
        }
        DB::connection('tenant')->transaction(function () use ($workHours) {
            foreach ($workHours as $workDay) {
                $this->getQuery()->updateOrCreate(['day' => Arr::get($workDay, 'day')], Arr::except($workDay, 'day'));
            }
        });

    }

    public function toggleDayClosedFlag(int $id): bool
    {
        $workDay = $this->findById($id);
        $workDay->is_closed = ! $workDay->is_closed;
        $workDay->save();

        return $workDay->save();
    }
}
