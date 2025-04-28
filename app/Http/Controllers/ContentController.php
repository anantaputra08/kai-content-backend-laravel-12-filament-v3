<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            if ($content->file_path) {
                $content->file_url = asset('storage/' . $content->file_path);
            }
        });

        return response()->json($contents);
    }

    /**
     * Search contents by title.
     * GET /api/contents/search
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $title = $request->input('title');
        $contents = Content::with('categories')
            ->where('title', 'LIKE', '%' . $title . '%')
            ->get();

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
            'categories' => 'nullable|json',
            'file' => 'required|file|mimes:mp4,avi,mov,webm',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Simpan file ke public disk
        $filePath = $request->file('file')->store('contents', 'public');

        // Cek apakah ada thumbnail, jika ada simpan
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        // Buat content
        $content = Content::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'status' => $request->status,
            'view_count' => 0,
            'total_watch_time' => 0,
            'rank' => 0,
        ]);

        // Handle categories jika ada
        if ($request->has('categories')) {
            $categoriesJson = $request->input('categories');
            try {
                $categoryIds = json_decode($categoriesJson, true);
                if (is_array($categoryIds)) {
                    // Sync categories
                    $content->categories()->sync($categoryIds);
                    Log::info('Categories added', ['categories' => $categoryIds]);
                } else {
                    Log::warning('Invalid categories format', ['categories' => $categoriesJson]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing categories', [
                    'error' => $e->getMessage(),
                    'categories_input' => $categoriesJson
                ]);
            }
        }

        // Add thumbnail URL if it exists
        if ($content->thumbnail_path) {
            $content->thumbnail_url = asset('storage/' . $content->thumbnail_path);
        }

        return response()->json($content->load('categories'), 201);
    }


    /**
     * Display detailed content data with relationships and additional computed properties.
     * GET /api/contents/details
     */
    public function getContentDetails(Request $request, string $id)
    {
        $content = Content::with('categories')->findOrFail($id);

        // Add thumbnail URL if it exists
        if ($content->thumbnail_path) {
            $content->thumbnail_url = asset('storage/' . $content->thumbnail_path);
        }

        // Add computed properties or additional logic if needed
        $content->view_count = $content->view_count ?? 0; // Default to 0 if null

        return response()->json([
            'message' => 'Content details retrieved successfully.',
            'data' => $content->load('categories'),
        ]);
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
    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/contents/{id}
     */
    public function update(Request $request, string $id)
    {
        $content = Content::findOrFail($id);
        // Log all incoming data for debugging
        Log::info('Content update request', [
            'id' => $id,
            'all_input' => $request->all(),
            'has_title' => $request->has('title'),
            'title_value' => $request->input('title'),
            'method' => $request->method(),
            'categories' => $request->input('categories'),
            'headers' => $request->headers->all()
        ]);

        try {
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|string',
                'categories' => 'nullable|json',
                'file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-flv,video/webm',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }

        // Prepare data for update
        $updateData = [];

        // Force check the input directly
        $title = $request->input('title');
        $description = $request->input('description');
        $status = $request->input('status');

        if (!empty($title)) {
            $updateData['title'] = $title;
            Log::info("Setting title to: $title");
        }

        if (!empty($description)) {
            $updateData['description'] = $description;
            Log::info("Setting description to: $description");
        }

        if (!empty($status)) {
            $updateData['status'] = $status;
            Log::info("Setting status to: $status");
        }

        // Log update data before applying it
        Log::info('Update data to be applied', $updateData);

        // Handle file upload if present
        if ($request->hasFile('file')) {
            // Delete old file
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }
            // Save new file
            $filePath = $request->file('file')->store('contents', 'public');
            $updateData['file_path'] = $filePath;
        }

        // Handle thumbnail upload if present
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($content->thumbnail_path && Storage::disk('public')->exists($content->thumbnail_path)) {
                Storage::disk('public')->delete($content->thumbnail_path);
            }
            // Save new thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $updateData['thumbnail_path'] = $thumbnailPath;
        }

        // Update basic content data
        Log::info('BEFORE UPDATE: Content title = ' . $content->title);
        $content->update($updateData);
        $content->refresh();
        Log::info('AFTER UPDATE: Content title = ' . $content->title);

        // Handle categories if present
        if ($request->has('categories')) {
            $categoriesJson = $request->input('categories');
            try {
                $categoryIds = json_decode($categoriesJson, true);
                if (is_array($categoryIds)) {
                    // Sync categories (this will remove existing associations and add new ones)
                    $content->categories()->sync($categoryIds);
                    Log::info('Categories updated', ['categories' => $categoryIds]);
                } else {
                    Log::warning('Invalid categories format', ['categories' => $categoriesJson]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing categories', [
                    'error' => $e->getMessage(),
                    'categories_input' => $categoriesJson
                ]);
            }
        }

        // Refresh content with updated categories
        $content->load('categories');

        // Add thumbnail URL if it exists
        if ($content->thumbnail_path) {
            $content->thumbnail_url = asset('storage/' . $content->thumbnail_path);
        }

        return response()->json([
            'message' => 'Content updated successfully.',
            'data' => $content,
        ]);
    }

    /**
     * Get all categories
     * GET /api/categories
     */
    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Get categories for a specific content
     * GET /api/contents/{id}/categories
     */
    public function getContentCategories(string $id)
    {
        $content = Content::findOrFail($id);
        $categories = $content->categories;

        return response()->json([
            'status' => 'success',
            'message' => 'Content categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Update categories for a specific content
     * POST /api/contents/{id}/categories
     */
    public function updateContentCategories(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $content = Content::findOrFail($id);

        // Sync categories
        $content->categories()->sync($request->category_ids);

        // Return the updated content with categories
        return response()->json([
            'status' => 'success',
            'message' => 'Content categories updated successfully',
            'data' => $content->load('categories')
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
