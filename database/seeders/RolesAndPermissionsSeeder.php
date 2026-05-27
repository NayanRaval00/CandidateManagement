<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Create default admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@yopmail.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Assign employee role to existing  user
        $testUser = User::where('email', 'nayan@yopmail.com')->first();
        if ($testUser && !$testUser->hasRole('employee')) {
            $testUser->assignRole($employeeRole);
        }

        // Seed default leave types
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'annual',
                'description' => 'Regular annual leave balance allocated to employees.',
                'default_balance' => 15,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'sick',
                'description' => 'Allocated leave for medical purposes.',
                'default_balance' => 10,
            ],
            [
                'name' => 'Casual Leave',
                'code' => 'casual',
                'description' => 'Used for personal work or urgent matters.',
                'default_balance' => 8,
            ],
        ];

        foreach ($leaveTypes as $typeData) {
            \App\Models\LeaveType::firstOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        // Initialize balances for all users
        foreach (User::all() as $user) {
            $user->initializeLeaveBalances();
        }
    }
}
