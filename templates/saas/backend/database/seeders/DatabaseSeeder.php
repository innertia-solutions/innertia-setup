<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Innertia\Auth\RBAC\Models\Role;
use Innertia\Exceptions\ConflictException;
use Innertia\Facades\Innertia;
use Innertia\Facades\Permissions;
use Innertia\Saas\UseCases\CreateTenant;
use Innertia\Saas\UseCases\CreateTenantAdmin;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantKey    = env('SEED_TENANT_KEY',     'dev');
        $tenantName   = env('SEED_TENANT_NAME',    'Dev Tenant');
        $adminEmail   = env('SEED_ADMIN_EMAIL',    "admin@{$tenantKey}.com");
        $adminPass    = env('SEED_ADMIN_PASSWORD', 'Admin1234!');
        $demoEmail    = env('SEED_DEMO_EMAIL',     null);
        $demoPassword = env('SEED_DEMO_PASSWORD',  null);

        // 1. Sync permissions
        Permissions::sync();

        // 2. Create tenant (skip if already exists)
        try {
            (new CreateTenant(
                key:          $tenantKey,
                name:         $tenantName,
                status:       'active',
                demoEmail:    $demoEmail,
                demoPassword: $demoPassword,
            ))->execute();
        } catch (ConflictException) {
            // already exists — continue
        }

        // 3. Activate tenant and create admin user
        Innertia::activate($tenantKey);

        try {
            (new CreateTenantAdmin(email: $adminEmail, password: $adminPass))->execute();
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // already exists — continue
        }

        // 4. Admin role with all permissions
        $tenantId  = (string) Innertia::tenant()->getKey();
        $adminRole = Role::findByName('Admin', $tenantId)
            ?? Role::createUnique('Admin', 'Acceso completo al sistema.', $tenantId);

        $adminRole->syncPermissions(Permissions::keys());

        Innertia::deactivate();

        $this->command->info("Tenant: {$tenantKey} | Admin: {$adminEmail} | Password: {$adminPass}");
    }
}
