<?php
namespace App\Http\Controllers;
use App\Models\ReviewContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewContentController extends Controller
{
    public function checkUserReview(Request $request, string $content_id)
    {
        // // Validasi input
        // $request->validate([
        //     'content_id' => 'required|integer|exists:contents,id', // Pastikan content_id valid
        // ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah user sudah memberikan feedback
        $reviewContent = ReviewContent::where('user_id', $user->id)
            ->where('content_id', $content_id) // Pastikan content_id sesuai
            ->whereNull('deleted_at') // Pastikan feedback belum di-soft delete
            ->first();

        if ($reviewContent) {
            return response()->json([
                'has_review' => true,
                'review' => $reviewContent,
            ]);
        }

        return response()->json([
            'has_review' => false,
            'message' => 'User has not submitted any review yet.',
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input yang diperlukan termasuk content_id
        $request->validate([
            'content_id' => 'required|integer|exists:contents,id',
            'rating' => 'nullable|numeric|between:0.5,5',
            'review' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah user sudah memiliki feedback untuk content ini
        $existingReview = ReviewContent::where('user_id', $user->id)
            ->where('content_id', $request->content_id)
            ->whereNull('deleted_at')
            ->first();

        if ($existingReview) {
            // Update feedback yang sudah ada
            $existingReview->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'message' => 'Review updated successfully',
                'review' => $existingReview
            ]);
        } else {
            // Buat feedback baru
            $review = ReviewContent::create([
                'user_id' => $user->id,
                'content_id' => $request->content_id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'message' => 'Feedback created successfully',
                'feedback' => $review
            ], 201);
        }
    }
}