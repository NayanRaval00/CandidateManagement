<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpenseOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::tomorrow()->subSecond();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();

        $todaySum = Expense::whereBetween('expense_date', [$todayStart, $todayEnd])->sum('amount');
        $monthSum = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');
        $yearSum = Expense::whereBetween('expense_date', [$yearStart, $yearEnd])->sum('amount');

        return [
            Stat::make("Today's Expenses", '₹' . number_format($todaySum, 2))
                ->description('Total spent today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make("This Month's Expenses", '₹' . number_format($monthSum, 2))
                ->description('Total spent this month')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
            Stat::make("This Year's Expenses", '₹' . number_format($yearSum, 2))
                ->description('Total spent this year')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
