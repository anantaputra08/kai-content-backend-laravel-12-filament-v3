<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carriages;

class CarriageSeeder extends Seeder
{
    public function run(): void
    {
        Carriages::insert([
            ['name' => 'Gerbong 1'],
            ['name' => 'Gerbong 2'],
            ['name' => 'Gerbong 3'],
            ['name' => 'Gerbong 4'],
            ['name' => 'Gerbong 5'],
            ['name' => 'Gerbong 6'],
            ['name' => 'Gerbong 7'],
            ['name' => 'Gerbong 8'],
            ['name' => 'Gerbong A'],
            ['name' => 'Gerbong B'],
            ['name' => 'Gerbong C'],
        ]);
    }
}
