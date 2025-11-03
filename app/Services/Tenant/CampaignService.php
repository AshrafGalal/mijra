<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\CampaignDTO;
use App\Models\Campaign;
use App\Models\Filters\CampaignFilters;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CampaignService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return CampaignFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Campaign::query();
    }

    /**
     * @throws \Throwable
     */
    public function create(CampaignDTO $dto): Campaign
    {
        DB::transaction(function () use ($dto) {
            // first create campaign
            $campaign = $this->getQuery()->create($dto->toArrayExcept(['media', 'contacts_file']));
            // check if there is media

        });
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->getQuery($filters)->paginate();
    }

    public function update(int $id, CampaignDTO $dto): Model
    {
        $campaign = $this->findById($id);
        $campaign->update($dto->toArray());

        return $campaign;
    }

    public function delete(int $id): bool
    {
        $campaign = $this->findById($id);

        return $campaign->delete();
    }
}
