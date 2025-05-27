<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Exports\UsersExport;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('position'),
                TextInput::make('mobile'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('current_company_name'),
                TextInput::make('current_position'),
                TextInput::make('eduction'),
                TextInput::make('current_ctc'),
                TextInput::make('expected_ctc'),
                Textarea::make('reason_for_job_change'),
                TextInput::make('notice_period'),

                FileUpload::make('resume')
                    ->directory('resumes')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048), // Max 2MB

                TextInput::make('password')
                    ->password()
                    ->required(fn(string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->label('Password'),
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
            ->filters([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->actions([

                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn(User $record) => static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

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
                                    Column::make('current_company_name'),
                                    Column::make('current_position'),
                                    Column::make('eduction'),
                                    Column::make('current_ctc'),
                                    Column::make('expected_ctc'),
                                    Column::make('reason_for_job_change'),
                                    Column::make('notice_period'),
                                    Column::make('created_at'),
                                ])
                                ->withFilename('users_export_' . now()->format('Y_m_d_His'))
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
                                Column::make('current_company_name'),
                                Column::make('current_position'),
                                Column::make('eduction'),
                                Column::make('current_ctc'),
                                Column::make('expected_ctc'),
                                Column::make('reason_for_job_change'),
                                Column::make('notice_period'),
                                Column::make('created_at'),
                            ])
                            ->withFilename('users_export_' . now()->format('Y_m_d_His'))
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}/view'), 

        ];
    }
}
