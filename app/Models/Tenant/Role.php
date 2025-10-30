<?php

namespace App\Models\Tenant;

use App\Enum\ActivationStatusEnum;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Role extends \Spatie\Permission\Models\Role
{
    use Filterable, UsesTenantConnection;

    const ADMIN = 'admin';

    protected $fillable = ['name', 'guard_name', 'is_active', 'description'];

    protected $table = 'roles';

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id')
            ->where('model_type', User::class);
    }
}
