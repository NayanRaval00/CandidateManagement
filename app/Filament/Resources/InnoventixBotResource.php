<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InnoventixBotResource\Pages;
use App\Models\InnoventixBot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InnoventixBotResource extends Resource
{
    protected static ?string $model = InnoventixBot::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Innoventix Bot Logs';

    protected static ?string $modelLabel = 'Bot Log';

    protected static ?string $pluralModelLabel = 'Bot Logs';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('User')
                            ->placeholder('Anonymous')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Executed At')
                            ->disabled(),
                        Forms\Components\Toggle::make('is_successful')
                            ->label('Execution Successful')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Natural Language Prompt')
                    ->schema([
                        Forms\Components\Textarea::make('prompt')
                            ->label('Prompt')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Query Execution')
                    ->schema([
                        Forms\Components\Textarea::make('sql_query')
                            ->label('Generated SQL')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->rows(2)
                            ->disabled()
                            ->visible(fn ($record) => $record && ! $record->is_successful)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Execution Results')
                    ->schema([
                        Forms\Components\Textarea::make('results')
                            ->label('Returned JSON Data')
                            ->rows(15)
                            ->disabled()
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $state)
                            ->columnSpanFull(),
                    ])->visible(fn ($record) => $record && $record->is_successful),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Executed At'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Anonymous')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prompt')
                    ->searchable()
                    ->limit(60)
                    ->label('Prompt'),
                Tables\Columns\IconColumn::make('is_successful')
                    ->boolean()
                    ->sortable()
                    ->label('Success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_successful')
                    ->label('Success Status')
                    ->trueLabel('Successful')
                    ->falseLabel('Failed'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListInnoventixBots::route('/'),
        ];
    }
}
