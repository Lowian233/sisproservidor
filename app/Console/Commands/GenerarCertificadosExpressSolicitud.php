<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SolicitudServicio;
use App\CertificadoExpress;

/**
 * Genera los certificados/manifiestos PDF para una solicitud Express ya conciliada.
 * Usa exactamente la misma lógica y plantillas que certificarExpress.
 * Útil cuando la certificación hizo timeout y no alcanzó a generar todos los documentos.
 *
 * Uso: php artisan certificados-express:generar 12345
 *      php artisan certificados-express:generar {id-slug}
 *      php artisan certificados-express:generar 12345 --no-email  (solo genera PDFs, no envía correos)
 */
class GenerarCertificadosExpressSolicitud extends Command
{
    protected $signature = 'certificados-express:generar 
                            {solicitud : ID_SolSer o SolSerSlug de la solicitud express} 
                            {--no-email : No enviar correos de notificación}';

    protected $description = 'Genera certificados/manifiestos PDF para una solicitud express conciliada (para cuando hubo timeout)';

    public function handle()
    {
        $id = $this->argument('solicitud');
        $noEmail = $this->option('no-email');

        set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $Solicitud = SolicitudServicio::with('cliente')
            ->where('ID_SolSer', $id)
            ->orWhere('SolSerSlug', $id)
            ->first();

        if (!$Solicitud) {
            $this->error("No existe solicitud con ID_SolSer ni SolSerSlug = {$id}");
            return 1;
        }

        // Express se identifica por cliente prepago (CliCategoria), no por SolSerTipo.
        $categoriaCliente = $Solicitud->cliente->CliCategoria ?? null;
        if ($categoriaCliente !== 'ClientePrepago') {
            if ($categoriaCliente === 'Cliente') {
                $this->error("La solicitud #{$Solicitud->ID_SolSer} es de cliente regular. Use: php artisan certificados:generar {$id}");
            } else {
                $this->error("La solicitud #{$Solicitud->ID_SolSer} no pertenece a un cliente Express (CliCategoria = '{$categoriaCliente}').");
            }
            return 1;
        }

        if (!in_array($Solicitud->SolSerStatus, ['Conciliado', 'Certificacion'])) {
            $this->error("La solicitud #{$Solicitud->ID_SolSer} tiene status '{$Solicitud->SolSerStatus}'. Debe estar en Conciliado o Certificacion.");
            return 1;
        }

        $controller = app(\App\Http\Controllers\ServiceExpressController::class);

        $certificadosCount = CertificadoExpress::where('FK_CertSolser', $Solicitud->ID_SolSer)->count();
        if ($certificadosCount === 0) {
            $this->info("Solicitud #{$Solicitud->ID_SolSer} - creando registros de certificados express...");
            $controller->solservdocstoreExpress($Solicitud->ID_SolSer);
        }

        $certificadosCount = CertificadoExpress::where('FK_CertSolser', $Solicitud->ID_SolSer)->count();
        if ($certificadosCount === 0) {
            $this->warn("No se encontraron certificados express para la solicitud #{$Solicitud->ID_SolSer}. Verifique que tenga residuos conciliados.");
            return 0;
        }

        $this->info("Solicitud Express #{$Solicitud->ID_SolSer} - generando {$certificadosCount} documento(s) PDF con la plantilla express...");

        $controller->generarPdfsCertificadosExpress($Solicitud, null, !$noEmail);

        $this->info("\nListo. Se generaron los documentos express correctamente.");
        return 0;
    }
}
