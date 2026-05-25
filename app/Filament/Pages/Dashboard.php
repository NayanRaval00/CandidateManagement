<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Redirect employees to their profile page.
     */
    public function mount(): void
    {
        // if (auth()->check() && auth()->user()->hasRole('employee')) {
        //     redirect()->to(MyProfile::getUrl());
        // }
    }
}
