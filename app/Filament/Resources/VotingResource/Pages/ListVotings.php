<?php

namespace App\Filament\Resources\VotingResource\Pages;

use App\Filament\Resources\VotingResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVotings extends ListRecords
{
    protected static string $resource = VotingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => ListRecords\Tab::make(),
            'active' => ListRecords\Tab::make('Active Votings')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->where('end_time', '>', Carbon::now())
                            ->orWhereNull('end_time');
                    })),
            'expired' => ListRecords\Tab::make('Expired Votings')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->where('is_active', false)
                    ->orWhere('end_time', '<=', Carbon::now())),
        ];
    }
    public function getDefaultActiveTab(): ?string
    {
        return 'active'; // ID tab yang ingin dijadikan default
    }
}
