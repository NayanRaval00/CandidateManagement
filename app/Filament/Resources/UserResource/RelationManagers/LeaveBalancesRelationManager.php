<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveBalancesRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveBalances';

    protected static ?string $title = 'Leave Balances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->required()
                    ->label('Total Balance (Days)'),
                Forms\Components\TextInput::make('used')
                    ->numeric()
                    ->required()
                    ->label('Used (Days)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Total Balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used')
                    ->label('Used')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Remaining')
                    ->numeric()
                    ->state(fn ($record) => $record->balance - $record->used),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
