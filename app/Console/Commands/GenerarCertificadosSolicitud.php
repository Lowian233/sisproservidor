<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SolicitudServicio;
use App\Certificado;

/**
 * Genera los certificados/manifiestos PDF para una solicitud ya conciliada.
 * Usa exactamente la misma lógica y plantillas que el controlador (changestatus).
 * Útil cuando el changestatus hizo timeout y no alcanzó a generar todos los documentos.
 *
 * Uso: php artisan certificados:generar 12345
 *      php artisan certificados:generar {id-slug}
 *      php artisan certificados:generar 12345 --no-email  (solo genera PDFs, no envía correos)
 */
class GenerarCertificadosSolicitud extends Command
{
    protected $signature = 'certificados:generar 
                            {solicitud : ID_SolSer o SolSerSlug de la solicitud} 
                            {--no-email : No enviar correos de notificación}';

    protected $description = 'Genera certificados/manifiestos PDF para una solicitud conciliada (para cuando hubo timeout)';

    public function handle()
    {
        $id = $this->argument('solicitud');
        $noEmail = $this->option('no-email');

        set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $Solicitud = SolicitudServicio::where('ID_SolSer', $id)
            ->orWhere('SolSerSlug', $id)
            ->first();

        if (!$Solicitud) {
            $this->error("No existe solicitud con ID_SolSer ni SolSerSlug = {$id}");
            return 1;
        }

        if (!in_array($Solicitud->SolSerStatus, ['Conciliado', 'Certificacion'])) {
            $this->error("La solicitud #{$Solicitud->ID_SolSer} tiene status '{$Solicitud->SolSerStatus}'. Debe estar en Conciliado o Certificacion.");
            return 1;
        }

        $certificadosCount = Certificado::where('FK_CertSolser', $Solicitud->ID_SolSer)->count();
        if ($certificadosCount === 0) {
            $this->info("Solicitud #{$Solicitud->ID_SolSer} - creando registros de certificados...");
            $controller = app(\App\Http\Controllers\SolicitudServicioController::class);
            $controller->solservdocstore($Solicitud->ID_SolSer);
        }

        $certificadosCount = Certificado::where('FK_CertSolser', $Solicitud->ID_SolSer)->count();
        if ($certificadosCount === 0) {
            $this->warn("No se encontraron certificados para la solicitud #{$Solicitud->ID_SolSer}. Verifique que tenga residuos conciliados.");
            return 0;
        }

        $this->info("Solicitud #{$Solicitud->ID_SolSer} - generando {$certificadosCount} documento(s) PDF con la plantilla del controlador...");

        $controller = app(\App\Http\Controllers\SolicitudServicioController::class);
        $controller->generarPdfsCertificadosRegulares($Solicitud, null, !$noEmail);

        $this->info("\nListo. Se generaron los documentos correctamente.");
        return 0;
    }
}
