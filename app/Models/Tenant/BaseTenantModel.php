<?php

namespace App\Models\Tenant;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class BaseTenantModel extends Model
{
    use Filterable, HasFactory, UsesTenantConnection;
}
