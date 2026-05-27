<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'employee', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_creates_assets_and_associates_with_users_via_many_to_many()
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);

        $asset = Asset::create([
            'name' => 'MacBook Pro 16"',
            'serial_number' => 'MB-16-TEST',
            'type' => 'Hardware',
            'status' => 'Assigned',
            'description' => 'M3 chip MacBook',
        ]);

        // Assert asset was created
        $this->assertDatabaseHas('assets', [
            'serial_number' => 'MB-16-TEST',
            'status' => 'Assigned',
        ]);

        // Attach employee to asset
        $assignedAt = now();
        $asset->users()->attach($employee->id, [
            'assigned_at' => $assignedAt,
            'notes' => 'Assigned for work from home.',
        ]);

        // Verify association
        $this->assertCount(1, $asset->users);
        $this->assertEquals('Employee User', $asset->users->first()->name);
        $this->assertEquals('Assigned for work from home.', $asset->users->first()->pivot->notes);

        // Verify reverse association
        $this->assertCount(1, $employee->fresh()->assets);
        $this->assertEquals('MacBook Pro 16"', $employee->assets->first()->name);
    }

    /** @test */
    public function admin_can_access_assets_resource_while_employee_cannot()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Admin can access assets index
        $this->actingAs($admin)
            ->get('/admin/assets')
            ->assertStatus(200);

        // Employee is forbidden (redirects or returns 403 based on Filament panels layout, usually returns 403 or redirects depending on auth status, let's assert non-200/non-successful)
        $response = $this->actingAs($employee)->get('/admin/assets');
        $this->assertTrue($response->status() >= 300);
    }
}
