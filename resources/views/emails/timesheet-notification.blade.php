<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timesheet Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            color: #1f2937;
            padding: 24px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 32px 24px;
            line-height: 1.6;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 16px;
        }
        .summary-box {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            border: 1px solid #e5e7eb;
        }
        .summary-title {
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .summary-item {
            font-size: 14px;
        }
        .summary-item span {
            color: #4b5563;
        }
        .summary-item strong {
            color: #111827;
        }
        .footer {
            background-color: #f9fafb;
            text-align: center;
            padding: 24px;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Timesheet Summary</h1>
        </div>
        <div class="content">
            <p>Dear {{ $record->user->name }},</p>
            <p>Your timesheet report for the period <strong>{{ $record->batch->start_date->format('M d, Y') }}</strong> to <strong>{{ $record->batch->end_date->format('M d, Y') }}</strong> has been approved by HR.</p>
            
            <p>Please find your frozen timesheet metrics summary below, and a detailed daily breakdown attached to this email as a PDF.</p>

            <div class="summary-box">
                <div class="summary-title">Timesheet Snapshot</div>
                <div style="font-size: 14px; line-height: 1.8;">
                    <div>• <strong>Expected Working Days:</strong> {{ $record->expected_working_days }} days</div>
                    <div>• <strong>Actual Days Worked:</strong> {{ $record->days_worked }} days</div>
                    <div>• <strong>Leaves Taken:</strong> {{ $record->leaves_count }} days</div>
                    <div>• <strong>Late Arrivals:</strong> {{ $record->late_count }} occurrences</div>
                    <div>• <strong>Total Hours Worked:</strong> {{ $record->formatted_hours }}</div>
                </div>
            </div>

            <p>If you have any questions or find any discrepancies, please get in touch with the HR department.</p>

            <p>Best regards,<br><strong>Innoventix Solutions HR Team</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Innoventix Solutions. All rights reserved.<br>
            This is an automated email, please do not reply directly.
        </div>
    </div>
</body>
</html>
