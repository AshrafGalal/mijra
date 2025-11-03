<?php

namespace App\Models\Landlord;

use App\Enum\WhatsappPhoneStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappPhone extends BaseLandlordModel
{
    protected $fillable = [
        'tenant_id',
        'phone_number',
        'phone_label',
        'whatsapp_number',
        'status',
        'qr_code',
        'last_update',
        'connected_at',
        'error_message',
    ];

    protected $casts = [
        'status' => WhatsappPhoneStatusEnum::class,
        'last_update' => 'datetime',
        'connected_at' => 'datetime',
    ];

    protected $hidden = [
        'qr_code', // Hide in general listings for security
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
