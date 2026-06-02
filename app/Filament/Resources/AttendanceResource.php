<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Leave & Attendance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Manual Attendance Override')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Employee'),
                                DatePicker::make('date')
                                    ->required()
                                    ->native(false)
                                    ->default(today())
                                    ->label('Attendance Date'),
                                DateTimePicker::make('punch_in')
                                    ->native(false)
                                    ->displayFormat('Y-m-d h:i A')
                                    ->label('Punch In Time'),
                                DateTimePicker::make('punch_out')
                                    ->native(false)
                                    ->displayFormat('Y-m-d h:i A')
                                    ->label('Punch Out Time'),
                                Select::make('status')
                                    ->options([
                                        'Present' => 'Present',
                                        'Late' => 'Late',
                                        'Half Day' => 'Half Day',
                                        'Absent' => 'Absent',
                                    ])
                                    ->required()
                                    ->default('Present')
                                    ->label('Status'),
                            ]),
                    ]),

                Section::make('Location Logs (Optional Override)')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('punch_in_latitude')
                                    ->numeric()
                                    ->step('any')
                                    ->label('In Latitude'),
                                TextInput::make('punch_in_longitude')
                                    ->numeric()
                                    ->step('any')
                                    ->label('In Longitude'),
                                TextInput::make('punch_out_latitude')
                                    ->numeric()
                                    ->step('any')
                                    ->label('Out Latitude'),
                                TextInput::make('punch_out_longitude')
                                    ->numeric()
                                    ->step('any')
                                    ->label('Out Longitude'),
                                TextInput::make('punch_in_location')
                                    ->columnSpan(2)
                                    ->label('Punch In Location Name'),
                                TextInput::make('punch_out_location')
                                    ->columnSpan(2)
                                    ->label('Punch Out Location Name'),
                            ]),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('punch_in')
                    ->dateTime('h:i A')
                    ->label('Punch In')
                    ->sortable(),
                TextColumn::make('punch_out')
                    ->dateTime('h:i A')
                    ->label('Punch Out')
                    ->sortable(),
                TextColumn::make('punch_in_latitude')
                    ->label('Punch In Location')
                    ->state(fn ($record) => $record->punch_in_latitude ? "{$record->punch_in_latitude}, {$record->punch_in_longitude}" : 'N/A')
                    ->description(fn ($record) => $record->punch_in_location),
                TextColumn::make('punch_out_latitude')
                    ->label('Punch Out Location')
                    ->state(fn ($record) => $record->punch_out_latitude ? "{$record->punch_out_latitude}, {$record->punch_out_longitude}" : 'N/A')
                    ->description(fn ($record) => $record->punch_out_location),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Present' => 'success',
                        'Late' => 'warning',
                        'Half Day' => 'info',
                        'Absent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Employee'),
                Filter::make('date')
                    ->form([
                        DatePicker::make('date_filter')
                            ->label('Day-wise')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when($data['date_filter'], fn ($q) => $q->whereDate('date', $data['date_filter']))),
                SelectFilter::make('month')
                    ->options([
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ])
                    ->label('Month')
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn ($q) => $q->whereMonth('date', $data['value']))),
                SelectFilter::make('year')
                    ->options(fn () => array_combine(range(date('Y'), date('Y') - 5), range(date('Y'), date('Y') - 5)))
                    ->label('Year')
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn ($q) => $q->whereYear('date', $data['value']))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    ExportBulkAction::make()
                        ->label('Export Report')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withColumns([
                                    Column::make('user.name')->heading('Employee Name'),
                                    Column::make('date')->heading('Date'),
                                    Column::make('punch_in')->heading('Punch In Time'),
                                    Column::make('punch_out')->heading('Punch Out Time'),
                                    Column::make('punch_in_latitude')->heading('Punch In Latitude'),
                                    Column::make('punch_in_longitude')->heading('Punch In Longitude'),
                                    Column::make('punch_in_location')->heading('Punch In Location'),
                                    Column::make('punch_out_latitude')->heading('Punch Out Latitude'),
                                    Column::make('punch_out_longitude')->heading('Punch Out Longitude'),
                                    Column::make('punch_out_location')->heading('Punch Out Location'),
                                    Column::make('status')->heading('Status'),
                                ])
                                ->withFilename('attendance_report_'.now()->format('Y_m_d_His')),
                        ]),
                ]),
            ])
            ->headerActions([
                ExportBulkAction::make()
                    ->label('Export All Filtered')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('user.name')->heading('Employee Name'),
                                Column::make('date')->heading('Date'),
                                Column::make('punch_in')->heading('Punch In Time'),
                                Column::make('punch_out')->heading('Punch Out Time'),
                                Column::make('punch_in_latitude')->heading('Punch In Latitude'),
                                Column::make('punch_in_longitude')->heading('Punch In Longitude'),
                                Column::make('punch_in_location')->heading('Punch In Location'),
                                Column::make('punch_out_latitude')->heading('Punch Out Latitude'),
                                Column::make('punch_out_longitude')->heading('Punch Out Longitude'),
                                Column::make('punch_out_location')->heading('Punch Out Location'),
                                Column::make('status')->heading('Status'),
                            ])
                            ->withFilename('attendance_report_'.now()->format('Y_m_d_His')),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
