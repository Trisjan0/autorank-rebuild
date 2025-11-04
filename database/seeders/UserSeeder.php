<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $validatorRole = Role::firstOrCreate(['name' => 'Validator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'web']);

        $superAdminEmail = env('DEFAULT_SUPER_ADMIN_EMAIL', 'superadmin@autorank.com');
        $superAdminPassword = env('DEFAULT_SUPER_ADMIN_PASSWORD', 'password');

        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@autorank.com');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', 'password');

        $validatorEmail = env('DEFAULT_VALIDATOR_EMAIL', 'validator@autorank.com');
        $validatorPassword = env('DEFAULT_VALIDATOR_PASSWORD', 'password');

        // Create Super Admin 
        $superAdmin = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($superAdminPassword),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Validator
        $validator = User::firstOrCreate(
            ['email' => $validatorEmail],
            [
                'name' => 'Validator',
                'password' => Hash::make($validatorPassword),
            ]
        );
        $validator->assignRole($validatorRole);

        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
    }
}
