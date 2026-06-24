<?php

namespace App\Jobs;

use App\SolicitudServicio;
use App\Http\Controllers\SolicitudServicioController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarCertificadosJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 600;  // 10 minutos para muchas solicitudes
    public $tries = 2;

    protected int $solicitudId;
    protected ?string $solserRecepcionDate;
    protected bool $enviarEmail;

    public function __construct(int $solicitudId, ?string $solserRecepcionDate = null, bool $enviarEmail = true)
    {
        $this->solicitudId = $solicitudId;
        $this->solserRecepcionDate = $solserRecepcionDate;
        $this->enviarEmail = $enviarEmail;
    }

    public function handle(): void
    {
        set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $Solicitud = SolicitudServicio::find($this->solicitudId);
        if (!$Solicitud) {
            Log::warning('GenerarCertificadosJob: Solicitud no encontrada', ['ID_SolSer' => $this->solicitudId]);
            return;
        }

        $controller = app(SolicitudServicioController::class);
        $controller->generarPdfsCertificadosRegulares($Solicitud, $this->solserRecepcionDate, $this->enviarEmail);

        Log::info('GenerarCertificadosJob: PDFs generados correctamente', ['ID_SolSer' => $this->solicitudId]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerarCertificadosJob falló', [
            'ID_SolSer' => $this->solicitudId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}