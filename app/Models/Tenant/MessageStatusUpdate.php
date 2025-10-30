<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageStatusUpdate extends BaseTenantModel
{
    protected $fillable = [
        'message_id',
        'status',
        'status_at',
        'metadata',
    ];

    protected $casts = [
        'status_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Get the message that owns the status update.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}

