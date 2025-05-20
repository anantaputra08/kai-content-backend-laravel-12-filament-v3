<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil kategori berdasarkan nama
        $category1 = Category::where('name', 'Documentary')->first();
        $category2 = Category::where('name', 'Sci-Fi')->first();
        $category3 = Category::where('name', 'Comedy')->first();

        // Buat konten
        $content1 = Content::create([
            'title' => 'Cara Membuat Website dengan Laravel',
            'description' => 'Panduan lengkap membuat website Laravel.',
            'file_path' => 'contents/cobaini.mp4',
            'thumbnail_path' => 'thumbnails/Screenshot 2025-04-15 at 20.34.54.png',
            'type' => 'video/mp4',
            'status' => 'published',
            'airing_start' => now()->addHours(1),
            'airing_end' => now()->addHours(2),
            'view_count' => 100,
            'total_watch_time' => 3600,
            'rank' => 5,
            'like' => 80,
            'dislike' => 2,
        ]);

        $content2 = Content::create([
            'title' => 'Unboxing iPhone 15 Pro Max',
            'description' => 'Review dan kesan pertama.',
            'file_path' => 'contents/cFVN5jE0XWqfWcJxX1tFrN6mTJIODcLG5ZbV0TU3.mp4',
            'thumbnail_path' => 'thumbnails/dkPUFh1FnFBdwODHtqGsmmS7bjuFM7lbHF6TXRCj.jpg',
            'type' => 'video/mp4',
            'status' => 'published',
            'airing_start' => now(),
            'airing_end' => now()->addHours(1),
            'view_count' => 250,
            'total_watch_time' => 7200,
            'rank' => 4,
            'like' => 200,
            'dislike' => 5,
        ]);

        $content3 = Content::create([
            'title' => 'Standup Comedy - Open Mic',
            'description' => 'Penampilan komedi lucu.',
            'file_path' => 'contents/cFVN5jE0XWqfWcJxX1tFrN6mTJIODcLG5ZbV0TU3.mp4',
            'thumbnail_path' => 'thumbnails/cKlXQx18lWH9gB0LR07yfnSwDQo8b22JXbTiuYOD.jpg',
            'type' => 'video/mp4',
            'status' => 'published',
            'airing_start' => now()->addHours(1),
            'airing_end' => now()->addDays(1),
            'view_count' => 400,
            'total_watch_time' => 10800,
            'rank' => 3,
            'like' => 300,
            'dislike' => 10,
        ]);

        // Relasikan konten dengan kategori
        if ($category1 && $category2 && $category3) {
            $content1->categories()->attach([$category1->id, $category2->id]);
            $content2->categories()->attach([$category2->id, $category3->id]);
            $content3->categories()->attach([$category3->id]);
        }
    }
}
