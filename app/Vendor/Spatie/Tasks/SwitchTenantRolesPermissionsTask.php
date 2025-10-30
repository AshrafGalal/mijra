<?php

namespace App\Vendor\Spatie\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Spatie\Permission\PermissionRegistrar;

class SwitchTenantRolesPermissionsTask implements SwitchTenantTask
{
    protected mixed $originalRoleModel;

    protected mixed $originalPermissionModel;

    public function __construct()
    {
        $this->originalRoleModel = config('permission.models.role');
        $this->originalPermissionModel = config('permission.models.permission');
    }

    public function makeCurrent(Tenant|IsTenant $tenant): void
    {
        $this->setTenantPermissionConfig($tenant);
        $this->refreshPermissionSystem();
    }

    public function forgetCurrent(): void
    {
        $this->resetPermissionConfig();
        $this->refreshPermissionSystem();

    }

    /**
     * Set tenant-specific permission configuration
     */
    protected function setTenantPermissionConfig(Tenant $tenant): void
    {
        // Set tenant-specific cache key
        $cacheKey = "spatie.permission.cache.tenant.{$tenant->id}";
        config(['permission.cache.key' => $cacheKey]);

        // Set tenant-specific models
        config([
            'permission.models.role' => 'App\\Models\\Tenant\\Role',
            'permission.models.permission' => 'App\\Models\\Tenant\\Permission',
        ]);

    }

    /**
     * Reset permission configuration to default
     */
    protected function resetPermissionConfig(): void
    {
        config([
            'permission.cache.key' => 'spatie.permission.cache',
            'permission.models.role' => $this->originalRoleModel,
            'permission.models.permission' => $this->originalPermissionModel,
        ]);
    }

    /**
     * Refresh the permission registrar
     */
    protected function refreshPermissionSystem(): void
    {
        // Forget the singleton instance (clears the internal cache)
        app()->forgetInstance(PermissionRegistrar::class);

        // Clear Spatie’s cached permissions/roles
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
