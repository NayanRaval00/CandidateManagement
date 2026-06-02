<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Leave Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Request Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->role('employee'))
                            ->required()
                            ->visible(fn () => auth()->user()->hasRole('admin'))
                            ->default(fn () => auth()->id())
                            ->dehydrated(true)
                            ->label('Employee'),

                        Forms\Components\Select::make('leave_type_id')
                            ->relationship('leaveType', 'name')
                            ->required()
                            ->live()
                            ->label('Leave Type'),

                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn ($state, $get, $set) => self::calculateDays($state, $get('end_date'), $set))
                            ->label('Start Date'),

                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn ($state, $get, $set) => self::calculateDays($get('start_date'), $state, $set))
                            ->label('End Date'),

                        Forms\Components\TextInput::make('days')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->label('Total Days')
                            ->rules([
                                fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                                    $leaveTypeId = $get('leave_type_id');
                                    if (! $leaveTypeId || ! $value) {
                                        return;
                                    }

                                    if ($value <= 0) {
                                        $fail('Leave duration must be at least 1 weekday.');

                                        return;
                                    }

                                    $userId = auth()->user()->hasRole('admin') ? ($get('user_id') ?? auth()->id()) : auth()->id();

                                    // Lazy initialize if needed
                                    $user = User::find($userId);
                                    if ($user) {
                                        $user->initializeLeaveBalances();
                                    }

                                    $balance = LeaveBalance::where('user_id', $userId)
                                        ->where('leave_type_id', $leaveTypeId)
                                        ->first();

                                    if (! $balance) {
                                        $fail('No leave balance record found for the selected employee.');

                                        return;
                                    }

                                    $remaining = $balance->balance - $balance->used;
                                    if ($value > $remaining) {
                                        $fail("Requested duration of {$value} days exceeds the remaining balance of {$remaining} days.");
                                    }
                                },
                            ]),

                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Reason for Leave'),
                    ])->columns(2),

                Forms\Components\Section::make('Approval Information')
                    ->schema([
                        Forms\Components\TextInput::make('status')
                            ->readOnly()
                            ->default('pending')
                            ->label('Status'),
                        Forms\Components\TextInput::make('approver.name')
                            ->readOnly()
                            ->label('Processed By')
                            ->visible(fn ($record) => $record && $record->approved_by !== null),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->readOnly()
                            ->label('Rejection Reason')
                            ->visible(fn ($record) => $record && $record->status === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($context) => $context === 'edit' || $context === 'view')
                    ->columns(2),
            ]);
    }

    public static function calculateDays(?string $start, ?string $end, callable $set): void
    {
        if (! $start || ! $end) {
            $set('days', 0);

            return;
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($startDate->gt($endDate)) {
            $set('days', 0);

            return;
        }

        // Calculate weekdays only (exclude Saturday & Sunday)
        $days = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (! $current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }

        $set('days', $days);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Employee')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('leaveType.name')->label('Leave Type')->sortable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable()->label('Days'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->relationship('leaveType', 'name')
                    ->label('Leave Type'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (LeaveRequest $record) => auth()->user()->can('update', $record)),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => auth()->user()->can('approve', $record) && $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Leave request approved successfully!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => auth()->user()->can('reject', $record) && $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('Reason for Rejection'),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Leave request rejected.')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Cancel')
                    ->visible(fn (LeaveRequest $record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole('admin')) {
            return $query;
        }

        // Managers can see their own requests AND requests of users reporting to them.
        // Other employees can only see their own requests.
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereIn('user_id', User::where('reporting_to_id', $user->id)->select('id'));
        });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'view' => Pages\ViewLeaveRequest::route('/{record}'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
