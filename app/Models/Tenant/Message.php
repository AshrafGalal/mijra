<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends BaseTenantModel implements HasMedia
{
    use HasUuids, InteractsWithMedia, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'has_media' => 'boolean',
        'is_forward' => 'boolean',
        'sender' => 'array',
        'received_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected $fillable = [
        'conversation_id',
        'external_message_id',
        'is_forward',
        'message_type',
        'direction',
        'status',
        'sender',
        'body',
        'has_media',
        'reply_to_message_id',
        'reply_to_external_message_id', // id of the message in the external system
        'received_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'emoji',
        'platform_account_id',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_external_message_id', 'external_message_id');
    }

    protected function receivedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d h:i A') : null,
        );
    }

    protected function sentAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d h:i A') : null,
        );
    }

    protected function deliveredAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d h:i A') : null,
        );
    }

    protected function readAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d h:i A') : null,
        );
    }
}
