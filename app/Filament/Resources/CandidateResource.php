<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\TextInput::make('position')->disabled(),
                Forms\Components\TextInput::make('mobile')->disabled(),
                Forms\Components\TextInput::make('city')->disabled(),
                Forms\Components\TextInput::make('state')->disabled(),
                Forms\Components\TextInput::make('current_company_name')->disabled(),
                Forms\Components\TextInput::make('current_position')->disabled(),
                Forms\Components\TextInput::make('education')->disabled(),
                Forms\Components\TextInput::make('current_ctc')->disabled(),
                Forms\Components\TextInput::make('expected_ctc')->disabled(),
                Forms\Components\Textarea::make('reason_for_job_change')->disabled(),
                Forms\Components\TextInput::make('notice_period')->disabled(),
                Forms\Components\FileUpload::make('resume')->disabled()->disk('public_root'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('position'),
                TextColumn::make('city'),
                TextColumn::make('state'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn(Candidate $record) => static::getUrl('view', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
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
                                    Column::make('current_company_name'),
                                    Column::make('current_position'),
                                    Column::make('education'),
                                    Column::make('current_ctc'),
                                    Column::make('expected_ctc'),
                                    Column::make('reason_for_job_change'),
                                    Column::make('notice_period'),
                                    Column::make('created_at'),
                                ])
                                ->withFilename('candidates_export_' . now()->format('Y_m_d_His'))
                        ]),
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
            'index' => Pages\ListCandidates::route('/'),
            'view' => Pages\ViewCandidate::route('/{record}/view'),
        ];
    }
}
