<?php

return [

    'provider' => env('AGENTIS_PROVIDER', 'gemini'),
    'max_rows' => 100,
    'cache_ttl' => 60,

    'tables' => [
        'users' => [
            'searchable' => ['id', 'name', 'email', 'position', 'mobile', 'city', 'state', 'joining_date', 'status', 'created_at'],
            'label' => 'Users (Employees)',
        ],
        'candidates' => [
            'searchable' => ['id', 'name', 'email', 'position', 'mobile', 'city', 'state', 'current_company_name', 'current_position', 'education', 'current_ctc', 'expected_ctc', 'notice_period', 'created_at'],
            'label' => 'Job Candidates',
        ],
        'attendances' => [
            'searchable' => ['id', 'user_id', 'date', 'punch_in', 'punch_out', 'status', 'punch_in_location', 'punch_out_location', 'created_at'],
            'label' => 'Attendance logs',
        ],
        'holidays' => [
            'searchable' => ['id', 'name', 'date', 'is_working_day', 'description', 'created_at'],
            'label' => 'Holidays schedule',
        ],
        'assets' => [
            'searchable' => ['id', 'name', 'serial_number', 'type', 'status', 'description', 'created_at'],
            'label' => 'Company Assets',
        ],
        'leave_requests' => [
            'searchable' => ['id', 'user_id', 'leave_type_id', 'start_date', 'end_date', 'days', 'reason', 'status', 'approved_by', 'rejection_reason', 'created_at'],
            'label' => 'Leave requests',
        ],
    ],

    'relationships' => [
        'attendances.user_id = users.id',
        'leave_requests.user_id = users.id',
        'leave_requests.approved_by = users.id',
    ],
];
