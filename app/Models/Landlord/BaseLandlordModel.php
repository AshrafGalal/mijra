<?php

namespace App\Models\Landlord;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class BaseLandlordModel extends Model
{
    use Filterable, HasFactory, UsesLandlordConnection;
}
