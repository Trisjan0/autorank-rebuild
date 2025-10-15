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
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $evaluatorRole = Role::firstOrCreate(['name' => 'Evaluator']);
        Role::firstOrCreate(['name' => 'Instructor']);

        // Create Super Admin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@autorank.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@autorank.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Evaluator User
        $evaluator = User::firstOrCreate(
            ['email' => 'evaluator@autorank.com'],
            [
                'name' => 'Evaluator',
                'password' => Hash::make('password'),
            ]
        );
        $evaluator->assignRole($evaluatorRole);

        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
    }
}
