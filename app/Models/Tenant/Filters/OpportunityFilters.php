<?php

namespace App\Models\Tenant\Filters;

use App\Abstracts\QueryFilter;
use Illuminate\Support\Arr;

class OpportunityFilters extends QueryFilter
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

    public function status($term)
    {
        return $this->builder->where('status', $term);
    }

    public function workflow_id($term)
    {
        return $this->builder->where('workflow_id', $term);
    }

    public function stage_id($term)
    {
        return $this->builder->where('stage_id', $term);
    }

    public function customer_id($term)
    {
        return $this->builder->where('customer_id', $term);
    }

    public function user_id($term)
    {
        return $this->builder->where('user_id', $term);
    }
}
