<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemNotificationResource\Pages;
use App\Filament\Resources\SystemNotificationResource\RelationManagers;
use App\Models\SystemNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SystemNotificationResource extends Resource
{
    protected static ?string $model = SystemNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Announcement / Notification Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'info' => 'Info',
                                'success' => 'Success',
                                'warning' => 'Warning',
                                'danger' => 'Danger',
                            ])
                            ->default('info')
                            ->required(),
                        Forms\Components\Select::make('target_type')
                            ->options([
                                'all' => 'All Users',
                                'specific' => 'Specific User',
                            ])
                            ->default('all')
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->requiredIf('target_type', 'specific')
                            ->visible(fn (Forms\Get $get) => $get('target_type') === 'specific')
                            ->label('Recipient'),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        default => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_type')
                    ->formatStateUsing(fn (string $state): string => $state === 'all' ? 'All Users' : 'Specific User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recipient')
                    ->placeholder('All Users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger' => 'Danger',
                    ]),
                Tables\Filters\SelectFilter::make('target_type')
                    ->options([
                        'all' => 'All Users',
                        'specific' => 'Specific User',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemNotifications::route('/'),
            'create' => Pages\CreateSystemNotification::route('/create'),
            'edit' => Pages\EditSystemNotification::route('/{record}/edit'),
        ];
    }
}
