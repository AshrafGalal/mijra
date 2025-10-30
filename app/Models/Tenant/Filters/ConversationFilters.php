<?php

namespace App\Models\Tenant\Filters;

use App\Abstracts\QueryFilter;

class ConversationFilters extends QueryFilter
{
    /**
     * Filter by status.
     */
    public function status($value)
    {
        return $this->builder->where('status', $value);
    }

    /**
     * Filter by platform.
     */
    public function platform($value)
    {
        return $this->builder->where('platform', $value);
    }

    /**
     * Filter by assigned user.
     */
    public function assigned_to($value)
    {
        if ($value === 'unassigned') {
            return $this->builder->whereNull('assigned_to');
        }
        
        return $this->builder->where('assigned_to', $value);
    }

    /**
     * Filter by customer ID.
     */
    public function customer_id($value)
    {
        return $this->builder->where('customer_id', $value);
    }

    /**
     * Filter by tag.
     */
    public function tag($value)
    {
        return $this->builder->whereHas('tags', function ($query) use ($value) {
            $query->where('conversation_tags.id', $value);
        });
    }

    /**
     * Filter unread conversations.
     */
    public function unread($value)
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return $this->builder->where('unread_count', '>', 0);
        }

        return $this->builder;
    }

    /**
     * Search in customer name or phone.
     */
    public function search($value)
    {
        return $this->builder->whereHas('customer', function ($query) use ($value) {
            $query->where('name', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%");
        });
    }

    /**
     * Filter by date range.
     */
    public function date_from($value)
    {
        return $this->builder->where('created_at', '>=', $value);
    }

    /**
     * Filter by date range.
     */
    public function date_to($value)
    {
        return $this->builder->where('created_at', '<=', $value);
    }

    /**
     * Sort order.
     */
    public function sort($value)
    {
        $direction = 'desc';
        $column = 'last_message_at';

        if (str_starts_with($value, '-')) {
            $direction = 'desc';
            $column = substr($value, 1);
        } elseif (str_starts_with($value, '+')) {
            $direction = 'asc';
            $column = substr($value, 1);
        } else {
            $column = $value;
        }

        $allowedColumns = ['created_at', 'last_message_at', 'unread_count', 'message_count'];
        
        if (in_array($column, $allowedColumns)) {
            return $this->builder->orderBy($column, $direction);
        }

        return $this->builder;
    }
}

