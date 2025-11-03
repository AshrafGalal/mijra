<?php

namespace App\Models\Tenant;

use App\Enum\ConversationTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Conversation extends BaseTenantModel implements HasMedia
{
    use HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'external_identifier_id',
        'tenant_platform_id',
        'last_message_id',
        'last_message_at',
        'unread_count',
        'contact_identifier_id',
        'contact_name',
        'title',
        'is_muted',
        'type',
        'platform',
        'metadata',
        'assigned_to',
        'platform_account_id',
        'platform_account_number',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'is_muted' => 'boolean',
        'type' => ConversationTypeEnum::class,
        'metadata' => 'array',
        'unread_count' => 'integer',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }
}
