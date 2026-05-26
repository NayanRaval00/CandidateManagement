<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\LeaveBalance;

class LeaveBalancesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        // Lazy initialize balances if missing
        $user->initializeLeaveBalances();

        $balances = LeaveBalance::where('user_id', $user->id)
            ->with('leaveType')
            ->get();

        $stats = [];
        foreach ($balances as $balance) {
            $remaining = $balance->balance - $balance->used;
            $stats[] = Stat::make(
                $balance->leaveType->name,
                "{$remaining} / {$balance->balance} Days"
            )
            ->description("Used: {$balance->used} Days")
            ->color($remaining > 0 ? 'success' : 'danger');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        // Only show for employees (admins see balance resource instead)
        return auth()->check() && auth()->user()->hasRole('employee');
    }
}
