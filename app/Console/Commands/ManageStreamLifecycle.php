<?php

namespace App\Console\Commands;

use App\Http\Controllers\StreamController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageStreamLifecycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manages the automated stream and voting lifecycle.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Stream manager command is running...');
        
        // Instantiate the controller to use its methods
        $streamController = new StreamController();
        
        // Call the main transition logic function
        $streamController->manageStreamTransitions();
        
        Log::info('Stream manager command finished.');
        return 0;
    }
}
