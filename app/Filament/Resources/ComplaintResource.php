<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Filament\Resources\ComplaintResource\RelationManagers;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'hugeicons-complaint';

    protected static ?string $navigationGroup = 'Complaint Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('category_complaint_id')
                    ->relationship('categoryComplaint', 'name')
                    ->label('Category Complaint'),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->required(),
                Forms\Components\FileUpload::make('attachment')
                    ->label('Attachment')
                    ->uploadingMessage('Uploading attachment...')
                    ->preserveFilenames()
                    ->disk('public')
                    ->directory('complaints')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/jpg',
                    ])
                    ->default(null),
                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->label('Assigned To')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\DateTimePicker::make('resolution_date'),
                Forms\Components\Textarea::make('resolution_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoryComplaint.name')
                    ->label('Category Complaint')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn ($state) => match ($state) {
                        'open' => 'success',
                        'in_progress' => 'warning',
                        'resolved' => 'primary',
                        'closed' => 'danger',
                    })
                    ->badge()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('attachment')
                    ->disk('public')
                    ->label('Attachment')
                    ->size(150)
                    ->searchable(),
                Tables\Columns\TextColumn::make('resolution_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
