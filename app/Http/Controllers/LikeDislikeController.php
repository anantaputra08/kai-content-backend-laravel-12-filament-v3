<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\LikeDislike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeDislikeController extends Controller
{
    /*
     * Get all likes/dislikes for a specific content
     * @param int $contentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($contentId)
    {
        $user = Auth::user();

        $status = LikeDislike::where('user_id', $user->id)
            ->where('content_id', $contentId)
            ->first();

        if ($status->is_like === 1) {
            $status->is_like = true;
            $dislike = false;
        } elseif ($status->is_like === 0) {
            $status->is_like = false;
            $dislike = true;
        }

        return response()->json([
            'status' => 'success',
            'data' => $status ? [
                'id' => $status->id,
                'user_id' => $status->user_id,
                'content_id' => $status->content_id,
                'is_like' => $status->is_like,
                'is_dislike' => $dislike,
            ] : null
        ]);
    }

    /**
     * Mengatur like/dislike
     */
    public function setReaction(Request $request, string $id)
    {
        $user = Auth::user();
        $content = Content::findOrFail($id);
        $reaction = $request->input('reaction'); // 'like' atau 'dislike'

        // Ambil record reaksi user ke konten ini
        $likeDislike = LikeDislike::firstOrNew([
            'user_id' => $user->id,
            'content_id' => $content->id,
        ]);

        $previous = $likeDislike->is_like; // bisa true, false, atau null
        $newValue = $reaction === 'like' ? true : false;

        if ($previous === $newValue) {
            // Toggle off (hapus reaksi)
            $likeDislike->is_like = null;

            if ($newValue === true) {
                $content->like = max(0, $content->like - 1);
            } else {
                $content->dislike = max(0, $content->dislike - 1);
            }
        } else {
            // Set atau ubah reaksi
            $likeDislike->is_like = $newValue;

            if ($previous !== null) {
                // Ubah reaksi
                if ($previous === true) {
                    $content->like = max(0, $content->like - 1);
                    $content->dislike += 1;
                } else {
                    $content->dislike = max(0, $content->dislike - 1);
                    $content->like += 1;
                }
            } else {
                // Reaksi baru
                if ($newValue === true) {
                    $content->like += 1;
                } else {
                    $content->dislike += 1;
                }
            }
        }

        $likeDislike->save();
        $content->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Reaction updated successfully',
            'data' => [
                'like' => $content->like,
                'dislike' => $content->dislike
            ]
        ]);
    }


    // ➕ Add or update like/dislike
    public function react(Request $request)
    {
        $request->validate([
            'content_id' => 'required|exists:contents,id',
            'is_like' => 'required|boolean',
        ]);

        $user = Auth::user();

        $reaction = LikeDislike::updateOrCreate(
            [
                'user_id' => $user->id,
                'content_id' => $request->content_id
            ],
            [
                'is_like' => $request->is_like
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => $request->is_like ? 'Liked successfully' : 'Disliked successfully',
            'data' => $reaction
        ]);
    }

    /*
     * Remove like/dislike
     * @param int $contentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove($contentId)
    {
        $user = Auth::user();

        LikeDislike::where('user_id', $user->id)
            ->where('content_id', $contentId)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reaction removed'
        ]);
    }
}
