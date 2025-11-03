<?php

namespace App\Models\Tenant;

use App\Enum\PriorityEnum;
use App\Enum\TaskStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends BaseTenantModel implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'description', 'status', 'customer_id', 'user_id',
        'priority', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'status' => TaskStatusEnum::class,
        'priority' => PriorityEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
