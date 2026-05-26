<?php

namespace App\Filament\Pages;

use App\Models\Holiday;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HolidayCalendar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Leave Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Holiday Calendar';

    protected static ?string $title = 'Holiday Calendar';

    protected static string $view = 'filament.pages.holiday-calendar';

    public function table(Table $table): Table
    {
        return $table
            ->query(Holiday::query()->whereYear('date', now()->year))
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Holiday Name'),
                TextColumn::make('is_working_day')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'info' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Working Day' : 'Office Closed')
                    ->label('Office Status'),
                TextColumn::make('description')
                    ->limit(50)
                    ->label('Description'),
            ])
            ->defaultSort('date', 'asc')
            ->filters([
                TernaryFilter::make('is_working_day')
                    ->label('Office Status')
                    ->trueLabel('Working Day')
                    ->falseLabel('Office Closed'),
            ]);
    }
}
