<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Innertia\Exceptions\ConflictException;
use Innertia\Facades\Innertia;
use Innertia\Facades\Permissions;
use Innertia\Saas\UseCases\CreateTenant;
use Innertia\Saas\UseCases\CreateTenantAdmin;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantKey  = env('SEED_TENANT_KEY',     'dev');
        $tenantName = env('SEED_TENANT_NAME',    'Dev Tenant');
        $adminEmail = env('SEED_ADMIN_EMAIL',    "admin@{$tenantKey}.com");
        $adminPass  = env('SEED_ADMIN_PASSWORD', 'Admin1234!');

        // 1. Sync permissions declared in config/innertia.php
        Permissions::sync();

        // 2. Create the dev tenant (active)
        try {
            (new CreateTenant(
                key:    $tenantKey,
                name:   $tenantName,
                status: 'active',
            ))->execute();
        } catch (ConflictException) {
            // already exists — continue
        }

        // 3. Create admin user.
        //    CreateTenantAdmin also:
        //      - grants access to every context in config('innertia.contexts')
        //      - assigns the super-admin role (bypasses all gates via Gate::before)
        //      - enables demo mode with these same credentials (shown on login page)
        Innertia::activate($tenantKey);

        try {
            (new CreateTenantAdmin(email: $adminEmail, password: $adminPass))->execute();
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // already exists — continue
        }

        Innertia::deactivate();

        $this->command->info("Tenant: {$tenantKey} | Login: {$adminEmail} | Password: {$adminPass}");
        $this->command->warn('Demo mode activo — estas credenciales aparecen en la pantalla de login.');
    }
}
