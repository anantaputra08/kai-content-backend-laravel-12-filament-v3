<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('thumbnail_path'); // untuk menyimpan thumbnail
            $table->string('type')->nullable(); // untuk menyimpan mime type
            $table->enum('status', ['pending', 'published', 'rejected'])->default('pending'); // bisa disesuaikan
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('total_watch_time')->default(0); // dalam detik misalnya
            $table->integer('rank')->default(0);
            $table->unsignedBigInteger('like')->default(0);
            $table->unsignedBigInteger('dislike')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
