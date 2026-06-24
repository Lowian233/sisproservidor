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

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('contrato:mail')->daily();
        $schedule->command('notify:unprogrammed-services')->weekdays('06:00');
        $schedule->command('notify:vehicle-documents-expiring')->dailyAt('06:00');
        $schedule->command('signatures:generate-tomorrow')->dailyAt('18:00'); // Ejecutar a las 6 PM para preparar firmas de servicios programados para mañana
        $schedule->command('crm:enviar-recordatorios')->twiceDaily(8, 14); // Enviar recordatorios a las 8 AM y 2 PM
        // Procesar cola de generación de PDFs (certificados conciliados)
        $schedule->command('queue:work database --stop-when-empty --queue=default')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
