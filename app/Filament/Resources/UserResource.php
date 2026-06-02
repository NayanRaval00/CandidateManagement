<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal & Account Information')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->password()
                                    ->required(fn (string $context) => $context === 'create')
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->label('Password')
                                    ->maxLength(255),
                                TextInput::make('mobile')
                                    ->maxLength(20),
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->required()
                                    ->label('Role'),
                                Select::make('status')
                                    ->options([
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive',
                                    ])
                                    ->required()
                                    ->default('Active')
                                    ->label('Status'),
                            ]),
                    ])->compact(),

                Forms\Components\Section::make('Work & Organization')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('position')
                                    ->maxLength(255),
                                TextInput::make('work_location')
                                    ->label('Office / Work Location')
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('joining_date')
                                    ->label('Joining Date')
                                    ->native(false),
                                Select::make('reporting_to_id')
                                    ->relationship('reportingTo', 'name', modifyQueryUsing: fn (Builder $query, ?User $record) => $record ? $query->where('id', '!=', $record->id) : $query
                                    )
                                    ->label('Reporting To')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])->compact(),

                Forms\Components\Section::make('Residential Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
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

                Forms\Components\Section::make('Emergency Contact Details')
                    ->schema([
                        Forms\Components\Grid::make(3)
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('position')->sortable(),
                TextColumn::make('work_location')->label('Work Location')->sortable()->searchable(),
                TextColumn::make('joining_date')->date()->sortable(),
                TextColumn::make('reportingTo.name')->label('Reporting To')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    ExportBulkAction::make()
                        ->label('Export as CSV')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withColumns([
                                    Column::make('name'),
                                    Column::make('email'),
                                    Column::make('position'),
                                    Column::make('mobile'),
                                    Column::make('city'),
                                    Column::make('state'),
                                    Column::make('residential_address'),
                                    Column::make('emergency_contact_name'),
                                    Column::make('emergency_contact_relation'),
                                    Column::make('emergency_contact_number'),
                                    Column::make('emergency_contact_address'),
                                    Column::make('reportingTo.name')->heading('Reporting To'),
                                    Column::make('work_location'),
                                    Column::make('joining_date'),
                                    Column::make('status'),
                                    Column::make('created_at'),
                                ])
                                ->withFilename('users_export_'.now()->format('Y_m_d_His')),
                        ]),

                ]),
            ])
            ->headerActions([
                ExportBulkAction::make()
                    ->label('Export All Users')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('name'),
                                Column::make('email'),
                                Column::make('position'),
                                Column::make('mobile'),
                                Column::make('city'),
                                Column::make('state'),
                                Column::make('residential_address'),
                                Column::make('emergency_contact_name'),
                                Column::make('emergency_contact_relation'),
                                Column::make('emergency_contact_number'),
                                Column::make('emergency_contact_address'),
                                Column::make('reportingTo.name')->heading('Reporting To'),
                                Column::make('work_location'),
                                Column::make('joining_date'),
                                Column::make('status'),
                                Column::make('created_at'),
                            ])
                            ->withFilename('users_export_'.now()->format('Y_m_d_His')),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LeaveBalancesRelationManager::class,
            RelationManagers\LeaveRequestsRelationManager::class,
            RelationManagers\AssetsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('reportingTo');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),

        ];
    }
}
