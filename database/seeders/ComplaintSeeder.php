<?php

namespace Database\Seeders;

use App\Models\Complaint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        Complaint::create([
            'user_id' => 1,
            'category_complaint_id' => 1,
            'description' => 'AC ruangan tidak berfungsi.',
            'status' => 'open',
            'attachment' => null,
            'resolution_date' => null,
            'resolution_notes' => null,
            'assigned_to' => null,
        ]);

        Complaint::create([
            'user_id' => 2,
            'category_complaint_id' => 2,
            'description' => 'Toilet lantai 2 kotor.',
            'status' => 'in_progress',
            'attachment' => 'complaints/toilet_kotor.jpg',
            'resolution_date' => Carbon::now()->addDays(3),
            'resolution_notes' => 'Sudah dijadwalkan pembersihan.',
            'assigned_to' => 3,
        ]);

        Complaint::create([
            'user_id' => 1,
            'category_complaint_id' => 3,
            'description' => 'Website tidak bisa diakses.',
            'status' => 'resolved',
            'attachment' => null,
            'resolution_date' => Carbon::now(),
            'resolution_notes' => 'Website sudah diperbaiki.',
            'assigned_to' => 2,
        ]);
    }
}
