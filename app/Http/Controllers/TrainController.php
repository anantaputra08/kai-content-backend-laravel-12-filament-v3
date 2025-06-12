<?php

namespace App\Http\Controllers;

use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrainController extends Controller
{
    /**
     * Menampilkan daftar semua kereta yang tersedia.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Ambil semua data kereta dari database
            $trains = Train::select('id', 'name', 'route')->get();

            // Kembalikan response JSON yang terstruktur
            return response()->json([
                'status' => 'success',
                'message' => 'Trains retrieved successfully',
                'data' => $trains
            ]);

        } catch (\Exception $e) {
            // Catat error jika terjadi masalah
            Log::error("Failed to retrieve trains: " . $e->getMessage());

            // Kembalikan response error
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve trains. Please try again later.'
            ], 500);
        }
    }
}
