<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('categories')
                    ->label('Category')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Video File')
                    ->uploadingMessage('Uploading attachment...')
                    ->required()
                    ->preserveFilenames()
                    ->disk('public')
                    ->directory('contents')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'video/mp4',
                        'video/quicktime',
                        'video/x-msvideo',
                        'video/x-flv',
                        'video/webm',
                    ])
                    ->maxSize(1024 * 1024)
                    ->imagePreviewHeight('250')
                    ->panelAspectRatio('16:9')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->loadingIndicatorPosition('left')
                    ->uploadProgressIndicatorPosition('left')
                    ->enableOpen()
                    ->enableDownload()
                    ->enableReordering()
                    ->default(null),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'published' => 'Published',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                // Forms\Components\TextInput::make('view_count')
                //     ->numeric()
                //     ->default(0),
                // Forms\Components\TextInput::make('total_watch_time')
                //     ->numeric()
                //     ->default(0),
                Forms\Components\TextInput::make('rank')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories_list')
                    ->label('Kategori')
                    ->getStateUsing(fn($record) => $record->categories->pluck('name')->join(', '))
                    ->wrap(),
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Video File'),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'published' => 'success',
                        'rejected' => 'danger',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('view_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_watch_time')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank')
                    ->numeric()
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
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
