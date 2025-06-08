<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneExpiredVotings extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'votings:prune';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Delete expired voting polls and their related data from the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to prune expired voting data...');

        // Tentukan batas waktu, misalnya voting yang berakhir lebih dari 1 hari yang lalu
        $cutOffDate = now()->subDay();

        // Ambil ID dari semua voting yang sudah kedaluwarsa
        $expiredVotingIds = Voting::where('end_time', '<', $cutOffDate)->pluck('id');

        if ($expiredVotingIds->isEmpty()) {
            $this->info('No expired votings to prune. All clean!');
            return 0;
        }

        $count = $expiredVotingIds->count();
        $this->info("Found {$count} expired voting polls to delete.");

        try {
            // Gunakan transaction untuk memastikan semua data terhapus atau tidak sama sekali
            DB::transaction(function () use ($expiredVotingIds) {
                // 1. Hapus semua data vote dari pengguna (user_votes)
                DB::table('user_votes')->whereIn('voting_id', $expiredVotingIds)->delete();

                // 2. Hapus semua opsi voting (voting_options)
                DB::table('voting_options')->whereIn('voting_id', $expiredVotingIds)->delete();

                // 3. Hapus data voting utamanya (votings)
                DB::table('votings')->whereIn('id', $expiredVotingIds)->delete();
            });

            $logMessage = "Successfully pruned {$count} expired voting polls.";
            Log::info($logMessage);
            $this->info($logMessage);

        } catch (\Exception $e) {
            $errorMessage = "Failed to prune expired votings: " . $e->getMessage();
            Log::error($errorMessage);
            $this->error($errorMessage);
            return 1; // Return error status
        }

        return 0; // Return success status
    }
}