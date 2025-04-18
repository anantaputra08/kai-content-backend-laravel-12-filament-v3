<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/contents
     */
    public function index()
    {
        $contents = Content::with('categories')->get();

        $contents->each(function ($content) {
        if ($content->thumbnail_path) {
            $content->thumbnail_url = asset('storage/' . $content->thumbnail_path);
        }
        });

        return response()->json($contents);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/contents
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'view_count' => 'nullable|integer',
            'total_watch_time' => 'nullable|integer',
            'rank' => 'nullable|integer',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'file' => 'required|file|mimes:mp4,avi,mov,webm', // max 50MB
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Simpan file ke public disk
        $filePath = $request->file('file')->store('contents', 'public');
        $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');

        // Buat content
        $content = Content::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'status' => $request->status,
            'view_count' => $request->view_count,
            'total_watch_time' => $request->total_watch_time,
            'rank' => $request->rank,
        ]);

        // Tambahkan kategori
        if ($request->has('category_ids')) {
            $content->categories()->attach($request->category_ids);
        }

        return response()->json($content->load('categories'), 201);
    }


    /**
     * Display the specified resource.
     * GET /api/contents/{id}
     */
    public function show(Request $request, string $id)
    {
        $content = Content::findOrFail($id);

        // Increment view count
        $content->increment('view_count');

        $path = $content->file_path;
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        $fullPath = $disk->path($path);
        $mime = mime_content_type($fullPath);

        $size = filesize($fullPath);
        $start = 0;
        $end = $size - 1;

        // Cek apakah client minta sebagian (partial request)
        if ($request->headers->has('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches);

            $start = intval($matches[1]);
            if (isset($matches[2]) && is_numeric($matches[2])) {
                $end = intval($matches[2]);
            }
        }

        $length = $end - $start + 1;

        $response = new StreamedResponse(function () use ($fullPath, $start, $length) {
            $file = fopen($fullPath, 'rb');
            fseek($file, $start);
            echo fread($file, $length);
            fclose($file);
        });

        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Accept-Ranges', 'bytes');

        if ($request->headers->has('Range')) {
            $response->setStatusCode(206); // Partial content
            $response->headers->set('Content-Range', "bytes $start-$end/$size");
        } else {
            $response->setStatusCode(200);
        }

        return $response;
    }
    // public function show(string $id)
    // {
    //     $content = Content::with('categories')->findOrFail($id);
    //     // Increment view count
    //     $content->increment('view_count');

    //     return response()->json($content);
    // }

    /**
     * Update the specified resource in storage.
     * PUT /api/contents/{id}
     */
    public function update(Request $request, string $id)
    {
        $content = Content::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:published,draft',
            'file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-flv,video/webm', // max 100MB
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        // Jika ada file baru
        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }

            // Simpan file baru
            $filePath = $request->file('file')->store('contents', 'public');
            $content->file_path = $filePath;
        }

        // Jika ada thumbnail baru
        if ($request->hasFile('thumbnail')) {
            // Hapus thumbnail lama
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }

            // Simpan thumbnail baru
            $filePath = $request->file('thumbnail')->store('thumbnails', 'public');
            $content->file_path = $filePath;
        }

        // Update data lain
        $content->update($request->except('file'));

        return response()->json([
            'message' => 'Content updated successfully.',
            'data' => $content,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/contents/{id}
     */
    public function destroy(string $id)
    {
        $content = Content::findOrFail($id);

        // Hapus file dari storage
        if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
            Storage::disk('public')->delete($content->file_path);
        }

        // Hapus data dari database
        $content->delete();

        return response()->json([
            'message' => 'Content deleted successfully.',
        ]);
    }

}
