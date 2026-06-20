<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['usuario', 'tecnico', 'administrador'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@servicios.com'],
            [
                'name'              => 'Administrador',
                'password'          => Hash::make('sistema'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('administrador');
    }
}
