<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    // Menampilkan semua feedback
    public function index()
    {
        $feedbacks = Feedback::with('user')->get();
        return response()->json($feedbacks);
    }

    // Menyimpan feedback baru
    // Menyimpan feedback baru atau update jika sudah ada
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'nullable|numeric|between:0.5,5',
            'review' => 'nullable|string',
        ]);

        // Cek apakah user sudah memiliki feedback
        $existingFeedback = Feedback::where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->first();

        if ($existingFeedback) {
            // Update feedback yang sudah ada
            $existingFeedback->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'message' => 'Feedback updated successfully',
                'feedback' => $existingFeedback
            ]);
        } else {
            // Buat feedback baru
            $feedback = Feedback::create([
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            return response()->json([
                'message' => 'Feedback created successfully',
                'feedback' => $feedback
            ], 201);
        }
    }

    // Menampilkan detail feedback
    public function show($id)
    {
        $feedback = Feedback::with('user')->findOrFail($id);
        return response()->json($feedback);
    }

    // Mengupdate feedback
    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);

        $request->validate([
            'rating' => 'nullable|integer|between:1,5',
            'review' => 'nullable|string',
        ]);

        // Pastikan hanya user yang membuat feedback yang bisa mengupdate
        if ($feedback->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback->update([
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json(['message' => 'Feedback updated successfully', 'feedback' => $feedback]);
    }

    // Menghapus feedback (soft delete)
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);

        // Pastikan hanya user yang membuat feedback yang bisa menghapus
        if ($feedback->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully']);
    }

    /**
     * Cek apakah user sudah memberikan feedback
     * GET /api/feedbacks/check
     */
    public function checkUserFeedback()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah user sudah memberikan feedback
        $feedback = Feedback::where('user_id', $user->id)
            ->whereNull('deleted_at') // Pastikan feedback belum di-soft delete
            ->first();

        if ($feedback) {
            return response()->json([
                'has_feedback' => true,
                'feedback' => $feedback,
            ]);
        }

        return response()->json([
            'has_feedback' => false,
            'message' => 'User has not submitted any feedback yet.',
        ]);
    }
}