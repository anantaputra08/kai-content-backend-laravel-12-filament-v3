<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VotingResource\Pages;
use App\Models\Voting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class VotingResource extends Resource
{
    protected static ?string $model = Voting::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Voting Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('train_id')
                            ->label('Train')
                            ->relationship('train', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('carriages_id')
                            ->label('Carriage')
                            ->relationship('carriage', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('start_time')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('end_time'),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Voting Options')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('content_id')
                                    ->relationship('content', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Content'),
                            ])
                            ->columns(1)
                            ->addActionLabel('Add Content to Vote'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                
                // Kolom gabungan untuk menampilkan lokasi (Kereta atau Gerbong)
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->getStateUsing(function (Voting $record) {
                        if ($record->train) {
                            return 'Train: ' . $record->train->name;
                        }
                        if ($record->carriage) {
                            return 'Carriage: ' . $record->carriage->name;
                        }
                        return 'N/A';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('train', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                     ->orWhereHas('carriage', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(function (Voting $record): bool {
                        if (!$record->is_active) return false;
                        if ($record->end_time && Carbon::parse($record->end_time)->isPast()) return false;
                        return true;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('total_votes')
                    ->label('Total Votes')
                    ->getStateUsing(function (Voting $record) {
                        return $record->options()->sum('vote_count');
                    })
                    ->numeric()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withSum('options as total_votes_sum', 'vote_count')
                            ->orderBy('total_votes_sum', $direction);
                    }),
                
                Tables\Columns\TextColumn::make('top_content')
                    ->label('Top Content (Votes)')
                    ->getStateUsing(function (Voting $record) {
                        $topOption = $record->options()
                                            ->orderBy('vote_count', 'desc')
                                            ->first();

                        if (!$topOption || $topOption->vote_count === 0) {
                            return 'Belum ada suara';
                        }
                        
                        $contentTitle = $topOption->content->title ?? 'N/A';
                        $voteCount = $topOption->vote_count;

                        return "{$contentTitle} ({$voteCount})";
                    })
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Total Options'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListVotings::route('/'),
            'create' => Pages\CreateVoting::route('/create'),
            'edit' => Pages\EditVoting::route('/{record}/edit'),
            'view' => Pages\ViewVoting::route('/{record}'),
        ];
    }
}
