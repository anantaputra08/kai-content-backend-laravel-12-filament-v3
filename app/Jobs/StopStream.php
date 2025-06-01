<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StopStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function handle()
    {
        // Clean up stream files if needed
        $streamDir = storage_path("app/streams/{$this->content->id}");
        
        if (file_exists($streamDir)) {
            // Delete all .ts files but keep the directory
            array_map('unlink', glob("$streamDir/*.ts"));
        }
        
        // You might want to keep the playlist file for replay
    }
}