<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\WebhookClient\Models\WebhookCall;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('temporary:clean')->daily();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('sanctum:prune-expired --hours=24')->daily();
        $schedule->command('model:prune', [
            '--model' => [WebhookCall::class],
        ])->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
