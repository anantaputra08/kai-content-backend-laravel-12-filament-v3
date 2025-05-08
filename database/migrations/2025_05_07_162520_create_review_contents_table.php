<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('review_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign Key ke tabel user
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade'); // Foreign Key ke tabel content
            $table->integer('rating')->nullable(); // Kolom rating, nullable
            $table->text('review')->nullable(); // Kolom review, nullable
            $table->timestamps();
            $table->softDeletes(); // Kolom deleted_at untuk soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_contents');
    }
};
