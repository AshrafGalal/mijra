<?php

namespace App\Models\Tenant;

class OpportunityItem extends BaseTenantModel
{
    protected $fillable = [
        'opportunity_id', 'item_id', 'price', 'quantity', 'discount', 'total',
    ];
}
