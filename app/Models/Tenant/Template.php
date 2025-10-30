<?php

namespace App\Models\Tenant;

use App\Enum\TemplateStatusEnum;
use App\Enum\TemplateTypeEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Template extends BaseTenantModel implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'category',
        'template_type',
        'whatsapp_number',
        'content',
        'header_content',
        'footer_content',
        'status',
    ];

    protected $casts = [
        'status' => TemplateStatusEnum::class,
        'template_type' => TemplateTypeEnum::class,
    ];

    public function buttons(): HasMany
    {
        return $this->hasMany(TemplateButton::class)->orderBy('sort_order');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(TemplateVariable::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
