<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use App\Models\User;

class ViewUser extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.view-user';

    public User $record;

    public function mount(User $record): void
    {
        $this->record = $record;
    }

    protected function getViewData(): array
    {
        return ['user' => $this->record];
    }

    public static function getSlug(): string
    {
        return 'view/{record}';
    }

    // public static function getRouteName(): string
    // {
    //     return 'filament.resources.users.view';
    // }
}

