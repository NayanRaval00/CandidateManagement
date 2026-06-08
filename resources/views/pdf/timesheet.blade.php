<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timesheet Report - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d3748;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-logo {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
        }
        .header-title {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #718096;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-label {
            color: #718096;
            font-weight: bold;
            padding: 4px 0;
            width: 18%;
        }
        .meta-value {
            padding: 4px 0;
            width: 32%;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .stats-card {
            background-color: #f7fafc;
            border: 1px solid #edf2f7;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            width: 15%;
        }
        .stats-val {
            font-size: 18px;
            font-weight: bold;
            color: #1a202c;
            margin-top: 4px;
        }
        .stats-lbl {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
            font-weight: bold;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .breakdown-table th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 11px;
            text-transform: uppercase;
        }
        .breakdown-table td {
            padding: 10px;
            border-bottom: 1px solid #edf2f7;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-present {
            background-color: #def7ec;
            color: #03543f;
        }
        .badge-leave {
            background-color: #e1effe;
            color: #1e429f;
        }
        .badge-holiday {
            background-color: #f3f4f6;
            color: #374151;
        }
        .badge-absent {
            background-color: #fde8e8;
            color: #9b1c1c;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #a0aec0;
            border-top: 1px solid #edf2f7;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">INNOVENTIX SOLUTIONS</td>
            <td class="header-title">Timesheet Report</td>
        </tr>
    </table>

    <div class="section-title">Employee & Period Information</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Employee Name:</td>
            <td class="meta-value">{{ $user->name }}</td>
            <td class="meta-label">Start Date:</td>
            <td class="meta-value">{{ $batch->start_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Email Address:</td>
            <td class="meta-value">{{ $user->email }}</td>
            <td class="meta-label">End Date:</td>
            <td class="meta-value">{{ $batch->end_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Generated On:</td>
            <td class="meta-value">{{ $record->created_at->format('F d, Y h:i A') }}</td>
            <td class="meta-label">Batch ID:</td>
            <td class="meta-value">#{{ $batch->id }}</td>
        </tr>
    </table>

    <div class="section-title">Performance Summary</div>
    <table class="stats-table">
        <tr>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Calendar Days</div>
                <div class="stats-val">{{ $record->total_calendar_days }}</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Expected Work Days</div>
                <div class="stats-val">{{ $record->expected_working_days }}</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Days Worked</div>
                <div class="stats-val">{{ $record->days_worked }}</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Leaves Taken</div>
                <div class="stats-val">{{ $record->leaves_count }}</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Company Holidays</div>
                <div class="stats-val">{{ $record->holidays_count }}</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Late Arrivals</div>
                <div class="stats-val" style="color: {{ $record->late_count > 0 ? '#b91c1c' : '#1a202c' }};">
                    {{ $record->late_count }}
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td class="stats-card" style="padding: 12px 0;">
                <div class="stats-lbl">Hours Worked</div>
                <div class="stats-val" style="color: #4f46e5;">{{ $record->formatted_hours }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Daily Status Breakdown</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th style="width: 25%;">Date</th>
                <th style="width: 20%;">Day</th>
                <th style="width: 25%;">Status</th>
                <th style="width: 30%;">Details</th>
            </tr>
        </thead>
        <tbody>
            @php
                $startDate = \Carbon\Carbon::parse($batch->start_date);
                $endDate = \Carbon\Carbon::parse($batch->end_date);
                $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
                $breakdown = $record->daily_breakdown_json;
            @endphp
            @foreach($period as $date)
                @php
                    $dateStr = $date->format('Y-m-d');
                    $status = $breakdown[$dateStr] ?? 'Absent';
                @endphp
                <tr>
                    <td>{{ $date->format('M d, Y') }}</td>
                    <td>{{ $date->format('l') }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($status) }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td>
                        @if($status === 'Present')
                            Active Workday
                        @elseif($status === 'Leave')
                            Approved Leave
                        @elseif($status === 'Holiday')
                            @if($date->isWeekend())
                                Weekend
                            @else
                                Company Holiday
                            @endif
                        @else
                            No Attendance Logged
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential Document &copy; {{ date('Y') }} Innoventix Solutions. Generated automatically via HR Dashboard.
    </div>

</body>
</html>
