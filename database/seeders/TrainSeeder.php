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
        Train::create(['name' => 'Argo Lawu', 'route' => 'Gambir - Solo Balapan', 'departure_time' => '18:00:00', 'arrival_time' => '21:30:00']);
        Train::create(['name' => 'Taksaka', 'route' => 'Gambir - Yogyakarta', 'departure_time' => '20:00:00', 'arrival_time' => '23:50:00']);
    }
}
