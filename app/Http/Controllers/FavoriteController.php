<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $favorites = Favorite::with(['content.categories'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($favorite) {
                if ($favorite->content && $favorite->content->thumbnail_path) {
                    $favorite->content->thumbnail_url = Storage::disk('public')->url($favorite->content->thumbnail_path);
                } else {
                    $favorite->content->thumbnail_url = null;
                }
                return $favorite;
            });

        return response()->json([
            'status' => 'success',
            'data' => $favorites,
        ]);
    }

    /**
     * Cek apakah konten sudah difavoritkan oleh user.
     */
    public function isFavorite(Request $request, string $id)
    {
        $user = $request->user();

        $exists = Favorite::where('user_id', $user->id)
            ->where('content_id', $id)
            ->exists();

        return response()->json([
            'status' => 'success',
            'is_favorite' => $exists,
        ]);
    }

    /**
     * Toggle status favorit: jika sudah, maka hapus. Jika belum, maka tambahkan.
     */
    public function toggleFavorite(Request $request, string $id)
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('content_id', $id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'status' => 'removed',
                'is_favorite' => false,
                'message' => 'Content removed from favorites.',
            ]);
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'content_id' => $id,
            ]);

            return response()->json([
                'status' => 'added',
                'is_favorite' => true,
                'message' => 'Content added to favorites.',
            ]);
        }
    }
}
