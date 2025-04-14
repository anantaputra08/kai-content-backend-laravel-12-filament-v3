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
        Schema::create('category_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Kolom nama kategori, harus unik
            $table->text('description')->nullable(); // Kolom deskripsi kategori, nullable
            $table->timestamps();
            $table->softDeletes(); // Kolom deleted_at untuk soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_complaints');
    }
};
