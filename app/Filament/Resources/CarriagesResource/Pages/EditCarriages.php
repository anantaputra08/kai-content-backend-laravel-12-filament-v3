<?php

namespace App\Filament\Resources\CarriagesResource\Pages;

use App\Filament\Resources\CarriagesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarriages extends EditRecord
{
    protected static string $resource = CarriagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
