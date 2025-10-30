<?php

namespace App\Models\Tenant;

use App\Enum\ActivationStatusEnum;

// act as Pipeline
class Workflow extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
    ];

    public function stages()
    {
        return $this->hasMany(Stage::class)->orderBy('sort_order');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
