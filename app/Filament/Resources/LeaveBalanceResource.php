<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveBalanceResource\Pages;
use App\Models\LeaveBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveBalanceResource extends Resource
{
    protected static ?string $model = LeaveBalance::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Leave Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->role('employee'))
                    ->required()
                    ->label('Employee'),
                Forms\Components\Select::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->required()
                    ->label('Leave Type'),
                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->label('Total Balance (Days)'),
                Forms\Components\TextInput::make('used')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->label('Used (Days)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Employee')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('leaveType.name')->label('Leave Type')->sortable(),
                Tables\Columns\TextColumn::make('balance')->label('Total Balance')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('used')->label('Used')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Remaining')
                    ->numeric()
                    ->sortable()
                    ->state(fn (LeaveBalance $record): int => $record->balance - $record->used),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->role('employee'))
                    ->label('Employee'),
                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->label('Leave Type'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveBalances::route('/'),
            'create' => Pages\CreateLeaveBalance::route('/create'),
            'edit' => Pages\EditLeaveBalance::route('/{record}/edit'),
        ];
    }
}
