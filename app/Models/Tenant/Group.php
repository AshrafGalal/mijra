<?php

namespace App\Models\Tenant;

use App\Enum\ActivationStatusEnum;

class Group extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color',
    ];

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
    ];
}
