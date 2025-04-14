<?php

namespace App\Filament\Resources\CategoryComplaintResource\Pages;

use App\Filament\Resources\CategoryComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryComplaints extends ListRecords
{
    protected static string $resource = CategoryComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
