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
        Schema::create('user_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_id')->constrained()->onDelete('cascade');
            $table->foreignId('voting_option_id')->constrained()->onDelete('cascade');
            $table->string('user_identifier'); // bisa IP address atau user ID jika ada auth
            $table->timestamps();
            
            $table->unique(['voting_id', 'user_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_votes');
    }
};
