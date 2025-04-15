<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat kategori
        $category1 = Category::create(['name' => 'Tutorial']);
        $category2 = Category::create(['name' => 'Review']);
        $category3 = Category::create(['name' => 'Entertainment']);

        // 2. Buat konten
        $content1 = Content::create([
            'title' => 'Cara Membuat Website dengan Laravel',
            'description' => 'Panduan lengkap membuat website Laravel.',
            'file_path' => 'videos/laravel_tutorial.mp4',
            'type' => 'video/mp4',
            'status' => 'published',
            'view_count' => 100,
            'total_watch_time' => 3600,
            'rank' => 5,
            'like' => 80,
            'dislike' => 2,
        ]);

        $content2 = Content::create([
            'title' => 'Unboxing iPhone 15 Pro Max',
            'description' => 'Review dan kesan pertama.',
            'file_path' => 'videos/iphone_review.mp4',
            'type' => 'video/mp4',
            'status' => 'published',
            'view_count' => 250,
            'total_watch_time' => 7200,
            'rank' => 4,
            'like' => 200,
            'dislike' => 5,
        ]);

        $content3 = Content::create([
            'title' => 'Standup Comedy - Open Mic',
            'description' => 'Penampilan komedi lucu.',
            'file_path' => 'videos/comedy_show.mp4',
            'type' => 'video/mp4',
            'status' => 'published',
            'view_count' => 400,
            'total_watch_time' => 10800,
            'rank' => 3,
            'like' => 300,
            'dislike' => 10,
        ]);

        // 3. Relasikan konten dengan kategori
        $content1->categories()->attach([$category1->id]); // Tutorial
        $content2->categories()->attach([$category2->id]); // Review
        $content3->categories()->attach([$category3->id]); // Entertainment

        // Tambahan: konten bisa punya lebih dari 1 kategori
        $content1->categories()->attach($category2->id); // Tutorial + Review
        $content2->categories()->attach($category1->id); // Review + Tutorial
    }
}
