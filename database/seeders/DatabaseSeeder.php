<?php

namespace Database\Seeders;

use App\Models\Landlord\Tenant;
use Database\Seeders\Landlord\ActivationCodeTableSeeder;
use Database\Seeders\Landlord\AdminTableSeeder;
use Database\Seeders\Landlord\FeaturePlanTableSeeder;
use Database\Seeders\Landlord\FeatureTableSeeder;
use Database\Seeders\Landlord\InvoiceTableSeeder;
use Database\Seeders\Landlord\PlansTableSeeder;
use Database\Seeders\Landlord\SettingsTableSeeder;
use Database\Seeders\Landlord\SourcesTableSeeder;
use Database\Seeders\Landlord\TenantOwnerSeeder;
use Database\Seeders\Landlord\TenantsTableSeeder;
use Database\Seeders\Landlord\UsersTableSeeder;
use Database\Seeders\Tenant\CategoriesTableSeeder;
use Database\Seeders\Tenant\CustomersTableSeeder;
use Database\Seeders\Tenant\DepartmentsTableSeeder;
use Database\Seeders\Tenant\GroupsTableSeeder;
use Database\Seeders\Tenant\PermissionsTableSeeder;
use Database\Seeders\Tenant\RefershConversationAndMessagesTable;
use Database\Seeders\Tenant\RolesTableSeeder;
use Database\Seeders\Tenant\StagesTableSeeder;
use Database\Seeders\Tenant\TasksTableSeeder;
use Database\Seeders\Tenant\WorkflowsTableSeeder;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::checkCurrent()
            ? $this->runTenantSpecificSeeders()
            : $this->runLandlordSpecificSeeders();
    }

    public function runTenantSpecificSeeders(): void
    {
        $this->call(RefershConversationAndMessagesTable::class);
//        $this->call(RolesTableSeeder::class);
//        $this->call(CustomersTableSeeder::class);
//        $this->call(\Database\Seeders\Tenant\UsersTableSeeder::class);
//        $this->call(DepartmentsTableSeeder::class);
//        $this->call(GroupsTableSeeder::class);
//        $this->call(CategoriesTableSeeder::class);
//        $this->call(WorkflowsTableSeeder::class);
//        $this->call(StagesTableSeeder::class);
//        $this->call(TasksTableSeeder::class);

    }

    public function runLandlordSpecificSeeders(): void
    {
        $this->call(Landlord\PermissionsTableSeeder::class);

        $this->call(SourcesTableSeeder::class);

        $this->call(AdminTableSeeder::class);

        $this->call(PlansTableSeeder::class);

        $this->call(SettingsTableSeeder::class);

        $this->call(FeatureTableSeeder::class);

        $this->call(FeaturePlanTableSeeder::class);

        $this->call(TenantsTableSeeder::class);

        $this->call(UsersTableSeeder::class);

        $this->call(ActivationCodeTableSeeder::class);

        $this->call(TenantOwnerSeeder::class);

        $this->call(InvoiceTableSeeder::class);
    }
}
