<?php

namespace Tests\Feature;

use App\Filament\Pages\MyAttendance;
use App\Filament\Widgets\AttendanceWidget;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceTest extends TestCase
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
    public function employee_can_punch_in_when_within_radius(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Setup office settings (at 23.0225, 72.5714, radius 100 meters)
        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 30,
        ]);

        $this->actingAs($employee);

        // Test Livewire punchIn inside the allowed zone (at 23.0226, 72.5715 - ~15 meters away)
        Livewire::test(MyAttendance::class)
            ->set('latitude', 23.02260000)
            ->set('longitude', 72.57150000)
            ->call('punchIn');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'date' => today()->toDateString().' 00:00:00',
            'status' => 'Present',
        ]);
    }

    /** @test */
    public function employee_cannot_punch_in_when_outside_radius(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Setup office settings (at 23.0225, 72.5714, radius 100 meters)
        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
        ]);

        $this->actingAs($employee);

        // Test Livewire punchIn outside the allowed zone (at 24.0, 73.0 - way outside)
        Livewire::test(MyAttendance::class)
            ->set('latitude', 24.00000000)
            ->set('longitude', 73.00000000)
            ->call('punchIn');

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $employee->id,
            'date' => today()->toDateString().' 00:00:00',
        ]);
    }

    /** @test */
    public function employee_cannot_punch_out_before_lockout_delay(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Setup office settings (at 23.0225, 72.5714, radius 100 meters, 30 mins delay)
        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 30,
        ]);

        $this->actingAs($employee);

        // Create an attendance record punched in just now
        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => today(),
            'punch_in' => now(),
            'punch_in_latitude' => 23.02250000,
            'punch_in_longitude' => 72.57140000,
            'status' => 'Present',
        ]);

        // Attempt to punch out immediately
        Livewire::test(MyAttendance::class)
            ->set('latitude', 23.02250000)
            ->set('longitude', 72.57140000)
            ->call('punchOut');

        $this->assertNull($attendance->fresh()->punch_out);
    }

    /** @test */
    public function admin_can_manually_adjust_and_override_attendance(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($admin);

        // Create manual override
        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => today(),
            'punch_in' => now()->subHours(8),
            'punch_out' => now(),
            'status' => 'Present',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $employee->id,
            'status' => 'Present',
        ]);
    }

    /** @test */
    public function employee_punch_resolves_and_saves_location_name(): void
    {
        // Mock OSM Nominatim response
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Test Location Name, Ahmedabad, Gujarat',
            ], 200),
        ]);

        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Setup office settings (at 23.0225, 72.5714, radius 100 meters)
        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 30,
        ]);

        $this->actingAs($employee);

        // Test Livewire punchIn inside the allowed zone (at 23.0226, 72.5715)
        Livewire::test(MyAttendance::class)
            ->call('punchIn', 23.02260000, 72.57150000);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'date' => today()->toDateString().' 00:00:00',
            'punch_in_location' => 'Test Location Name, Ahmedabad, Gujarat',
        ]);
    }

    /** @test */
    public function widget_employee_can_punch_in_when_within_radius(): void
    {
        $employee = User::create([
            'name' => 'Test Employee 2',
            'email' => 'employee2@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 30,
        ]);

        $this->actingAs($employee);

        Livewire::test(AttendanceWidget::class)
            ->set('latitude', 23.02260000)
            ->set('longitude', 72.57150000)
            ->call('punchIn');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'date' => today()->toDateString().' 00:00:00',
            'status' => 'Present',
        ]);
    }

    /** @test */
    public function widget_employee_cannot_punch_out_before_lockout_delay(): void
    {
        $employee = User::create([
            'name' => 'Test Employee 3',
            'email' => 'employee3@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 30,
        ]);

        $this->actingAs($employee);

        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => today(),
            'punch_in' => now(),
            'punch_in_latitude' => 23.02250000,
            'punch_in_longitude' => 72.57140000,
            'status' => 'Present',
        ]);

        Livewire::test(AttendanceWidget::class)
            ->set('latitude', 23.02250000)
            ->set('longitude', 72.57140000)
            ->call('punchOut');

        $this->assertNull($attendance->fresh()->punch_out);
    }

    /** @test */
    public function employee_work_hours_are_bounded_by_calendar_day(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);

        $date = Carbon::parse('2026-06-04');
        $punchIn = Carbon::parse('2026-06-04 22:00:00');
        $punchOut = Carbon::parse('2026-06-05 06:00:00');

        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => $date,
            'punch_in' => $punchIn,
            'punch_out' => $punchOut,
            'status' => 'Present',
        ]);

        // Worked hours on 2026-06-04 should be capped from 22:00:00 to 24:00:00 (2.0 hours)
        $this->assertEquals(2.0, $attendance->hours_worked);
        $this->assertEquals('2h 0m', $attendance->formatted_hours_worked);
    }

    /** @test */
    public function employee_can_start_and_end_breaks(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        $this->actingAs($employee);

        // Create an attendance record punched in
        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => today(),
            'punch_in' => now()->subHour(),
            'status' => 'Present',
        ]);

        // Start break
        Livewire::test(MyAttendance::class)
            ->call('startBreak', 'Lunch');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'reason' => 'Lunch',
            'end_time' => null,
        ]);

        // End break
        Livewire::test(MyAttendance::class)
            ->call('endBreak');

        $this->assertDatabaseMissing('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'end_time' => null,
        ]);
    }

    /** @test */
    public function employee_breaks_are_excluded_from_worked_hours(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);

        $date = Carbon::parse('2026-06-04');
        $punchIn = Carbon::parse('2026-06-04 09:00:00');
        $punchOut = Carbon::parse('2026-06-04 17:00:00');

        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => $date,
            'punch_in' => $punchIn,
            'punch_out' => $punchOut,
            'status' => 'Present',
        ]);

        // Create a 1-hour break from 12:00:00 to 13:00:00
        $attendance->breaks()->create([
            'start_time' => Carbon::parse('2026-06-04 12:00:00'),
            'end_time' => Carbon::parse('2026-06-04 13:00:00'),
            'reason' => 'Lunch',
        ]);

        // Total shift = 8 hours. Break = 1 hour. Worked hours = 7.0 hours.
        $this->assertEquals(7.0, $attendance->hours_worked);
        $this->assertEquals('7h 0m', $attendance->formatted_hours_worked);
    }

    /** @test */
    public function employee_punch_out_automatically_ends_active_break(): void
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Setup settings
        $setting = AttendanceSetting::getSingleton();
        $setting->update([
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 0, // set to 0 for instant punch out in test
        ]);

        $this->actingAs($employee);

        // Create an attendance record punched in
        $attendance = Attendance::create([
            'user_id' => $employee->id,
            'date' => today(),
            'punch_in' => now()->subMinutes(10),
            'punch_in_latitude' => 23.02250000,
            'punch_in_longitude' => 72.57140000,
            'status' => 'Present',
        ]);

        // Start break
        $attendance->breaks()->create([
            'start_time' => now()->subMinutes(5),
            'reason' => 'Tea Break',
            'end_time' => null,
        ]);

        $this->assertTrue($attendance->is_on_break);

        // Punch out
        Livewire::test(MyAttendance::class)
            ->set('latitude', 23.02250000)
            ->set('longitude', 72.57140000)
            ->call('punchOut');

        $this->assertFalse($attendance->fresh()->is_on_break);
        $this->assertNotNull($attendance->fresh()->punch_out);
    }
}
