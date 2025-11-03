<?php

namespace App\Models\Tenant\Filters;

use App\Abstracts\QueryFilter;
use Illuminate\Support\Arr;

class CustomersFilter extends QueryFilter
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

    public function email($term)
    {
        return $this->builder->where('email', $term);
    }

    public function phone($term)
    {
        return $this->builder->where('phone', $term);
    }

    public function status($term)
    {
        return $this->builder->where('status', $term);
    }

    public function country_code($term)
    {
        return $this->builder->whereIn('country_code', Arr::wrap($term));
    }

    public function name($term)
    {
        return $this->builder->where(function ($query) use ($term) {
            $query->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%");
        });
    }
}
