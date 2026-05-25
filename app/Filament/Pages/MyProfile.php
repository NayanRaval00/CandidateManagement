<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.pages.my-profile';

    protected static ?string $title = 'My Profile';

    public ?User $record = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->record = auth()->user();
        
        if ($this->record) {
            $data = $this->record->toArray();
            $data['reporting_to_name'] = $this->record->reportingTo?->name;
            $this->form->fill($data);
        }
    }

    /**
     * Determine if this page should appear in the navigation menu.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('employee');
    }

    /**
     * Define the profile update form schema.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(User::class, 'email', ignorable: $this->record),
                                TextInput::make('mobile')
                                    ->maxLength(20),
                            ]),
                    ])->compact(),

                Section::make('Work & Organization')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('position')
                                    ->disabled()
                                    ->maxLength(255),
                                TextInput::make('work_location')
                                    ->label('Office / Work Location')
                                    ->disabled()
                                    ->maxLength(255),
                                \Filament\Forms\Components\DatePicker::make('joining_date')
                                    ->label('Joining Date')
                                    ->native(false)
                                    ->disabled(),
                                TextInput::make('reporting_to_name')
                                    ->label('Reporting To')
                                    ->disabled()
                                    ->maxLength(255),
                            ]),
                    ])->compact(),

                Section::make('Residential Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('city')
                                    ->maxLength(255),
                                TextInput::make('state')
                                    ->maxLength(255),
                                Textarea::make('residential_address')
                                    ->label('Residential Address')
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),
                    ])->compact(),

                Section::make('Emergency Contact Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('emergency_contact_name')
                                    ->label('Emergency Contact Name')
                                    ->maxLength(255),
                                TextInput::make('emergency_contact_relation')
                                    ->label('Emergency Contact Relation')
                                    ->maxLength(255),
                                TextInput::make('emergency_contact_number')
                                    ->label('Emergency Contact Number')
                                    ->tel()
                                    ->maxLength(20),
                                Textarea::make('emergency_contact_address')
                                    ->label('Emergency Contact Address')
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),
                    ])->compact(),

                Section::make('Security & Password')
                    ->schema([
                        Toggle::make('change_password')
                            ->label('Update Password')
                            ->live()
                            ->columnSpanFull(),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->password()
                                    ->required(fn ($get) => $get('change_password'))
                                    ->visible(fn ($get) => $get('change_password'))
                                    ->label('New Password')
                                    ->maxLength(255),
                                TextInput::make('password_confirmation')
                                    ->password()
                                    ->required(fn ($get) => $get('change_password'))
                                    ->visible(fn ($get) => $get('change_password'))
                                    ->same('password')
                                    ->label('Confirm Password')
                                    ->maxLength(255),
                            ])
                    ])->compact()
            ])
            ->statePath('data');
    }

    /**
     * Save profile changes and update the preview.
     */
    public function save(): void
    {
        $formData = $this->form->getState();

        $updateData = collect($formData)
            ->except([
                'change_password', 
                'password', 
                'password_confirmation',
                'position',
                'work_location',
                'joining_date',
                'reporting_to_id',
                'reporting_to_name'
            ])
            ->toArray();

        if (!empty($formData['change_password']) && !empty($formData['password'])) {
            $updateData['password'] = Hash::make($formData['password']);
        }

        $this->record->update($updateData);
        $this->record = $this->record->fresh();

        // Refill form with updated record, resetting toggle & password inputs
        $data = $this->record->toArray();
        $data['reporting_to_name'] = $this->record->reportingTo?->name;
        $this->form->fill($data);

        Notification::make()
            ->title('Profile updated successfully!')
            ->success()
            ->send();
    }

    /**
     * Define the form action buttons.
     */
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save'),
        ];
    }
}
