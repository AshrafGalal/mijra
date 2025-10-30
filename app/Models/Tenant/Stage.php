<?php

namespace App\Models\Tenant;

use App\Enum\ActivationStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stage extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'is_active',
        'workflow_id',
    ];

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    protected static function booted()
    {
        parent::boot();

        static::creating(function ($stage) {
            $max = static::max('sort_order');
            $stage->sort_order = $max ? $max + 1 : 1;
        });
    }
}
