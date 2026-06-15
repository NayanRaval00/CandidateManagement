<?php

namespace App\Services\Timesheet;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface TimesheetCalculatorInterface
{
    /**
     * Calculate timesheet metrics for a given user in a date range.
     */
    public function calculate(
        User $user,
        Carbon $start,
        Carbon $end,
        Collection $attendances,
        Collection $leaveRequests,
        Collection $manualLeaves,
        array $holidayDates
    ): array;
}
