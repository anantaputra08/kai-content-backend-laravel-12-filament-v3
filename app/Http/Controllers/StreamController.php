<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStream;
use App\Jobs\StopStream;
use App\Models\Content;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StreamController extends Controller
{
    /**
     * Get currently playing content with synchronization data
     */
    public function nowPlaying()
    {
        $now = now();
        $currentTime = $now->format('H:i:s');

        // Get content that should be airing now
        $content = Content::where('status', 'published')
            ->where('is_live', true)
            ->whereTime('airing_time_start', '<=', $currentTime)
            ->whereTime('airing_time_end', '>=', $currentTime)
            ->orderBy('airing_time_start', 'asc')
            ->first();

        if (!$content) {
            return response()->json([
                'message' => 'No content is currently airing',
                'is_live' => false,
                'server_time' => $now->toIso8601String()
            ], 404);
        }

        // Calculate playback position (in seconds)
        $startTime = Carbon::parse($content->airing_time_start);
        $playbackPosition = $now->diffInSeconds($startTime, true); // signed

        return response()->json([
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'thumbnail' => url(Storage::url($content->thumbnail_path))
            ],
            'stream_url' => url("/api/stream/{$content->id}/playlist"),
            'sync_data' => [
                'server_time' => $now->toIso8601String(),
                'playback_position' => $playbackPosition,
                'segment_duration' => 10 // seconds per HLS segment
            ],
            'is_live' => true
        ]);
    }

    public function testNowPlaying()
    {
        // Panggil function nowPlaying() secara internal (atau ulangi logic-nya)
        $now = now();
        $currentTime = $now->format('H:i:s');

        $content = Content::where('status', 'published')
            ->where('is_live', true)
            ->whereTime('airing_time_start', '<=', $currentTime)
            ->whereTime('airing_time_end', '>=', $currentTime)
            ->orderBy('airing_time_start', 'asc')
            ->first();

        if (!$content) {
            return response("<h2>No content is currently airing</h2><p>Server time: {$now->toIso8601String()}</p>", 404);
        }

        $startTime = Carbon::parse($content->airing_time_start);
        $playbackPosition = $now->diffInSeconds($startTime, true); // signed

        // Buat tampilan HTML sederhana
        $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test Now Playing</title>
        <meta charset="utf-8" />
        <style>
            body { font-family: sans-serif; margin: 2em; }
            .info { margin-bottom: 1em; }
            .label { font-weight: bold; }
            code { background: #f9f9f9; padding: 2px 5px; border-radius: 3px; }
        </style>
        <script>
        function updateClientTime() {
            document.getElementById('clientTime').innerText = new Date().toISOString();
        }
        setInterval(updateClientTime, 500);
        window.onload = updateClientTime;
        </script>
    </head>
    <body>
        <h2>Test Now Playing</h2>
        <div class="info"><span class="label">Content:</span> {$content->title}</div>
        <div class="info"><span class="label">Thumbnail:</span> <img src="{$content->thumbnail_path}" style="max-width:150px;"></div>
        <div class="info"><span class="label">Stream URL:</span> <a href="{$content->stream_url}" target="_blank">{$content->stream_url}</a></div>
        <div class="info"><span class="label">Server time:</span> <code>{$now->toIso8601String()}</code></div>
        <div class="info"><span class="label">Client time:</span> <code id="clientTime"></code></div>
        <div class="info"><span class="label">Airing Start:</span> <code>{$startTime->toIso8601String()}</code></div>
        <div class="info"><span class="label">Playback Position:</span> <code>{$playbackPosition} detik</code></div>
        <div class="info"><span class="label">HLS Segment Duration:</span> <code>10 detik</code></div>
        <div class="info">
            <a href="{$content->stream_url}" target="_blank">Buka playlist.m3u8 di tab baru</a>
        </div>
        <div class="info">
            <a href="https://hls-js.netlify.app/demo/?src={$content->stream_url}" target="_blank">Test di HLS.js Demo Player</a>
        </div>
    </body>
    </html>
    HTML;

        return response($html);
    }

    /**
     * Serve HLS playlist with correct URLs
     */
    public function playlist(Content $content)
    {
        $playlistPath = "public/streams/{$content->id}/playlist.m3u8";
        $fullPath = storage_path("app/{$playlistPath}");

        if (!file_exists($fullPath)) {
            abort(404, "Stream playlist not found");
        }

        $playlistContent = file_get_contents($fullPath);

        // Replace relative paths with absolute URLs
        $modifiedContent = preg_replace_callback(
            '/^(.*\.ts)$/m',
            function ($matches) use ($content) {
                return url("/storage/streams/{$content->id}/{$matches[1]}");
            },
            $playlistContent
        );

        return response($modifiedContent, 200)
            ->header('Content-Type', 'application/x-mpegURL')
            ->header('Cache-Control', 'no-cache');
    }

    /**
     * Start virtual live stream
     */
    public function startStream(Content $content)
    {
        // Validate content can be streamed
        if (!Storage::exists($content->file_path)) {
            return response()->json([
                'message' => 'Video file not found',
                'errors' => ['file' => ['The video file does not exist']]
            ], 422);
        }

        // Generate unique stream key
        $streamKey = Str::random(32);
        $startTime = now();
        $endTime = $startTime->copy()->addHours(2);

        // Update content
        $content->update([
            'is_live' => true,
            'stream_key' => $streamKey,
            'stream_url' => url("/api/stream/{$content->id}/playlist"),
            'airing_time_start' => $startTime,
            'airing_time_end' => $endTime
        ]);

        // Process video in background
        ProcessStream::dispatch($content);

        return response()->json([
            'message' => 'Virtual live stream started',
            'stream_key' => $streamKey,
            'stream_url' => $content->stream_url,
            'schedule' => [
                'start' => $startTime->toIso8601String(),
                'end' => $endTime->toIso8601String()
            ]
        ]);
    }

    /**
     * Stop the stream
     */
    public function stopStream(Content $content)
    {
        $content->update([
            'is_live' => false,
            'airing_time_end' => now()
        ]);

        StopStream::dispatch($content);

        return response()->json([
            'message' => 'Stream stopped successfully',
            'stopped_at' => now()->toIso8601String()
        ]);
    }

    /**
     * Get stream synchronization data
     */
    public function syncData(Content $content)
    {
        if (!$content->is_live) {
            abort(404, 'Stream is not active');
        }

        $startTime = Carbon::parse($content->airing_time_start);
        $currentPosition = now()->diffInSeconds($startTime);

        return response()->json([
            'content_id' => $content->id,
            'server_time' => now()->toIso8601String(),
            'stream_start' => $startTime->toIso8601String(),
            'current_position' => $currentPosition,
            'segment_duration' => 10 // Should match HLS segment duration
        ]);
    }
}