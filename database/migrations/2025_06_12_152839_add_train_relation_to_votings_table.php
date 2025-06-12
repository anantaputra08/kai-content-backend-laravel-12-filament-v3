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
        Schema::table('votings', function (Blueprint $table) {
            // 1. Tambahkan kolom train_id yang bisa null, berelasi ke tabel 'trains'
            $table->foreignId('train_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            // 2. Ubah kolom carriages_id yang sudah ada menjadi nullable
            $table->foreignId('carriages_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votings', function (Blueprint $table) {
            // Hapus relasi dan kolom train_id
            $table->dropForeign(['train_id']);
            $table->dropColumn('train_id');
            
            // Kembalikan kolom carriages_id menjadi not-nullable
            $table->foreignId('carriages_id')->nullable(false)->change();
        });
    }
};
