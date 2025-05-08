<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\LikeDislike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
     * Mengatur like/dislike dengan format request yang baru
     * 
     * @param Request $request
     * @param string $id ID konten (opsional, bisa dari request body)
     * @return JsonResponse
     */
    public function setReaction(Request $request, string $id = null)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'reaction_type' => 'required|in:like,dislike',
            'action' => 'required|boolean',
            'content_id' => 'required_without:id|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verifikasi token dan autentikasi user
        try {
            // Gunakan token untuk autentikasi
            // Contoh: Jika menggunakan token API atau sanctum/passport
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed'
            ], 401);
        }

        // Ambil content ID dari parameter URL atau request body
        $contentId = $id ?? $request->input('content_id');
        $content = Content::findOrFail($contentId);

        // Ambil jenis reaksi dan aksi
        $reactionType = $request->input('reaction_type'); // 'like' atau 'dislike'
        $action = $request->input('action'); // true = create, false = delete

        // Ambil record reaksi user ke konten ini
        $likeDislike = LikeDislike::firstOrNew([
            'user_id' => $user->id,
            'content_id' => $content->id,
        ]);

        $previous = null;
        if ($likeDislike->is_like !== null) {
            $previous = (bool) $likeDislike->is_like; // Konversi 1/0 ke true/false
        }
        $newValue = $reactionType === 'like' ? true : false;

        if ($action === false) {
            // Jika action false, hapus reaksi
            if ($previous === $newValue) {
                // Hapus reaksi yang sesuai dengan jenis reaksi
                $likeDislike->is_like = null;
                if ($newValue === true) {
                    $content->like = max(0, $content->like - 1);
                } else {
                    $content->dislike = max(0, $content->dislike - 1);
                }
            } else {
                // Jika mencoba menghapus reaksi yang tidak ada, abaikan
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No matching reaction to remove',
                    'data' => [
                        'like' => $content->like,
                        'dislike' => $content->dislike
                    ]
                ]);
            }
        } else {
            // Jika action true, buat atau ubah reaksi
            if ($previous === $newValue) {
                // Reaksi sudah ada dan sama, tidak perlu diubah
                return response()->json([
                    'status' => 'info',
                    'message' => 'Reaction already exists',
                    'data' => [
                        'like' => $content->like,
                        'dislike' => $content->dislike
                    ]
                ]);
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
        }

        $likeDislike->save();
        $content->save();

        // Prepare the is_like and is_dislike values for the response
        $responseIsLike = false;
        $responseIsDislike = false;

        if ($likeDislike->is_like !== null) {
            $responseIsLike = (bool) $likeDislike->is_like;
            $responseIsDislike = !$responseIsLike;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reaction updated successfully',
            'data' => [
                'like' => $content->like,
                'dislike' => $content->dislike,
                'is_like' => $responseIsLike,
                'is_dislike' => $responseIsDislike,
                'content_id' => $content->id,
                'user_id' => $user->id,
                'reaction_type' => $reactionType,
                'action' => $action
            ]
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
