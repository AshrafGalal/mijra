<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\CustomerDTO;
use App\Enum\CustomerStatusEnum;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Filters\CustomersFilter;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomerService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return CustomersFilter::class;
    }

    protected function baseQuery(): Builder
    {
        return Customer::query();
    }

    public function create(CustomerDTO $customerDTO): Customer
    {
        // get country name
        $countryData = countriesData()->firstWhere('dial_code', $customerDTO->country_code);
        $customerDTO->country = Arr::get($countryData, 'name');

        return $this->getQuery()->create($customerDTO->toArray());
    }

    public function paginate(array $filters = [], $limit = 15): LengthAwarePaginator
    {
        return $this->getQuery($filters)->paginate($limit);
    }

    public function statics()
    {
        $allStatuses = CustomerStatusEnum::cases();
        $counts = $this->getQuery()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
        $normalized = collect($allStatuses)->mapWithKeys(fn ($status) => [
            $status->getLabel() => $counts->get($status->value, 0),
        ]);
        $total = $counts->sum();

        return [
            'total' => $total,
            'statuses' => $normalized,
        ];
    }

    public function update(string $id, CustomerDTO $customerDTO): Model
    {
        // get country name
        $countryData = countriesData()->firstWhere('dial_code', $customerDTO->country_code);
        $customerDTO->country = Arr::get($countryData, 'name');
        $customer = $this->findById($id);
        $customer->update($customerDTO->toArray());

        return $customer->fresh();
    }

    public function delete(int $id): bool
    {
        $customer = $this->findById($id);

        return $customer->delete();
    }
}
