<?php

namespace App\Models\Tenant;

use App\Enum\DayOfWeekEnum;

class WorkHour extends BaseTenantModel
{
    protected $fillable = [
        'day', 'from', 'to', 'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'day' => DayOfWeekEnum::class,
    ];
}
