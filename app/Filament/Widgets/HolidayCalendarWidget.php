<?php

namespace App\Filament\Widgets;

use App\Models\Holiday;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class HolidayCalendarWidget extends FullCalendarWidget
{
    public \Illuminate\Database\Eloquent\Model|string|null $model = Holiday::class;

    public \Illuminate\Database\Eloquent\Model|int|string|null $record = null;

    /**
     * Fetch events to populate the calendar.
     *
     * @param  array{start: string, end: string, timezone: string}  $fetchInfo
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return Holiday::query()
            ->where('date', '>=', $fetchInfo['start'])
            ->where('date', '<=', $fetchInfo['end'])
            ->get()
            ->map(fn (Holiday $holiday): array => [
                'id' => $holiday->id,
                'title' => $holiday->name.($holiday->is_working_day ? ' (Working)' : ' (Closed)'),
                'start' => $holiday->date->toDateString(),
                'allDay' => true,
                'backgroundColor' => $holiday->is_working_day ? 'rgba(14, 165, 233, 0.15)' : 'rgba(244, 63, 94, 0.15)',
                'borderColor' => $holiday->is_working_day ? '#0ea5e9' : '#f43f5e',
                'textColor' => $holiday->is_working_day ? '#0284c7' : '#e11d48',
            ])
            ->toArray();
    }

    /**
     * Form schema for creating/editing holiday events.
     */
    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Holiday Name'),
            DatePicker::make('date')
                ->required()
                ->native(false)
                ->label('Date'),
            Toggle::make('is_working_day')
                ->default(false)
                ->label('Is Office Working Day?')
                ->helperText('If enabled, the office is open. If disabled, the office is closed.'),
            Textarea::make('description')
                ->maxLength(65535)
                ->rows(3)
                ->label('Description/Notes'),
        ];
    }

    /**
     * Header actions (e.g. Create Holiday).
     */
    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn (): bool => auth()->user()->hasRole('admin'))
                ->mountUsing(function (Form $form, array $arguments): void {
                    $form->fill([
                        'date' => isset($arguments['start']) ? Carbon::parse($arguments['start'])->toDateString() : null,
                    ]);
                }),
        ];
    }

    /**
     * Modal actions when an event is clicked.
     */
    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (): bool => auth()->user()->hasRole('admin')),
            Actions\ViewAction::make()
                ->visible(fn (): bool => ! auth()->user()->hasRole('admin')),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()->hasRole('admin')),
        ];
    }
}
