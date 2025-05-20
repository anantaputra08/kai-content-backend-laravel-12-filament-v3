<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carriages;

class CarriageSeeder extends Seeder
{
    public function run(): void
    {
        Carriages::insert([
            ['name' => 'Gerbong A'],
            ['name' => 'Gerbong B'],
            ['name' => 'Gerbong C'],
        ]);
    }
}
