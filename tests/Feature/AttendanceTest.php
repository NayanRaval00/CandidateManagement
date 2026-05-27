<?php

namespace Tests\Feature;

use App\Filament\Pages\MyAttendance;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
