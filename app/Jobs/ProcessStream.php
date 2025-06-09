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
        if (!Storage::disk('public')->exists($this->content->file_path)) {
            Log::error("Input file not found for content {$this->content->id}: " . $this->content->file_path);
            $this->fail(new \Exception("Input file not found."));
            return;
        }
        $inputPath = Storage::disk('public')->path($this->content->file_path);

        $outputDirRelative = "streams/{$this->content->id}";
        Storage::disk('public')->makeDirectory($outputDirRelative);
        $playlistPath = Storage::disk('public')->path("{$outputDirRelative}/playlist.m3u8");

        // Perintah FFMPEG
        // - "-c:v libx264 -c:a aac" : Melakukan re-encoding ke format standar HLS.
        // - "-hls_playlist_type vod" : Menandakan ini adalah Video-on-Demand.
        // - "-hls_segment_filename" : Pola penamaan file .ts yang standar.
        // - Kita HAPUS -hls_base_url agar URL ditangani oleh Laravel.
        // - Kita tambahkan quote (") di sekitar path untuk menangani spasi di nama file.
        $command = sprintf(
            'ffmpeg -i "%s" -c:v libx264 -c:a aac -hls_time 10 -hls_playlist_type vod -hls_segment_filename "%s/segment%%03d.ts" -start_number 0 "%s"',
            $inputPath,
            Storage::disk('public')->path($outputDirRelative),
            $playlistPath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error("FFmpeg error for content {$this->content->id}", [
                'command' => $command,
                'output' => implode("\n", $output),
                'return_code' => $returnCode
            ]);
            
            $this->fail(new \Exception("FFmpeg failed with return code: " . $returnCode));
            return;
        }

        Log::info("Successfully processed HLS for content {$this->content->id}");
    }
}