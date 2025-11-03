<?php

namespace App\Models\Tenant;

use App\Enum\OpportunityStatusEnum;
use App\Enum\PriorityEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends BaseTenantModel
{
    protected $fillable = [
        'customer_id', 'workflow_id', 'stage_id', 'user_id',
        'priority', 'status', 'source', 'notes', 'expected_close_date',
    ];

    protected $casts = [
        'status' => OpportunityStatusEnum::class,
        'priority' => PriorityEnum::class,
        'expected_close_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function OpportunityItems()
    {
        return $this->hasMany(OpportunityItem::class);
    }
}
