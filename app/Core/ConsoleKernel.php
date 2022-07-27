<?php

namespace Core;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;

/**
 * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
 */
class ConsoleKernel extends FoundationConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<class-string<\Illuminate\Console\Command>>
     */
    protected $commands = [];

    /**
     * Register all of the commands in the given directory.
     *
     * @param  array<class-string>|array<\Closure>|string  $paths
     *
     * @return void
     */
    public function loadCommandsFromPaths($paths): void
    {
        $this->load($paths);
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
