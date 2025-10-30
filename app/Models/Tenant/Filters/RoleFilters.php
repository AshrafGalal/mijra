<?php

namespace App\Models\Tenant\Filters;

use App\Abstracts\QueryFilter;
use App\Enum\ActivationStatusEnum;
use Illuminate\Support\Arr;

class RoleFilters extends QueryFilter
{
    public function __construct($params = [])
    {
        parent::__construct($params);
    }

    public function ids($term)
    {
        return $this->builder->whereIntegerInRaw('id', Arr::wrap($term));
    }

    public function idsNotIn($term)
    {
        return $this->builder->whereIntegerNotInRaw('id', Arr::wrap($term));
    }

    public function is_active()
    {
        return $this->builder->where('is_active', ActivationStatusEnum::ACTIVE->value);
    }
}
