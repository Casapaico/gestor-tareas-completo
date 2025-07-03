<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecutar notificaciones de WhatsApp cada minuto
        $schedule->command('tareas:whatsapp-notify')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/scheduler.log'));

        // Limpiar logs del scheduler cada día (opcional)
        $schedule->call(function () {
            $logPath = storage_path('logs/scheduler.log');
            if (file_exists($logPath) && filesize($logPath) > 10 * 1024 * 1024) { // 10MB
                file_put_contents($logPath, '');
            }
        })->daily()->at('02:00');
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
