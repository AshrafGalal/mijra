<?php

namespace App\Models\Landlord;

use App\Enum\ActivationStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Role extends \Spatie\Permission\Models\Role
{
    use UsesLandlordConnection;

    protected $fillable = ['name', 'guard_name', 'is_active', 'description'];

    protected $table = 'roles';

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'model_has_roles', 'role_id', 'model_id')
            ->where('model_type', Admin::class);
    }
}
