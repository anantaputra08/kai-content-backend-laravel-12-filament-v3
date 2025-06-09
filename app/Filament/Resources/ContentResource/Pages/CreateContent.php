<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use App\Jobs\ProcessStream;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;
    
    protected function afterCreate(): void
    {
        $content = $this->record;

        ProcessStream::dispatch($content);
    }
}
