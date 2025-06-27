<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StreamResource\Pages;
use App\Filament\Resources\StreamResource\RelationManagers;
use App\Models\Stream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StreamResource extends Resource
{
    protected static ?string $model = Stream::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('content_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('train_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('carriage_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('start_airing_time'),
                Forms\Components\DateTimePicker::make('end_airing_time'),
                Forms\Components\Toggle::make('processed_after_finish'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content.title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('train.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carriage.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_airing_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_airing_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('processed_after_finish')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_airing_time', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListStreams::route('/'),
            'create' => Pages\CreateStream::route('/create'),
            'edit' => Pages\EditStream::route('/{record}/edit'),
        ];
    }
}
