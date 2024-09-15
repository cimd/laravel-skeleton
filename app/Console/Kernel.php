<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Konnec\Helpers\Actions\LoadModuleCommands;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Backup
        $schedule->command('backup:clean')->daily();
        $schedule->command('backup:run --only-db')->twiceDaily(1, 13);
        $schedule->command('backup:run --only-files')->weekly();

        if (App::environment('local')) {
            $schedule->command('telescope:prune --hours=3')->hourly();
        }

        // Clean Ups
        $schedule->command('model:prune')->daily();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('app:prune-tokens')->daily();
        $schedule->command('cache:prune-stale-tags')->hourly();

        $schedule->command('octane:reload')->daily()->at('05:00');
        $schedule->command('queue:restart')->daily()->at('05:00');
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->registerModulesCommands();

        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    private function registerModulesCommands(): void
    {
        $this->commands = array_merge($this->commands, LoadModuleCommands::handle());
    }
}
