<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStream;
use App\Jobs\StopStream;
use App\Models\Carriages;
use App\Models\Content;
use App\Models\UserVote;
use App\Models\Voting;
use App\Models\VotingOption; // Import VotingOption
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request; // Import Request jika belum ada
use Illuminate\Support\Facades\Log;

class StreamController extends Controller
{
    /**
     * Get currently playing content for a SPECIFIC carriage.
     *
     * @param \App\Models\Carriages $carriage
     * @return \Illuminate\Http\JsonResponse
     */
    public function nowPlaying(Carriages $carriage) // 1. Terima $carriage
    {
        $now = now();
        $carriageId = $carriage->id; // Ambil ID untuk digunakan di dalam closure

        // 2. Tambahkan 'whereHas' untuk filter berdasarkan carriage
        $content = Content::where('status', 'published')
            ->where('is_live', true)
            ->where('airing_time_start', '<=', $now)
            ->where('airing_time_end', '>=', $now)
            ->whereHas('carriages', function ($query) use ($carriageId) {
                $query->where('carriages.id', $carriageId);
            })
            ->first();

        if (!$content) {
            // Jika tidak ada konten yang live di carriage ini, cukup kembalikan pesan
            // bahwa tidak ada yang tayang. Konten selanjutnya akan ditentukan oleh voting.
            return response()->json([
                'message' => "No content is currently airing on '{$carriage->name}'.",
                'is_live' => false,
                'server_time' => $now->toIso8601String(),
                // Kembalikan 'next_content' sebagai null agar konsisten dengan struktur data
                // yang diharapkan oleh klien (Android).
                'next_content' => null
            ], 200); // 200 OK karena ini adalah status yang valid, bukan error.
        }

        $playbackPosition = $now->diffInSeconds($content->airing_time_start);

        return response()->json([
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'thumbnail' => url(Storage::url($content->thumbnail_path)),
                'duration_seconds' => $content->duration_seconds
            ],
            'stream_url' => url("/api/stream/{$content->id}/playlist"),
            'sync_data' => [
                'server_time' => $now->toIso8601String(),
                'playback_position' => $playbackPosition,
                'segment_duration' => 10
            ],
            'is_live' => true
        ]);
    }

    /**
     * Serve HLS playlist with correct URLs
     */
    public function playlist(Content $content)
    {
        // Pastikan file_path di content sudah berisi path yang benar ke playlist HLS
        // Misalnya: 'public/streams/{id}/playlist.m3u8'
        $playlistPath = "public/streams/{$content->id}/playlist.m3u8";
        $fullPath = storage_path("app/{$playlistPath}");

        if (!file_exists($fullPath)) {
            abort(404, "Stream playlist not found for content ID: {$content->id}. Path: {$fullPath}");
        }

        $playlistContent = file_get_contents($fullPath);

        // Replace relative paths with absolute URLs for .ts segments
        $modifiedContent = preg_replace_callback(
            '/^(.*\.ts)$/m',
            function ($matches) use ($content) {
                // Pastikan URL storage ini sesuai dengan konfigurasi Anda
                // Biasanya, ini akan mengarah ke direktori yang disymlink ke public
                return url("/storage/streams/{$content->id}/{$matches[1]}");
            },
            $playlistContent
        );

        return response($modifiedContent, 200)
            ->header('Content-Type', 'application/x-mpegURL')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate') // Improved cache control
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Start virtual live stream
     * This function should be called by an automated process (e.g., scheduler)
     * after a voting period ends or for a scheduled content.
     */
    public function startStream(Content $content)
    {
        // Validate content can be streamed
        // Assuming 'file_path' stores the path to the original video file
        if (!Storage::exists($content->file_path)) {
            return response()->json([
                'message' => 'Video file not found for processing',
                'errors' => ['file' => ['The video file does not exist at ' . $content->file_path]]
            ], 422);
        }

        // Set airing_time_start and airing_time_end based on content duration
        // Assuming Content model has a 'duration_seconds' field
        if (!$content->duration_seconds) {
            // You might want to calculate duration here or fail if not available
            return response()->json([
                'message' => 'Content duration is not set. Cannot start stream.',
                'errors' => ['duration' => ['Content duration_seconds is required']]
            ], 422);
        }

        $startTime = now();
        $endTime = $startTime->copy()->addSeconds($content->duration_seconds);

        Content::where('is_live', true)->update(['is_live' => false]);

        // Update content status to live
        $content->update([
            'is_live' => true,
            'stream_key' => Str::random(32), // Generate unique stream key (if still needed)
            'stream_url' => url("/api/stream/{$content->id}/playlist"), // Dynamic HLS playlist URL
            'airing_time_start' => $startTime,
            'airing_time_end' => $endTime
        ]);

        ProcessStream::dispatch($content);
        Log::info("Successfully started stream for '{$content->title}' (ID: {$content->id}).");

        return response()->json([
            'message' => 'Virtual live stream started',
            'stream_url' => $content->stream_url,
            'schedule' => [
                'start' => $startTime->toIso8601String(),
                'end' => $endTime->toIso8601String()
            ]
        ]);
    }

    /**
     * Stop the stream.
     * This should also be called by an automated process when a stream ends.
     */
    public function stopStream(Content $content)
    {
        // Only stop if it's currently live
        if (!$content->is_live) {
            return response()->json([
                'message' => 'Stream is not active or already stopped.',
            ], 400);
        }

        $content->update([
            'is_live' => false,
            // 'airing_time_end' can be updated here if not already set by 'startStream'
            // or if it was set for a longer duration than actual playback
        ]);

        // Dispatch a job to clean up resources if necessary
        // For example, if HLS segments are temporary.
        // StopStream::dispatch($content); // Removed for now, depends on implementation details

        return response()->json([
            'message' => 'Stream stopped successfully',
            'stopped_at' => now()->toIso8601String()
        ]);
    }

    /**
     * Get stream synchronization data
     * (This function might be redundant if nowPlaying provides enough info)
     */
    public function syncData(Content $content)
    {
        if (!$content->is_live || $content->airing_time_end < now()) {
            abort(404, 'Stream is not active or has ended.');
        }

        $startTime = Carbon::parse($content->airing_time_start);
        $currentPosition = now()->diffInSeconds($startTime);

        return response()->json([
            'content_id' => $content->id,
            'title' => $content->title,
            'server_time' => now()->toIso8601String(),
            'stream_start' => $startTime->toIso8601String(),
            'current_position' => $currentPosition,
            'segment_duration' => 10, // Should match HLS segment duration
            'stream_end' => $content->airing_time_end->toIso8601String(),
            'remaining_seconds' => $content->airing_time_end->diffInSeconds(now())
        ]);
    }

    /**
     * Manages the overall stream lifecycle.
     * This method should be run by a scheduler (e.g., every minute).
     */
    public function manageStreamTransitions()
    {
        $now = now();
        Log::info("Running manageStreamTransitions...");

        // Prioritas 1: Hentikan stream yang sudah selesai waktunya.
        $finishedStream = Content::where('is_live', true)
            ->where('airing_time_end', '<=', $now)
            ->first();

        if ($finishedStream) {
            Log::info("Stream '{$finishedStream->title}' has ended. Stopping it.");
            $this->stopStream($finishedStream);
            // Kita berhenti di sini, siklus selanjutnya (membuat vote) akan diurus oleh getVotingForCarriage
            // saat klien melakukan request.
            return;
        }

        // Prioritas 2: Cari voting yang sudah selesai waktunya TAPI masih aktif.
        $finishedVoting = Voting::where('is_active', true)
            ->where('end_time', '<=', $now)
            ->first();

        if ($finishedVoting) {
            Log::info("Voting '{$finishedVoting->title}' (ID: {$finishedVoting->id}) has ended. Determining winner and starting stream.");
            // Panggil fungsi eksekutor di VotingController
            $votingController = new \App\Http\Controllers\VotingController();
            $votingController->endVotingAndStartWinner($finishedVoting->id);
            return;
        }

        Log::info("No finished streams or votings to process at this time.");
    }

    /**
     * Get stream status for a specific carriage, with its voting info.
     * This is a client-facing endpoint to get the overall status.
     * It now fetches voting data by calling the VotingController's getVotingForCarriage method,
     * which will create a new poll for the specified carriage if the system is idle.
     *
     * @param Request $request
     * @param \App\Models\Carriages $carriage Injected via Route-Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStreamStatus(Request $request, Carriages $carriage)
    {
        $this->manageStreamTransitions();
        // 1. Dapatkan status stream global
        $nowPlayingResponse = $this->nowPlaying($carriage);
        $nowPlayingData = $nowPlayingResponse->getData();

        // 2. Dapatkan data voting & carriage dari VotingController
        $votingController = new VotingController();
        $votingResponse = $votingController->getVotingForCarriage($carriage->id);

        // Ambil semua data dari response (bukan hanya 'voting')
        $responseData = $votingResponse->getData(true);
        $activeVotingData = $responseData['voting'];
        $carriageData = $responseData['carriage']; // <-- Ambil data carriage

        // 3. Gabungkan semua data untuk response akhir
        $data = [
            'now_playing' => $nowPlayingData,
            'active_voting' => $activeVotingData,
            'carriage' => $carriageData, // <-- SERTAKAN carriage di sini
            'server_time' => now()->toIso8601String()
        ];

        return response()->json($data);
    }
}