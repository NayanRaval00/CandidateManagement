<?php

namespace Tests\Feature;

use App\Filament\Pages\HolidayCalendar;
use App\Filament\Resources\HolidayResource\Pages\ListHolidays;
use App\Mail\HolidayNotificationMail;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HolidayTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $employee;

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

        // Create employee user
        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->employee->assignRole('employee');
    }

    /** @test */
    public function admin_can_access_holiday_resource()
    {
        $this->actingAs($this->admin);

        Livewire::test(ListHolidays::class)
            ->assertSuccessful();
    }

    /** @test */
    public function employee_cannot_access_holiday_resource()
    {
        $this->actingAs($this->employee);

        Livewire::test(ListHolidays::class)
            ->assertForbidden();
    }

    /** @test */
    public function all_users_can_access_holiday_calendar_page()
    {
        $this->actingAs($this->employee);
        Livewire::test(HolidayCalendar::class)
            ->assertSuccessful();

        $this->actingAs($this->admin);
        Livewire::test(HolidayCalendar::class)
            ->assertSuccessful();
    }

    /** @test */
    public function same_day_holiday_creation_triggers_immediate_notifications()
    {
        Mail::fake();

        // Initially no database notifications
        $this->assertCount(0, $this->employee->notifications);

        // Admin creates a holiday for today
        Holiday::create([
            'name' => 'Emergency Holiday',
            'date' => today()->toDateString(),
            'is_working_day' => false,
            'description' => 'Power failure',
        ]);

        $this->employee->refresh();
        $this->admin->refresh();

        // Both users should have 1 database notification
        $this->assertCount(1, $this->employee->notifications);
        $this->assertCount(1, $this->admin->notifications);

        $this->assertEquals('Today is a Holiday: Emergency Holiday', $this->employee->notifications->first()->data['title']);

        // Check email was sent
        Mail::assertSent(HolidayNotificationMail::class, function ($mail) {
            return $mail->holiday->name === 'Emergency Holiday' && $mail->isToday === true;
        });
    }

    /** @test */
    public function tomorrow_holiday_notifications_are_dispatched_via_console_command()
    {
        Mail::fake();

        // Create a holiday for tomorrow
        $holiday = Holiday::create([
            'name' => 'Tomorrow Holiday',
            'date' => today()->addDay()->toDateString(),
            'is_working_day' => false,
            'description' => 'Office Closed Tomorrow',
        ]);

        // It should NOT be marked as notified yet
        $this->assertFalse($holiday->fresh()->notified);
        $this->assertCount(0, $this->employee->notifications);

        // Run the console command
        $this->artisan('app:send-holiday-notifications')
            ->assertExitCode(0);

        $this->employee->refresh();
        $this->assertTrue($holiday->fresh()->notified);

        // User should have received the notification
        $this->assertCount(1, $this->employee->notifications);
        $this->assertEquals('Tomorrow is a Holiday: Tomorrow Holiday', $this->employee->notifications->first()->data['title']);

        // Check email was sent
        Mail::assertSent(HolidayNotificationMail::class, function ($mail) {
            return $mail->holiday->name === 'Tomorrow Holiday' && $mail->isToday === false;
        });
    }
}
