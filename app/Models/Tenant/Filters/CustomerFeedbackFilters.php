<?php

namespace App\Models\Tenant\Filters;

use App\Abstracts\QueryFilter;

class CustomerFeedbackFilters extends QueryFilter
{
    public function __construct($params = [])
    {
        parent::__construct($params);
    }

    public function rating($term)
    {
        return $this->builder->where('rating', $term);
    }

    public function feedback_category_id($term)
    {
        return $this->builder->where('feedback_category_id', $term);
    }

    public function status($term)
    {
        return $this->builder->where('status', $term);
    }
}
