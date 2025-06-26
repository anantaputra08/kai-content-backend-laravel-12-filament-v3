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
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('content_id'); // ID konten yang di-stream
            $table->string('train_id'); // ID kereta api yang melakukan streaming
            $table->string('carriage_id'); // ID gerbong kereta api
            $table->timestamp('start_airing_time')->nullable(); // Waktu streaming dimulai
            $table->timestamp('end_airing_time')->nullable(); // Waktu streaming berakhir
            $table->boolean('processed_after_finish')->nullable(); // Menandakan apakah streaming telah diproses setelah selesai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
