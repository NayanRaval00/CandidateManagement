<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Expense Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }

        /* Header section */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-container {
            width: 50%;
        }

        .logo-img {
            max-height: 35px;
            display: block;
        }

        .report-title-container {
            width: 50%;
            text-align: right;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #605BF0;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            font-size: 10px;
            color: #777777;
            margin: 0;
        }

        /* Metadata & Info Section */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-top: 1px solid #EAEAEA;
            border-bottom: 1px solid #EAEAEA;
            padding: 8px 0;
        }

        .info-table td {
            padding: 6px 0;
            font-size: 9px;
            color: #555555;
        }

        .info-label {
            font-weight: bold;
            color: #333333;
        }

        /* Summary Cards */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin: 0 -12px 25px -12px;
        }

        .summary-card {
            background-color: #F8F9FD;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            width: 33.33%;
        }

        .summary-val {
            font-size: 16px;
            font-weight: bold;
            color: #605BF0;
            margin-bottom: 4px;
        }

        .summary-lbl {
            font-size: 9px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details-table th {
            background-color: #605BF0;
            color: #FFFFFF;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #605BF0;
        }

        .details-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E2E8F0;
            vertical-align: top;
        }

        .details-table tr:nth-child(even) {
            background-color: #F8F9FA;
        }

        .amount-col {
            text-align: right;
            font-weight: bold;
        }

        .total-row {
            background-color: #EDECFD !important;
            font-weight: bold;
            border-top: 2px solid #605BF0;
            border-bottom: 2px solid #605BF0;
        }

        .total-row td {
            padding: 10px;
            color: #605BF0;
            font-size: 11px;
        }

        /* Helpers */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #E2E8F0;
            color: #4A5568;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-travel {
            background-color: #EBF8FF;
            color: #2B6CB0;
        }

        .badge-food {
            background-color: #FEEBC8;
            color: #C05621;
        }

        .badge-utilities {
            background-color: #E6FFFA;
            color: #234E52;
        }

        .badge-software {
            background-color: #EBF4FF;
            color: #2B6CB0;
        }

        .badge-rent {
            background-color: #EDF2F7;
            color: #2D3748;
        }

        .badge-marketing {
            background-color: #FED7D7;
            color: #9B2C2C;
        }

        .badge-other {
            background-color: #EDF2F7;
            color: #4A5568;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 20px;
            text-align: center;
            border-top: 1px solid #EAEAEA;
            padding-top: 5px;
            font-size: 8px;
            color: #A0AEC0;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                @if($logoBase64)
                <img class="logo-img" src="{{ $logoBase64 }}" alt="Innoventix Solutions" />
                @else
                <span style="font-size: 16px; font-weight: bold; color: #605BF0;">Innoventix Solutions</span>
                @endif
            </td>
            <td class="report-title-container">
                <div class="report-title">EXPENSE REPORT</div>
                <div class="report-subtitle">Generated on {{ \Carbon\Carbon::parse($generatedAt)->format('F d, Y h:i A') }}</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Section -->
    <table class="info-table">
        <tr>
            <td width="15%"><span class="info-label">Generated By:</span></td>
            <td width="35%">{{ $generatedBy }}</td>
            <td width="15%"><span class="info-label">Report Period:</span></td>
            <td width="35%">
                @if($minDate && $maxDate)
                {{ \Carbon\Carbon::parse($minDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($maxDate)->format('M d, Y') }}
                @else
                All-Time
                @endif
            </td>
        </tr>
    </table>

    <!-- Summary Cards -->
    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="summary-val">&#8377;{{ number_format($totalAmount, 2) }}</div>
                <div class="summary-lbl">Total Expenses</div>
            </td>
            <td class="summary-card">
                <div class="summary-val">{{ $totalCount }}</div>
                <div class="summary-lbl">Transactions</div>
            </td>
            <td class="summary-card">
                <div class="summary-val">
                    @if($totalCount > 0)
                    &#8377;{{ number_format($totalAmount / $totalCount, 2) }}
                    @else
                    &#8377;0.00
                    @endif
                </div>
                <div class="summary-lbl">Average / Transaction</div>
            </td>
        </tr>
    </table>

    <!-- Details Table -->
    <table class="details-table">
        <thead>
            <tr>
                <th width="18%">Date & Time</th>
                <th width="32%">Title</th>
                <th width="15%">Category</th>
                <th width="20%">Description</th>
                <th width="15%" class="amount-col">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y h:i A') }}</td>
                <td style="font-weight: 500; color: #1A202C;">{{ $expense->title }}</td>
                <td>
                    <span class="badge badge-{{ strtolower($expense->category) }}">
                        {{ $expense->category }}
                    </span>
                </td>
                <td style="color: #718096; font-size: 10px;">{{ $expense->description ?? '-' }}</td>
                <td class="amount-col">&#8377;{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #718096; padding: 20px;">
                    No expense records found.
                </td>
            </tr>
            @endforelse

            @if($expenses->count() > 0)
            <tr class="total-row">
                <td colspan="4" style="text-align: right; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Total</td>
                <td class="amount-col">&#8377;{{ number_format($totalAmount, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer Page Numbers -->
    <div class="footer">
        Innoventix Solutions &bull; Confidential Expense Report &bull; Page 1
    </div>

</body>

</html>