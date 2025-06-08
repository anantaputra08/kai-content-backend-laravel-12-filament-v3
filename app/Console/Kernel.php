<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ManageStreamLifecycle::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Menjalankan satu perintah utama yang mengatur seluruh siklus hidup stream.
        // Perintah ini akan memanggil logic di StreamController secara berurutan.
        $schedule->command('stream:manage')
            ->everyMinute()
            ->withoutOverlapping(); // Mencegah perintah berjalan jika instance sebelumnya belum selesai.

        $schedule->command('votings:prune')->daily();
        $schedule->command('app:check-stream-transitions')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
