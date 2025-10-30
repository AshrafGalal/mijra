<?php

namespace App\Models\Tenant;

use App\Enum\CustomerSourceEnum;
use App\Enum\CustomerStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Customer extends BaseTenantModel
{
    protected $fillable = [
        'first_name', 'last_name', 'country_code', 'phone', 'email', 'notes',
        'source', 'address', 'country', 'city', 'zipcode', 'status', 'tags',
    ];

    protected $casts = [
        'source' => CustomerSourceEnum::class,
        'status' => CustomerStatusEnum::class,
        'tags' => 'array',
    ];

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name.' '.$this->last_name,
        );
    }
}
