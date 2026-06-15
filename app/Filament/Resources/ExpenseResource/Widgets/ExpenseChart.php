<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Expenses (Current Year)';

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $year = Carbon::now()->year;

        // Initialize array for 12 months with 0
        $monthlySums = array_fill(1, 12, 0.0);

        // Fetch current year's expenses and group by month in a database-agnostic way
        $yearExpenses = Expense::whereYear('expense_date', $year)->get();
        $grouped = $yearExpenses->groupBy(fn($expense) => Carbon::parse($expense->expense_date)->month);

        foreach ($grouped as $monthNum => $items) {
            $monthlySums[$monthNum] = (float) $items->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Expenses (₹)',
                    'data' => array_values($monthlySums),
                    'backgroundColor' => '#605BF0',
                    'borderColor' => '#605BF0',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
