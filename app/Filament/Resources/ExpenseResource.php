<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Services\ExpensePdfService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Expense Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Expense Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('category')
                                    ->options([
                                        'Travel' => 'Travel',
                                        'Food' => 'Food',
                                        'Utilities' => 'Utilities',
                                        'Software' => 'Software',
                                        'Rent' => 'Rent',
                                        'Marketing' => 'Marketing',
                                        'Other' => 'Other',
                                    ])
                                    ->required()
                                    ->searchable(),
                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->required()
                                    ->minValue(0.01),
                                DateTimePicker::make('expense_date')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                                Textarea::make('description')
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
                TextColumn::make('expense_date')
                    ->label('Date & Time')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Travel' => 'info',
                        'Food' => 'warning',
                        'Utilities' => 'success',
                        'Software' => 'info',
                        'Rent' => 'gray',
                        'Marketing' => 'danger',
                        'Other' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Travel' => 'Travel',
                        'Food' => 'Food',
                        'Utilities' => 'Utilities',
                        'Software' => 'Software',
                        'Rent' => 'Rent',
                        'Marketing' => 'Marketing',
                        'Other' => 'Other',
                    ]),
                Filter::make('expense_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('To Date')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Table $table) {
                        $records = $table->getLivewire()->getFilteredTableQuery()->get();

                        if ($records->isEmpty()) {
                            Notification::make()
                                ->title('No records to export')
                                ->warning()
                                ->send();

                            return;
                        }

                        $pdfService = app(ExpensePdfService::class);
                        $pdf = $pdfService->generate($records, auth()->user()->name);

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'expenses_report_' . now()->format('Y_m_d_His') . '.pdf'
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_selected_pdf')
                        ->label('Export Selected to PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('No records selected')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $pdfService = app(ExpensePdfService::class);
                            $pdf = $pdfService->generate($records, auth()->user()->name);

                            return response()->streamDownload(
                                fn() => print($pdf->output()),
                                'expenses_selected_' . now()->format('Y_m_d_His') . '.pdf'
                            );
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ExpenseResource\Widgets\ExpenseOverview::class,
            ExpenseResource\Widgets\ExpenseChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
