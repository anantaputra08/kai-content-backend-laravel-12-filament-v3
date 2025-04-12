<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/contents
     */
    public function index()
    {
        $contents = Content::with('categories')->get();
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Simpan file ke public disk
        $filePath = $request->file('file')->store('contents', 'public');

        // Buat content
        $content = Content::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
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
    public function show(string $id)
    {
        $content = Content::with('categories')->findOrFail($id);
        // Increment view count
        $content->increment('view_count');

        return response()->json($content);
    }

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
