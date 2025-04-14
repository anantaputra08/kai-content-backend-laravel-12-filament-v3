<?php

namespace App\Filament\Resources\CategoryComplaintResource\Pages;

use App\Filament\Resources\CategoryComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryComplaint extends EditRecord
{
    protected static string $resource = CategoryComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
