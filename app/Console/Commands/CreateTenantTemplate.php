<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateTenantTemplate extends Command
{
    protected $signature = 'tenant:create-template';

    protected $description = 'Create or update tenant template database';

    public function handle()
    {
        $templateDb = config('database.tenant_template_db');

        // Drop existing template
        DB::connection('landlord')->statement("DROP DATABASE IF EXISTS `$templateDb`");

        // Create template database
        DB::connection('landlord')->statement("CREATE DATABASE `$templateDb`");

        // Dynamically set the tenant connection to point to template DB
        config([
            'database.connections.tenant.database' => $templateDb,
        ]);

        // Reconnect to apply the new config
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Run migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--force' => true,
        ]);

        // Run seeders (common data)
        Artisan::call('db:seed', [
            '--class' => 'TenantDatabaseSeeder',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $this->info('Tenant template database created successfully!');
    }
}
