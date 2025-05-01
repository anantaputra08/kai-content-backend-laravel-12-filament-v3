<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Favorite;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    /**
     * Mengatur like/dislike
     */
    public function setReaction(Request $request, string $id)
    {
        $content = Content::findOrFail($id);
        $reaction = $request->input('reaction'); // 'like' atau 'dislike'
        $value = $request->input('value', true); // true untuk menambah, false untuk menghapus

        if ($reaction === 'like') {
            $content->like = $value ? $content->like + 1 : max(0, $content->like - 1);
        } else if ($reaction === 'dislike') {
            $content->dislike = $value ? $content->dislike + 1 : max(0, $content->dislike - 1);
        }

        $content->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Reaction recorded successfully',
            'data' => [
                'like' => $content->like,
                'dislike' => $content->dislike
            ]
        ]);
    }
}
