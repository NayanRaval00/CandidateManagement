<?php

namespace App\Providers;

use App\Services\Timesheet\DefaultTimesheetCalculator;
use App\Services\Timesheet\TimesheetCalculatorInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            TimesheetCalculatorInterface::class,
            config('timesheets.calculator', DefaultTimesheetCalculator::class)
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Schema::defaultStringLength(191);
    }
}
