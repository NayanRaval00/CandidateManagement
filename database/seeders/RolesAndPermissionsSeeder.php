<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AttendanceSetting;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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
        if (! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Assign employee role to existing  user
        $testUser = User::where('email', 'nayan@yopmail.com')->first();
        if ($testUser && ! $testUser->hasRole('employee')) {
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
            LeaveType::firstOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        // Initialize balances for all users
        foreach (User::all() as $user) {
            $user->initializeLeaveBalances();
        }

        // Seed mock assets
        $assets = [
            [
                'name' => 'MacBook Pro 16"',
                'serial_number' => 'MP16-2026-X89',
                'type' => 'Hardware',
                'status' => 'Assigned',
                'description' => 'M3 Max, 64GB RAM, 1TB SSD',
            ],
            [
                'name' => 'Dell UltraSharp 27" Monitor',
                'serial_number' => 'DELL-27-U2723QE',
                'type' => 'Hardware',
                'status' => 'Assigned',
                'description' => '4K USB-C Hub Monitor',
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'serial_number' => 'LOGI-MX-3S',
                'type' => 'Hardware',
                'status' => 'Available',
                'description' => 'Wireless Mouse with silent clicks',
            ],
        ];

        $seededAssets = [];
        foreach ($assets as $assetData) {
            $seededAssets[] = Asset::firstOrCreate(
                ['serial_number' => $assetData['serial_number']],
                $assetData
            );
        }

        // Assign some assets to employee Nayan
        $employee = User::where('email', 'nayan@gmail.com')->first();
        if ($employee) {
            foreach ($seededAssets as $asset) {
                if ($asset->status === 'Assigned') {
                    $employee->assets()->syncWithoutDetaching([
                        $asset->id => [
                            'assigned_at' => now(),
                            'notes' => 'Assigned during onboarding.',
                        ],
                    ]);
                }
            }
        }

        // Seed default attendance setting singleton
        AttendanceSetting::getSingleton();
    }
}
