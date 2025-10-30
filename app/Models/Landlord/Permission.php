<?php

namespace App\Models\Landlord;

use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Permission extends \Spatie\Permission\Models\Permission
{
    use UsesLandlordConnection;
}
