<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $evaluatorRole = Role::firstOrCreate(['name' => 'Evaluator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'web']);

        // Create Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@autorank.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('sredlohecalpa'),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@autorank.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('aredlohecalp'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Evaluator User
        $evaluator = User::firstOrCreate(
            ['email' => 'evaluator@autorank.com'],
            [
                'name' => 'Evaluator',
                'password' => Hash::make('eredlohecalp'),
            ]
        );
        $evaluator->assignRole($evaluatorRole);

        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
    }
}
