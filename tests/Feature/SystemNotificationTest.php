<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemNotification;
use App\Filament\Resources\SystemNotificationResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee1;
    protected User $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'employee', 'guard_name' => 'web']);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        // Create employee users
        $this->employee1 = User::create([
            'name' => 'Employee One',
            'email' => 'emp1@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->employee1->assignRole('employee');

        $this->employee2 = User::create([
            'name' => 'Employee Two',
            'email' => 'emp2@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->employee2->assignRole('employee');
    }

    /** @test */
    public function it_sends_notification_to_all_users_when_target_type_is_all()
    {
        // Clear existing notifications
        $this->assertCount(0, $this->employee1->notifications);
        $this->assertCount(0, $this->employee2->notifications);

        // Admin creates system notification for all
        SystemNotification::create([
            'title' => 'Important Announcement',
            'content' => '<p>All office locations are closed tomorrow.</p>',
            'type' => 'warning',
            'target_type' => 'all',
        ]);

        // Both employees should receive the notification in the database
        $this->employee1->refresh();
        $this->employee2->refresh();
        $this->admin->refresh();

        $this->assertCount(1, $this->employee1->notifications);
        $this->assertCount(1, $this->employee2->notifications);
        $this->assertCount(1, $this->admin->notifications); // Admin is also a user

        $this->assertEquals('Important Announcement', $this->employee1->notifications->first()->data['title']);
        $this->assertEquals('All office locations are closed tomorrow.', $this->employee1->notifications->first()->data['body']);
    }

    /** @test */
    public function it_sends_notification_to_specific_user_only()
    {
        // Admin creates notification targeting Employee One only
        SystemNotification::create([
            'title' => 'Personal Warning',
            'content' => 'Please submit your timesheets.',
            'type' => 'danger',
            'target_type' => 'specific',
            'user_id' => $this->employee1->id,
        ]);

        $this->employee1->refresh();
        $this->employee2->refresh();

        // Employee 1 should have 1 notification, Employee 2 should have 0
        $this->assertCount(1, $this->employee1->notifications);
        $this->assertCount(0, $this->employee2->notifications);

        $this->assertEquals('Personal Warning', $this->employee1->notifications->first()->data['title']);
    }

    /** @test */
    public function only_admins_can_access_system_notifications_resource()
    {
        $this->actingAs($this->admin);
        
        // Admin can list notifications
        \Livewire\Livewire::test(\App\Filament\Resources\SystemNotificationResource\Pages\ListSystemNotifications::class)
            ->assertSuccessful();

        $this->actingAs($this->employee1);

        // Employee gets forbidden status code (403)
        \Livewire\Livewire::test(\App\Filament\Resources\SystemNotificationResource\Pages\ListSystemNotifications::class)
            ->assertForbidden();
    }

    /** @test */
    public function it_polls_and_dispatches_events_for_new_unread_notifications()
    {
        $employee = User::create([
            'name' => 'Polling Test User',
            'email' => 'polling@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($employee);

        $component = \Livewire\Livewire::test(\App\Livewire\NotificationPoller::class);
        $component->call('checkNotifications')
            ->assertNotDispatched('play-notification-sound');

        // Create a new system notification targeting this employee
        $notification = SystemNotification::create([
            'title' => 'Chime Test',
            'content' => 'This is a test notification.',
            'type' => 'info',
            'target_type' => 'specific',
            'user_id' => $employee->id,
        ]);

        // Poller should now find the notification and dispatch the event
        $component->call('checkNotifications')
            ->assertDispatched('play-notification-sound');

        // Verify the notification ID is marked as processed
        $this->assertContains($employee->unreadNotifications()->first()->id, $component->get('processedIds'));
    }
}
