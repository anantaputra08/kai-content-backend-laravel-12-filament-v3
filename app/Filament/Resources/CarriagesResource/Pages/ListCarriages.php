<?php

namespace App\Filament\Resources\CarriagesResource\Pages;

use App\Filament\Resources\CarriagesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarriages extends ListRecords
{
    protected static string $resource = CarriagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
