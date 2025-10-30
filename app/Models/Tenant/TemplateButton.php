<?php

namespace App\Models\Tenant;

use App\Enum\ButtonTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateButton extends BaseTenantModel
{
    protected $fillable = [
        'button_text',
        'button_type',
        'action_value',
        'background_color',
        'text_color',
        'sort_order',
    ];

    protected $casts = [
        'button_type' => ButtonTypeEnum::class,
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function isWhatsAppButton(): bool
    {
        return $this->button_type == ButtonTypeEnum::WHATSAPP;
    }

    public function isPhoneButton(): bool
    {
        return $this->button_type == ButtonTypeEnum::PHONE;
    }

    public function isUrlButton(): bool
    {
        return $this->button_type == ButtonTypeEnum::URL;
    }

    public function isEmailButton(): bool
    {
        return $this->button_type == ButtonTypeEnum::EMAIL;
    }

    public function isQuickReplyButton(): bool
    {
        return $this->button_type == ButtonTypeEnum::QUICK_REPLY;
    }
}
