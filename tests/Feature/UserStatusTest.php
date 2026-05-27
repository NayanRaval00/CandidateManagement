<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserStatusTest extends TestCase
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
    public function active_user_can_access_filament_panel(): void
    {
        $user = User::create([
            'name' => 'Active Admin',
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'status' => 'Active',
        ]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function inactive_user_cannot_access_filament_panel(): void
    {
        $user = User::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'status' => 'Inactive',
        ]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        // Access to filament panels redirects/blocks unauthorized access with a 403
        $response->assertStatus(403);
    }
}
