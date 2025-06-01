<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessStream implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function handle()
    {
        // Path input (sesuaikan dengan struktur XAMPP)
        $inputPath = $this->getAbsolutePath($this->content->file_path);

        // Path output (di dalam storage Laravel)
        $outputDir = Storage::path("streams/{$this->content->id}");
        $playlistPath = "{$outputDir}/playlist.m3u8";

        // Buat direktori jika belum ada
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Konversi ke HLS
        $command = "ffmpeg -i {$inputPath} -codec: copy -start_number 0 -hls_time 10 -hls_list_size 0 -hls_base_url " . url('/storage/streams/' . $this->content->id . '/') . " -f hls {$playlistPath}";
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error("FFmpeg error for content {$this->content->id}", [
                'command' => $command,
                'output' => $output,
                'return_code' => $returnCode
            ]);
            throw new \Exception("Failed to process stream");
        }

        // Update stream URL di database
        $this->content->update([
            'stream_url' => url("api/streams/{$this->content->id}/playlist.m3u8")
        ]);
    }

    protected function getAbsolutePath($storagePath)
    {
        // Konversi path storage ke absolute path di sistem
        $relativePath = str_replace('storage/', '', $storagePath);
        return base_path("storage/app/public/{$relativePath}");
    }
}