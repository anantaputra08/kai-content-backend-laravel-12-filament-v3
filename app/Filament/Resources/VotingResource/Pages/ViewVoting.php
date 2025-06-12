<?php

namespace App\Filament\Resources\VotingResource\Pages;

use App\Filament\Resources\VotingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVoting extends ViewRecord
{
    protected static string $resource = VotingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
