<?php

namespace App\Services\Timesheet;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DefaultTimesheetCalculator implements TimesheetCalculatorInterface
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
    ): array {
        $dailyBreakdown = [];
        $daysWorked = 0;
        $leavesCount = 0;
        $holidaysCount = 0;
        $expectedWorkingDays = 0;
        $totalMinutes = 0;
        $lateCount = 0;
        $lateLogs = [];

        $gracePeriodTime = config('timesheets.grace_period_time', '09:45:00');
        $workingDays = config('timesheets.default_working_days', [1, 2, 3, 4, 5]);

        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $isWeekend = ! in_array($date->dayOfWeekIso, $workingDays);
            $isHoliday = in_array($dateStr, $holidayDates);

            // Is expected working day? Working day according to config AND not a holiday
            if (! $isWeekend && ! $isHoliday) {
                $expectedWorkingDays++;
            }

            // Check attendance
            $att = $attendances->first(fn ($a) => ($a->date?->format('Y-m-d') === $dateStr) || ($a->punch_in?->format('Y-m-d') === $dateStr));

            if ($att && $att->punch_in) {
                $dailyBreakdown[$dateStr] = 'Present';
                $daysWorked++;
                $totalMinutes += $att->getMinutesWorkedOnDay();

                // Check late arrival
                $punchTime = $att->punch_in->format('H:i:s');
                if ($punchTime > $gracePeriodTime) {
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

        return [
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
        ];
    }
}
