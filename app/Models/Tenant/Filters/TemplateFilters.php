<?php

namespace App\Models\Tenant\Filters;

use App\Abstracts\QueryFilter;
use Illuminate\Support\Arr;

class TemplateFilters extends QueryFilter
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
}
