<?php

namespace Tests\Feature;

use App\Filament\Resources\LeaveRequestResource;
use App\Filament\Resources\LeaveRequestResource\Pages\CreateLeaveRequest;
use App\Mail\LeaveStatusUpdatedMail;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'employee', 'guard_name' => 'web']);

        // Create leave types
        LeaveType::create(['name' => 'Annual Leave', 'code' => 'annual', 'default_balance' => 15]);
        LeaveType::create(['name' => 'Sick Leave', 'code' => 'sick', 'default_balance' => 10]);
    }

    /** @test */
    public function it_calculates_weekdays_correctly_excluding_weekends()
    {
        $setVal = null;
        $setter = function ($key, $value) use (&$setVal) {
            $setVal = $value;
        };

        // Monday 2026-05-25 to Friday 2026-05-29 (5 days)
        LeaveRequestResource::calculateDays('2026-05-25', '2026-05-29', $setter);
        $this->assertEquals(5, $setVal);

        // Friday 2026-05-29 to Monday 2026-06-01 (4 days: Fri, Mon, exclude Sat, Sun)
        LeaveRequestResource::calculateDays('2026-05-29', '2026-06-01', $setter);
        $this->assertEquals(2, $setVal); // Friday and Monday are weekdays = 2 days

        // Saturday 2026-05-30 to Sunday 2026-05-31 (0 days: weekends only)
        LeaveRequestResource::calculateDays('2026-05-30', '2026-05-31', $setter);
        $this->assertEquals(0, $setVal);
    }

    /** @test */
    public function it_lazy_initializes_leave_balances()
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertCount(0, $employee->leaveBalances);

        $employee->initializeLeaveBalances();

        $this->assertCount(2, $employee->fresh()->leaveBalances);
        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $employee->id,
            'balance' => 15,
            'used' => 0,
        ]);
    }

    /** @test */
    public function it_adjusts_balances_when_approved_or_rejected()
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $employee->initializeLeaveBalances();

        $annualType = LeaveType::where('code', 'annual')->first();

        // Create a pending leave request
        $request = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $annualType->id,
            'start_date' => '2026-05-25',
            'end_date' => '2026-05-29',
            'days' => 5,
            'reason' => 'Annual vacation',
            'status' => 'pending',
        ]);

        // Balance should not be changed yet
        $balance = LeaveBalance::where('user_id', $employee->id)->where('leave_type_id', $annualType->id)->first();
        $this->assertEquals(0, $balance->used);

        // Approve the request
        $request->update(['status' => 'approved']);

        $balance = $balance->fresh();
        $this->assertEquals(5, $balance->used);
        $this->assertEquals(10, $balance->remaining);

        // Reject it (status changed from approved to rejected)
        $request->update(['status' => 'rejected']);

        $balance = $balance->fresh();
        $this->assertEquals(0, $balance->used);
        $this->assertEquals(15, $balance->remaining);
    }

    /** @test */
    public function it_sets_user_id_automatically_when_non_admin_creates_leave_request()
    {
        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');
        $employee->initializeLeaveBalances();

        $annualType = LeaveType::where('code', 'annual')->first();

        $this->actingAs($employee);

        Livewire::test(CreateLeaveRequest::class)
            ->fillForm([
                'leave_type_id' => $annualType->id,
                'start_date' => '2026-05-25',
                'end_date' => '2026-05-29',
                'days' => 5,
                'reason' => 'Annual vacation',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $employee->id,
            'leave_type_id' => $annualType->id,
            'days' => 5,
            'reason' => 'Annual vacation',
        ]);
    }

    /** @test */
    public function it_sends_notification_and_email_on_approval()
    {
        Mail::fake();

        $employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $employee->initializeLeaveBalances();

        $annualType = LeaveType::where('code', 'annual')->first();

        $request = LeaveRequest::create([
            'user_id' => $employee->id,
            'leave_type_id' => $annualType->id,
            'start_date' => '2026-05-25',
            'end_date' => '2026-05-29',
            'days' => 5,
            'reason' => 'Annual vacation',
            'status' => 'pending',
        ]);

        $request->update(['status' => 'approved']);

        Mail::assertSent(LeaveStatusUpdatedMail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->email);
        });

        $this->assertCount(1, $employee->unreadNotifications);
        $this->assertStringContainsString('Approved', $employee->unreadNotifications->first()->data['title']);
    }
}
