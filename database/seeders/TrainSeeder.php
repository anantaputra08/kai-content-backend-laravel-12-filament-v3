<?php

namespace Database\Seeders;

use App\Models\Train;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Train::create(['name' => 'Argo Bromo Anggrek', 'route' => 'Gambir - Surabaya Pasarturi']);
        Train::create(['name' => 'Argo Lawu', 'route' => 'Gambir - Solo Balapan']);
        Train::create(['name' => 'Taksaka', 'route' => 'Gambir - Yogyakarta']);
    }
}
