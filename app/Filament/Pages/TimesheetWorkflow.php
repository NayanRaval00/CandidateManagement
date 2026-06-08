<?php

namespace App\Filament\Pages;

use App\Jobs\SendApprovedTimesheetJob;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveRequest;
use App\Models\TimesheetBatch;
use App\Models\TimesheetRecord;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class TimesheetWorkflow extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Timesheet Distribution';

    protected static ?string $title = 'Ad-Hoc Timesheet Distribution';

    protected static ?string $navigationGroup = 'Leave & Attendance';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.timesheet-workflow';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public string $startDate = '';

    public string $endDate = '';

    public ?int $activeBatchId = null;

    public bool $showBreakdownModal = false;

    public ?int $breakdownRecordId = null;

    public bool $showLeaveModal = false;

    public ?int $leaveUserId = null;

    public string $leaveType = 'Sick';

    public string $leaveStartDate = '';

    public string $leaveEndDate = '';

    public string $leaveReason = '';

    public string $search = '';

    /**
     * Mount the page.
     */
    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        // Automatically set the active batch to the most recent one if available
        $latestBatch = TimesheetBatch::latest()->first();
        if ($latestBatch) {
            $this->activeBatchId = $latestBatch->id;
        }
    }

    /**
     * Determine if navigation should be registered.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    /**
     * Get the active batch object.
     */
    public function getActiveBatchProperty(): ?TimesheetBatch
    {
        if (! $this->activeBatchId) {
            return null;
        }

        return TimesheetBatch::with('records.user')->find($this->activeBatchId);
    }

    /**
     * Get the list of all timesheet batches.
     */
    public function getBatchesProperty()
    {
        return TimesheetBatch::with('generatedBy')->latest()->get();
    }

    /**
     * Select a specific batch to view.
     */
    public function selectBatch(int $batchId): void
    {
        $this->activeBatchId = $batchId;
    }

    /**
     * Generate a new timesheet batch.
     */
    public function generateBatch(): void
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        // Fetch all employees (users with role 'employee' or all users except admin)
        $users = User::role('employee')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        if ($users->isEmpty()) {
            Notification::make()
                ->title('No Employees Found')
                ->body('There are no employees in the database to generate timesheets for.')
                ->danger()
                ->send();

            return;
        }

        // Create the Batch
        $batch = TimesheetBatch::create([
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'draft',
            'generated_by' => auth()->id(),
        ]);

        // Fetch holidays in the range
        $holidays = Holiday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();
        $holidayDates = $holidays->where('is_working_day', false)->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->toArray();

        foreach ($users as $user) {
            // Fetch leaves and manual leaves
            $leaveRequests = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($sub) use ($start, $end) {
                            $sub->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                        });
                })->get();

            $manualLeaves = Leave::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($sub) use ($start, $end) {
                            $sub->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                        });
                })->get();

            // Fetch attendances in range
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->get();

            // Daily breakdown & counters
            $dailyBreakdown = [];
            $daysWorked = 0;
            $leavesCount = 0;
            $holidaysCount = 0;
            $expectedWorkingDays = 0;
            $totalMinutes = 0;
            $lateCount = 0;
            $lateLogs = [];

            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $isWeekend = $date->isWeekend();
                $isHoliday = in_array($dateStr, $holidayDates);

                // Is expected working day? Weekday AND not a holiday
                if (! $isWeekend && ! $isHoliday) {
                    $expectedWorkingDays++;
                }

                // Check attendance
                $att = $attendances->first(fn ($a) => ($a->date?->format('Y-m-d') === $dateStr) || ($a->punch_in?->format('Y-m-d') === $dateStr));

                if ($att && $att->punch_in) {
                    $dailyBreakdown[$dateStr] = 'Present';
                    $daysWorked++;
                    $totalMinutes += $att->getMinutesWorkedOnDay();

                    // Check late arrival (grace period past 09:45:00)
                    $punchTime = $att->punch_in->format('H:i:s');
                    if ($punchTime > '09:45:00') {
                        $lateCount++;
                        $lateLogs[] = $att->punch_in->toDateTimeString();
                    }
                } else {
                    // Check leaves
                    $hasLeave = false;
                    foreach ($leaveRequests as $lr) {
                        if ($date->between($lr->start_date, $lr->end_date)) {
                            $hasLeave = true;
                            break;
                        }
                    }
                    if (! $hasLeave) {
                        foreach ($manualLeaves as $ml) {
                            if ($date->between($ml->start_date, $ml->end_date)) {
                                $hasLeave = true;
                                break;
                            }
                        }
                    }

                    if ($hasLeave) {
                        $dailyBreakdown[$dateStr] = 'Leave';
                        $leavesCount++;
                    } elseif ($isHoliday || $isWeekend) {
                        $dailyBreakdown[$dateStr] = 'Holiday';
                        $holidaysCount++;
                    } else {
                        $dailyBreakdown[$dateStr] = 'Absent';
                    }
                }
            }

            $hours = floor($totalMinutes / 60);
            $mins = $totalMinutes % 60;
            $formattedHours = "{$hours}h {$mins}m";
            $totalHoursDecimal = round($totalMinutes / 60, 2);

            TimesheetRecord::create([
                'batch_id' => $batch->id,
                'user_id' => $user->id,
                'total_calendar_days' => $period->count(),
                'expected_working_days' => $expectedWorkingDays,
                'days_worked' => $daysWorked,
                'leaves_count' => $leavesCount,
                'holidays_count' => $holidaysCount,
                'late_count' => $lateCount,
                'total_hours' => $totalHoursDecimal,
                'formatted_hours' => $formattedHours,
                'daily_breakdown_json' => $dailyBreakdown,
                'late_logs_json' => $lateLogs,
            ]);
        }

        Notification::make()
            ->title('Timesheet Batch Generated')
            ->body('A new draft timesheet batch has been created successfully.')
            ->success()
            ->send();

        $this->activeBatchId = $batch->id;
    }

    /**
     * Recalculate record metrics for a specific user.
     */
    public function recalculateRecord(TimesheetRecord $record): void
    {
        $batch = $record->batch;
        $start = $batch->start_date;
        $end = $batch->end_date;
        $user = $record->user;

        $holidays = Holiday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();
        $holidayDates = $holidays->where('is_working_day', false)->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->toArray();

        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end) {
                        $sub->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })->get();

        $manualLeaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end) {
                        $sub->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })->get();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        $dailyBreakdown = [];
        $daysWorked = 0;
        $leavesCount = 0;
        $holidaysCount = 0;
        $expectedWorkingDays = 0;
        $totalMinutes = 0;
        $lateCount = 0;
        $lateLogs = [];

        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $isWeekend = $date->isWeekend();
            $isHoliday = in_array($dateStr, $holidayDates);

            if (! $isWeekend && ! $isHoliday) {
                $expectedWorkingDays++;
            }

            $att = $attendances->first(fn ($a) => ($a->date?->format('Y-m-d') === $dateStr) || ($a->punch_in?->format('Y-m-d') === $dateStr));

            if ($att && $att->punch_in) {
                $dailyBreakdown[$dateStr] = 'Present';
                $daysWorked++;
                $totalMinutes += $att->getMinutesWorkedOnDay();

                $punchTime = $att->punch_in->format('H:i:s');
                if ($punchTime > '09:45:00') {
                    $lateCount++;
                    $lateLogs[] = $att->punch_in->toDateTimeString();
                }
            } else {
                $hasLeave = false;
                foreach ($leaveRequests as $lr) {
                    if ($date->between($lr->start_date, $lr->end_date)) {
                        $hasLeave = true;
                        break;
                    }
                }
                if (! $hasLeave) {
                    foreach ($manualLeaves as $ml) {
                        if ($date->between($ml->start_date, $ml->end_date)) {
                            $hasLeave = true;
                            break;
                        }
                    }
                }

                if ($hasLeave) {
                    $dailyBreakdown[$dateStr] = 'Leave';
                    $leavesCount++;
                } elseif ($isHoliday || $isWeekend) {
                    $dailyBreakdown[$dateStr] = 'Holiday';
                    $holidaysCount++;
                } else {
                    $dailyBreakdown[$dateStr] = 'Absent';
                }
            }
        }

        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;
        $formattedHours = "{$hours}h {$mins}m";
        $totalHoursDecimal = round($totalMinutes / 60, 2);

        $record->update([
            'total_calendar_days' => $period->count(),
            'expected_working_days' => $expectedWorkingDays,
            'days_worked' => $daysWorked,
            'leaves_count' => $leavesCount,
            'holidays_count' => $holidaysCount,
            'late_count' => $lateCount,
            'total_hours' => $totalHoursDecimal,
            'formatted_hours' => $formattedHours,
            'daily_breakdown_json' => $dailyBreakdown,
            'late_logs_json' => $lateLogs,
        ]);
    }

    /**
     * Approve and lock the selected batch.
     */
    public function approveBatch(int $batchId): void
    {
        $batch = TimesheetBatch::findOrFail($batchId);

        if ($batch->status !== 'draft') {
            return;
        }

        $batch->update(['status' => 'approved']);

        Notification::make()
            ->title('Batch Approved & Locked')
            ->body('The timesheet batch calculations are locked. You can now send them to employees.')
            ->success()
            ->send();
    }

    /**
     * Dispatch sending job to the queue.
     */
    public function sendBatch(int $batchId): void
    {
        $batch = TimesheetBatch::findOrFail($batchId);

        if ($batch->status !== 'approved') {
            return;
        }

        // Dispatch background job
        SendApprovedTimesheetJob::dispatch($batchId);

        Notification::make()
            ->title('Distribution Dispatched')
            ->body('PDF rendering and email delivery jobs have been added to the queue.')
            ->info()
            ->send();
    }

    /**
     * Run the timesheet generation and send emails immediately (sync).
     */
    public function sendBatchImmediately(int $batchId): void
    {
        $batch = TimesheetBatch::findOrFail($batchId);

        if ($batch->status !== 'approved') {
            return;
        }

        // Execute background job synchronously on the spot
        SendApprovedTimesheetJob::dispatchSync($batchId);

        Notification::make()
            ->title('Distribution Complete')
            ->body('Timesheets have been generated and sent to all employees successfully.')
            ->success()
            ->send();
    }

    /**
     * Add manual leave for an employee and trigger recalculation.
     */
    public function addManualLeave(): void
    {
        $this->validate([
            'leaveUserId' => 'required|exists:users,id',
            'leaveType' => 'required|string',
            'leaveStartDate' => 'required|date',
            'leaveEndDate' => 'required|date|after_or_equal:leaveStartDate',
            'leaveReason' => 'nullable|string',
        ]);

        $leave = Leave::create([
            'user_id' => $this->leaveUserId,
            'leave_type' => $this->leaveType,
            'start_date' => Carbon::parse($this->leaveStartDate),
            'end_date' => Carbon::parse($this->leaveEndDate),
            'status' => 'approved',
            'reason' => $this->leaveReason,
        ]);

        // Find active draft batches overlapping with this leave duration
        $overlappingBatches = TimesheetBatch::where('status', 'draft')
            ->where(function ($q) use ($leave) {
                $q->whereBetween('start_date', [$leave->start_date, $leave->end_date])
                    ->orWhereBetween('end_date', [$leave->start_date, $leave->end_date])
                    ->orWhere(function ($sub) use ($leave) {
                        $sub->where('start_date', '<=', $leave->start_date)
                            ->where('end_date', '>=', $leave->end_date);
                    });
            })->get();

        foreach ($overlappingBatches as $batch) {
            $record = TimesheetRecord::where('batch_id', $batch->id)
                ->where('user_id', $this->leaveUserId)
                ->first();

            if ($record) {
                $this->recalculateRecord($record);
            }
        }

        Notification::make()
            ->title('Manual Leave Added')
            ->body('Leave registered and overlapping draft timesheet snapshots recalculated.')
            ->success()
            ->send();

        $this->resetLeaveForm();
    }

    /**
     * Open leave modal for a specific user.
     */
    public function openLeaveModal(int $userId): void
    {
        $this->leaveUserId = $userId;
        $this->leaveStartDate = now()->format('Y-m-d');
        $this->leaveEndDate = now()->format('Y-m-d');
        $this->leaveReason = '';
        $this->showLeaveModal = true;
    }

    /**
     * Reset leave form and close modal.
     */
    public function resetLeaveForm(): void
    {
        $this->showLeaveModal = false;
        $this->leaveUserId = null;
        $this->leaveStartDate = '';
        $this->leaveEndDate = '';
        $this->leaveReason = '';
    }

    /**
     * View daily breakdown for a record.
     */
    public function openBreakdown(int $recordId): void
    {
        $this->breakdownRecordId = $recordId;
        $this->showBreakdownModal = true;
    }

    /**
     * Get breakdown record property.
     */
    public function getBreakdownRecordProperty(): ?TimesheetRecord
    {
        if (! $this->breakdownRecordId) {
            return null;
        }

        return TimesheetRecord::with('user')->find($this->breakdownRecordId);
    }
}
