<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\SolicitudServicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\UnprogrammedServiceAlert;
class NotifyUnprogrammedServices extends Command
{
    protected $signature = 'notify:unprogrammed-services';
    protected $description = 'Notifica los servicios creados hace más de tres días y sin programar.';
    public function handle()
    {
        // Fecha límite: hace 3 días
        $dateThreshold = Carbon::now()->subDays(3);
        
        // Se consultan los servicios creados antes de la fecha límite
        $services = SolicitudServicio::where('created_at', '<=', $dateThreshold)
            ->get();
        foreach($services as $service) {
            // Verifica si el servicio no tiene programaciones registradas
            if(!$service->programacionesrecibidas()->exists()){
                // Envía la notificación (puedes ajustar destinatarios y lógica)
                Mail::to('logistica@prosarc.com.co')->send(new UnprogrammedServiceAlert($service));
                $this->info("Notificación enviada para el servicio ID: {$service->ID_SolSer}");
            }
        }
        return 0;
    }
}