<?php

namespace Tests\Feature;

use App\Filament\Resources\InnoventixBotResource\Pages\ListInnoventixBots;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InnoventixBotResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        // Create employee user
        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->employee->assignRole('employee');
    }

    /** @test */
    public function only_admins_can_access_innoventix_bot_resource()
    {
        $this->actingAs($this->admin);

        // Admin can list logs
        Livewire::test(ListInnoventixBots::class)
            ->assertSuccessful();

        $this->actingAs($this->employee);

        // Employee gets forbidden status code (403)
        Livewire::test(ListInnoventixBots::class)
            ->assertForbidden();
    }
}
