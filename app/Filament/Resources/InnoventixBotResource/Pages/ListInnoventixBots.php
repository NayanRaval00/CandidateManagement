<?php

namespace App\Filament\Resources\InnoventixBotResource\Pages;

use App\Filament\Resources\InnoventixBotResource;
use Filament\Resources\Pages\ListRecords;

class ListInnoventixBots extends ListRecords
{
    protected static string $resource = InnoventixBotResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
