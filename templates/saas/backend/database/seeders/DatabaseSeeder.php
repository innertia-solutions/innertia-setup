<?php

namespace Database\Seeders;

use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Innertia\Facades\Permissions;
use Innertia\Auth\RBAC\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sync permissions defined in config/innertia.php
        Permissions::sync();

        // 2. Create super-admin role (bypasses all gates via Gate::before in InnertiaServiceProvider)
        $role = Role::firstOrCreate(['name' => 'super-admin']);

        // 3. Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('Admin1234!'),
            ]
        );

        $admin->assignRole($role);

        // 4. Grant app access (requiere tenant activo — correr con tenant activado)
        if (\Innertia\Facades\Innertia::tenant()) {
            foreach (array_keys(config('innertia.apps', [])) as $app) {
                if (! $admin->hasApp($app)) {
                    $admin->grantApp($app);
                }
            }
        }
    }
}
