<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['type'] = Storage::disk('public')->mimeType($data['file_path']);

    //     return $data;
    // }

}
