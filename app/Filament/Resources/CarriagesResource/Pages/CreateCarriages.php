<?php

namespace App\Filament\Resources\CarriagesResource\Pages;

use App\Filament\Resources\CarriagesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCarriages extends CreateRecord
{
    protected static string $resource = CarriagesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
