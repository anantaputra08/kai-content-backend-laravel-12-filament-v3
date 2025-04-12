<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Horror',
            'Comedy',
            'Drama',
            'Action',
            'Romance',
            'Sci-Fi',
            'Documentary',
            'Thriller',
            'Fantasy',
            'Adventure',
            'Animation',
            'Mystery',
            'Biography',
            'Crime',
            'Family',
            'History',
            'Musical',
            'Sport',
            'War',
            'Western',
            'Short',
            'Noir',
            'Superhero',
            'Martial Arts',
            'Historical Fiction',
            'Cyberpunk',
            'Dystopian',
            'Post-Apocalyptic',
            'Steampunk',
            'Slice of Life',
            'Parody',
            'Satire',
        ];

        foreach ($categories as $name) {
            Category::create([
                'name' => $name,
            ]);
        }

    }
}
