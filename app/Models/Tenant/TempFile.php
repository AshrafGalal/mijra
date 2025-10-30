<?php

namespace App\Models\Tenant;

use Illuminate\Support\Facades\Storage;

class TempFile extends BaseTenantModel
{
    protected $fillable = [
        'file_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected static function booted()
    {
        parent::boot();

        static::deleted(function ($tempFile) {
            if ($tempFile->path && Storage::exists($tempFile->path)) {
                Storage::delete($tempFile->path);
            }
        });
    }
}
