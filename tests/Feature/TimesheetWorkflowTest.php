<?php

namespace Tests\Feature;

use App\Filament\Pages\TimesheetWorkflow;
use App\Jobs\SendApprovedTimesheetJob;
use App\Mail\TimesheetNotificationMail;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\TimesheetBatch;
use App\Models\TimesheetRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TimesheetWorkflowTest extends TestCase
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

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->employee->assignRole('employee');
    }

    /** @test */
    public function admin_can_generate_draft_timesheet_batch(): void
    {
        $this->actingAs($this->admin);

        // Setup some attendances for employee
        // June 1st (Monday) and June 2nd (Tuesday) 2026
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => Carbon::parse('2026-06-01'),
            'punch_in' => Carbon::parse('2026-06-01 09:00:00'),
            'punch_out' => Carbon::parse('2026-06-01 17:00:00'),
            'status' => 'Present',
        ]);

        // June 2nd, late arrival (10:00:00 > 09:45:00)
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => Carbon::parse('2026-06-02'),
            'punch_in' => Carbon::parse('2026-06-02 10:00:00'),
            'punch_out' => Carbon::parse('2026-06-02 18:00:00'),
            'status' => 'Present',
        ]);

        // June 3rd (Wednesday) is a Holiday
        Holiday::create([
            'name' => 'Test Holiday',
            'date' => Carbon::parse('2026-06-03'),
            'is_working_day' => false,
        ]);

        // Run timesheet generation from June 1st to June 5th (5 days: Mon to Fri)
        Livewire::test(TimesheetWorkflow::class)
            ->set('startDate', '2026-06-01')
            ->set('endDate', '2026-06-05')
            ->call('generateBatch');

        $this->assertDatabaseHas('timesheet_batches', [
            'status' => 'draft',
            'generated_by' => $this->admin->id,
        ]);

        $batch = TimesheetBatch::first();

        // Assert record is generated for the employee
        $this->assertDatabaseHas('timesheet_records', [
            'batch_id' => $batch->id,
            'user_id' => $this->employee->id,
            'total_calendar_days' => 5,
            'expected_working_days' => 4, // 5 days - 1 holiday = 4 days (no weekends in Mon-Fri 2026-06-01 to 2026-06-05)
            'days_worked' => 2, // Jun 1 & Jun 2
            'holidays_count' => 1, // Jun 3 holiday
            'late_count' => 1, // Late on June 2
        ]);

        $record = TimesheetRecord::where('user_id', $this->employee->id)->first();
        $this->assertEquals('16h 0m', $record->formatted_hours); // 8 hours + 8 hours = 16 hours
        $this->assertEquals('Present', $record->daily_breakdown_json['2026-06-01']);
        $this->assertEquals('Present', $record->daily_breakdown_json['2026-06-02']);
        $this->assertEquals('Holiday', $record->daily_breakdown_json['2026-06-03']);
        $this->assertEquals('Absent', $record->daily_breakdown_json['2026-06-04']);
    }

    /** @test */
    public function manual_leave_addition_recalculates_draft_batch(): void
    {
        $this->actingAs($this->admin);

        // Run timesheet generation from June 1st to June 5th
        Livewire::test(TimesheetWorkflow::class)
            ->set('startDate', '2026-06-01')
            ->set('endDate', '2026-06-05')
            ->call('generateBatch');

        $batch = TimesheetBatch::first();
        $record = TimesheetRecord::where('user_id', $this->employee->id)->first();

        $this->assertEquals(0, $record->leaves_count);
        $this->assertEquals('Absent', $record->daily_breakdown_json['2026-06-04']);

        // Now inject a manual leave for June 4th
        Livewire::test(TimesheetWorkflow::class)
            ->set('activeBatchId', $batch->id)
            ->set('leaveUserId', $this->employee->id)
            ->set('leaveType', 'Sick')
            ->set('leaveStartDate', '2026-06-04')
            ->set('leaveEndDate', '2026-06-04')
            ->set('leaveReason', 'Fever')
            ->call('addManualLeave');

        // Verify the leave is saved
        $this->assertDatabaseHas('leaves', [
            'user_id' => $this->employee->id,
            'leave_type' => 'Sick',
            'start_date' => '2026-06-04 00:00:00',
            'status' => 'approved',
        ]);

        // Verify the draft timesheet record snapshot is automatically updated
        $freshRecord = $record->fresh();
        $this->assertEquals(1, $freshRecord->leaves_count);
        $this->assertEquals('Leave', $freshRecord->daily_breakdown_json['2026-06-04']);
    }

    /** @test */
    public function admin_can_approve_and_lock_batch(): void
    {
        $this->actingAs($this->admin);

        $batch = TimesheetBatch::create([
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-05'),
            'status' => 'draft',
            'generated_by' => $this->admin->id,
        ]);

        Livewire::test(TimesheetWorkflow::class)
            ->call('approveBatch', $batch->id);

        $this->assertEquals('approved', $batch->fresh()->status);
    }

    /** @test */
    public function timesheet_sending_disabled_in_draft(): void
    {
        $this->actingAs($this->admin);
        Queue::fake();

        $batch = TimesheetBatch::create([
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-05'),
            'status' => 'draft',
            'generated_by' => $this->admin->id,
        ]);

        Livewire::test(TimesheetWorkflow::class)
            ->call('sendBatch', $batch->id);

        Queue::assertNotPushed(SendApprovedTimesheetJob::class);
    }

    /** @test */
    public function timesheet_sending_dispatches_queue_jobs(): void
    {
        $this->actingAs($this->admin);
        Queue::fake();

        $batch = TimesheetBatch::create([
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-05'),
            'status' => 'approved',
            'generated_by' => $this->admin->id,
        ]);

        Livewire::test(TimesheetWorkflow::class)
            ->call('sendBatch', $batch->id);

        Queue::assertPushed(SendApprovedTimesheetJob::class, function ($job) use ($batch) {
            return $job->batchId === $batch->id;
        });
    }

    /** @test */
    public function send_approved_timesheet_job_renders_pdf_and_sends_email(): void
    {
        Mail::fake();

        $batch = TimesheetBatch::create([
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-05'),
            'status' => 'approved',
            'generated_by' => $this->admin->id,
        ]);

        $record = TimesheetRecord::create([
            'batch_id' => $batch->id,
            'user_id' => $this->employee->id,
            'total_calendar_days' => 5,
            'expected_working_days' => 5,
            'days_worked' => 5,
            'leaves_count' => 0,
            'holidays_count' => 0,
            'late_count' => 0,
            'total_hours' => 40.0,
            'formatted_hours' => '40h 0m',
            'daily_breakdown_json' => ['2026-06-01' => 'Present'],
            'late_logs_json' => [],
        ]);

        // Run the job synchronously
        $job = new SendApprovedTimesheetJob($batch->id);
        $job->handle();

        // Assert mailable was sent
        Mail::assertSent(TimesheetNotificationMail::class, function ($mail) {
            return $mail->hasTo($this->employee->email);
        });

        // Assert batch status changed to dispatched
        $this->assertEquals('dispatched', $batch->fresh()->status);
    }

    /** @test */
    public function timesheet_sending_immediately_processes_sync(): void
    {
        $this->actingAs($this->admin);
        Mail::fake();

        $batch = TimesheetBatch::create([
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-05'),
            'status' => 'approved',
            'generated_by' => $this->admin->id,
        ]);

        $record = TimesheetRecord::create([
            'batch_id' => $batch->id,
            'user_id' => $this->employee->id,
            'total_calendar_days' => 5,
            'expected_working_days' => 5,
            'days_worked' => 5,
            'leaves_count' => 0,
            'holidays_count' => 0,
            'late_count' => 0,
            'total_hours' => 40.0,
            'formatted_hours' => '40h 0m',
            'daily_breakdown_json' => ['2026-06-01' => 'Present'],
            'late_logs_json' => [],
        ]);

        Livewire::test(TimesheetWorkflow::class)
            ->call('sendBatchImmediately', $batch->id);

        // Assert mail was sent immediately/synchronously during the Livewire action
        Mail::assertSent(TimesheetNotificationMail::class, function ($mail) {
            return $mail->hasTo($this->employee->email);
        });

        // Assert batch status changed to dispatched
        $this->assertEquals('dispatched', $batch->fresh()->status);
    }
}
