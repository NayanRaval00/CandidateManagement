<?php

namespace App\Filament\Pages;

use App\Models\AttendanceSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageAttendanceSettings extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Leave & Attendance';

    protected static ?string $navigationLabel = 'Attendance Settings';

    protected static ?string $title = 'Attendance Settings';

    protected static string $view = 'filament.pages.manage-attendance-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function mount(): void
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $setting = AttendanceSetting::getSingleton();
        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Geo-Fencing Rules')
                    ->description('Set coordinates and radius for the allowed punch-in zone.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->required()
                                    ->step('any')
                                    ->label('Office Latitude'),
                                TextInput::make('longitude')
                                    ->numeric()
                                    ->required()
                                    ->step('any')
                                    ->label('Office Longitude'),
                                TextInput::make('radius')
                                    ->numeric()
                                    ->required()
                                    ->suffix('meters')
                                    ->label('Allowed Radius (Meters)'),
                            ]),
                    ]),

                Section::make('Timing & Delay Rules')
                    ->description('Set timing limits and punch constraints.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_punch_out_delay')
                                    ->numeric()
                                    ->required()
                                    ->suffix('minutes')
                                    ->label('Min Punch Out Delay')
                                    ->helperText('Minimum minutes required between Punch In and Punch Out.'),
                                TimePicker::make('punch_in_start')
                                    ->native(false)
                                    ->displayFormat('h:i A')
                                    ->format('H:i:s')
                                    ->label('Punch In Starts'),
                                TimePicker::make('punch_in_end')
                                    ->native(false)
                                    ->displayFormat('h:i A')
                                    ->format('H:i:s')
                                    ->label('Punch In Ends'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $setting = AttendanceSetting::getSingleton();
        $setting->update($this->form->getState());

        Notification::make()
            ->title('Attendance Settings updated successfully!')
            ->success()
            ->send();
    }
}
