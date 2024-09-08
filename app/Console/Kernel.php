<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

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
        //      Normalize search path between windows and linux platforms
        (PHP_OS === 'WINNT') ? $searchPath = 'modules\*\Commands\*.php' : $searchPath = 'modules/*/Commands/*.php';

        $modulesCommands = collect(
            glob(base_path($searchPath))
        )->map(function ($item) {
            (PHP_OS === 'WINNT') ? $withoutPrefix = Str::after($item, base_path() . '\\modules\\') : $withoutPrefix = Str::after($item, base_path() . '/modules/');
            $withoutSuffix = Str::beforeLast($withoutPrefix, '.php');

            $partial = 'Modules' . DIRECTORY_SEPARATOR . $withoutSuffix;

            return str_replace('/', '\\', $partial);
        })->toArray();

        $this->commands = array_merge($this->commands, $modulesCommands);
    }
}
