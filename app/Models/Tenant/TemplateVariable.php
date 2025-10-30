<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVariable extends BaseTenantModel
{
    protected $fillable = [
        'template_id',
        'variable_name',
        //        'default_value',
        'is_required',
        'source',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the template that owns this parameter
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
