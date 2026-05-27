<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveRequests';

    protected static ?string $title = 'Leave Requests & History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->required()
                    ->label('Leave Type'),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('Start Date'),
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->label('End Date'),
                Forms\Components\TextInput::make('days')
                    ->numeric()
                    ->required()
                    ->label('Days'),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        return LeaveRequest::pluck('start_date')
                            ->map(fn ($date) => $date?->format('Y'))
                            ->filter()
                            ->unique()
                            ->sortDesc()
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereYear('start_date', $data['value']);
                        }
                    })
                    ->label('Filter By Year'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
