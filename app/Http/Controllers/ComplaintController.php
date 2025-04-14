<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $complaints = Complaint::with(['user', 'categoryComplaint', 'assignedTo'])
            ->withTrashed() // Sertakan data yang soft deleted
            ->get();

        return response()->json($complaints);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_complaint_id' => 'nullable|exists:category_complaints,id',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_date' => 'nullable|date',
            'resolution_notes' => 'nullable|string',
        ]);

        // Tambahkan user_id dari user yang sedang login
        $validated['user_id'] = auth()->id();

        // Set status default ke 'open'
        $validated['status'] = 'open';

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->storeAs('complaints', $file->getClientOriginalName(), 'public');
            $validated['attachment'] = $path;
        }

        $complaint = Complaint::create($validated);

        // Tambahkan URL attachment jika ada
        if ($complaint->attachment) {
            $complaint->attachment_url = asset('storage/' . $complaint->attachment);
        }

        return response()->json($complaint->load(['user', 'categoryComplaint', 'assignedTo']), 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $complaint = Complaint::with(['user', 'categoryComplaint', 'assignedTo'])
            ->withTrashed()
            ->findOrFail($id);

        if ($complaint->attachment) {
            $complaint->attachment_url = asset('storage/' . $complaint->attachment);
        }

        return response()->json($complaint);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $complaint = Complaint::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'category_complaint_id' => 'nullable|exists:category_complaints,id',
            'description' => 'nullable|string', // Perbaiki typo: 'nulable' menjadi 'nullable'
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg|max:2048', // Max 2MB, nullable agar opsional
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_date' => 'nullable|date',
            'resolution_notes' => 'nullable|string',
            'status' => 'nullable|in:open,in_progress,resolved,closed', // Validasi status
        ]);

        // Tambahkan user_id dari user yang sedang login (jika diperlukan)
        // $validated['user_id'] = auth()->id();

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Hapus file lama jika ada
            if ($complaint->attachment) {
                Storage::disk('public')->delete($complaint->attachment);
            }

            $file = $request->file('attachment');
            $path = $file->storeAs('complaints', $file->getClientOriginalName(), 'public');
            $validated['attachment'] = $path;
        }

        $complaint->update($validated);

        // Tambahkan URL attachment jika ada
        if ($complaint->attachment) {
            $complaint->attachment_url = asset('storage/' . $complaint->attachment);
        }

        return response()->json($complaint->load(['user', 'categoryComplaint', 'assignedTo']));
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        // Hapus file terkait jika ada
        if ($complaint->attachment) {
            Storage::disk('public')->delete($complaint->attachment);
        }
        
        return response()->json(['message' => 'Complaint deleted successfully.'], 200);
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore(string $id)
    {
        $complaint = Complaint::withTrashed()->findOrFail($id);
        $complaint->restore();
        return response()->json($complaint->load(['user', 'categoryComplaint', 'assignedTo']));
    }

    /**
     * Force delete a resource (permanently).
     */
    public function forceDelete(string $id)
    {
        $complaint = Complaint::withTrashed()->findOrFail($id);

        // Hapus file terkait jika ada
        if ($complaint->attachment) {
            Storage::disk('public')->delete($complaint->attachment);
        }

        $complaint->forceDelete();
        return response()->json(null, 204);
    }
}