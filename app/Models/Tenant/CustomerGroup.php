<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomerGroup extends Pivot
{
    use UsesTenantConnection;

    protected $fillable = ['customer_id', 'group_id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
