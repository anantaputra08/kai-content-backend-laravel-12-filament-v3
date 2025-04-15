<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // Tampilkan semua favorit user
    public function index()
    {
        $favorites = Favorite::with('content', 'user')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($favorites, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content_id' => 'required|exists:contents,id',
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => Auth::id(),
            'content_id' => $request->content_id,
        ]);

        // Load relasi user dan content
        $favorite->load(['user', 'content']);

        return response()->json([
            'message' => $favorite->wasRecentlyCreated
                ? 'Content added to favorites.'
                : 'Content is already in favorites.',
            'status' => $favorite->wasRecentlyCreated ? 'created' : 'exists',
            'data' => $favorite
        ], 201);
    }

    // Hapus dari favorit
    public function destroy($content_id)
    {
        $deleted = Favorite::where('user_id', Auth::id())
            ->where('content_id', $content_id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Content removed from favorites.']);
        }

        return response()->json(['message' => 'Favorite not found.'], 404);
    }
}
