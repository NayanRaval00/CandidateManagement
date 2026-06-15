<?php

use App\Services\Timesheet\DefaultTimesheetCalculator;

return [
    /*
    |--------------------------------------------------------------------------
    | Timesheet Calculator Class
    |--------------------------------------------------------------------------
    |
    | The class name of the timesheet calculator service used to calculate
    | attendance, leaves, late counts, and formatted working hours.
    |
    */
    'calculator' => DefaultTimesheetCalculator::class,

    /*
    |--------------------------------------------------------------------------
    | Grace Period Time
    |--------------------------------------------------------------------------
    |
    | The daily time limit (H:i:s format) after which an employee's punch-in
    | is flagged as a late arrival.
    |
    */
    'grace_period_time' => '09:45:00',

    /*
    |--------------------------------------------------------------------------
    | Default Working Days
    |--------------------------------------------------------------------------
    |
    | Days of the week (1 = Monday, 7 = Sunday) that are expected working days
    | by default, unless designated as a holiday.
    |
    */
    'default_working_days' => [1, 2, 3, 4, 5],
];
