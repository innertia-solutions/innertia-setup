<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Innertia\Facades\Innertia;
use Innertia\Facades\Permissions;
use Innertia\Saas\UseCases\CreateTenant;
use Innertia\Saas\UseCases\CreateTenantAdmin;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantKey  = env('SEED_TENANT_KEY', 'dev');
        $tenantName = env('SEED_TENANT_NAME', 'Dev Tenant');
        $email      = env('SEED_ADMIN_EMAIL', "admin@{$tenantKey}.com");
        $password   = env('SEED_ADMIN_PASSWORD', 'Admin1234!');

        // 1. Sync permissions
        Permissions::sync();

        // 2. Create tenant (skip if already exists)
        try {
            (new CreateTenant($tenantKey, $tenantName, 'active'))->execute();
        } catch (\Innertia\Exceptions\ConflictException) {
            // already exists — continue
        }

        // 3. Activate tenant and create admin user
        Innertia::activate($tenantKey);

        (new CreateTenantAdmin(email: $email, password: $password))->execute();

        Innertia::deactivate();

        $this->command->info("Tenant: {$tenantKey} | Admin: {$email} | Password: {$password}");
    }
}
