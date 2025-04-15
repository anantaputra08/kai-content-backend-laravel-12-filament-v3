<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('category_complaints')->insert([
            ['name' => 'Pelayanan', 'description' => 'Keluhan terkait pelayanan staf atau sistem.'],
            ['name' => 'Kebersihan', 'description' => 'Masalah kebersihan lingkungan atau fasilitas.'],
            ['name' => 'Teknologi', 'description' => 'Gangguan atau keluhan terhadap perangkat IT.'],
            ['name' => 'Sarana dan Prasarana', 'description' => 'Kerusakan atau kekurangan fasilitas umum.'],
            ['name' => 'Keamanan', 'description' => 'Keluhan seputar keamanan dan ketertiban.'],
            ['name' => 'Lainnya', 'description' => 'Keluhan yang tidak termasuk dalam kategori di atas.'],
        ]);        
    }
}
