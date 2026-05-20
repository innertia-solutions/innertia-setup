<?php

namespace Database\Seeders;

use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Innertia\Auth\RBAC\Models\Role;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $viewerRole = Role::firstOrCreate(
            ['name' => 'viewer'],
            ['description' => 'Acceso de solo lectura']
        );

        $demoUsers = [
            [
                'name'     => 'Demo User',
                'email'    => 'demo@demo.com',
                'password' => Hash::make('Demo1234!'),
                'role'     => $viewerRole,
            ],
        ];

        foreach ($demoUsers as $data) {
            $role = $data['role'];
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => $data['password'],
                ]
            );
            $user->assignRole($role);
            $user->grantApp('backoffice');
        }
    }
}
