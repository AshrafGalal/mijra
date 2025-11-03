<?php

namespace App\Models\Tenant;

use App\Enum\ActivationStatusEnum;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Translatable\HasTranslations;

class Category extends BaseTenantModel
{
    use HasTranslations, NodeTrait;

    protected $fillable = [
        'name',
        'is_active',
        'parent_id',
        'description',
    ];

    public $translatable = ['name']; // translatable attributes

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
    ];

    protected static function boot(): void
    {
        parent::boot();
        //        static::creating(function ($category) {
        //            $englishName = $category->getTranslation('name', 'en');
        //            $category->slug = $englishName
        //                ? Str::slug($englishName)
        //                : 'cat-' . Str::random(8);
        //            $category->slug = Str::slug($category->getTranslations('name','en'));
        //        });
    }
}
