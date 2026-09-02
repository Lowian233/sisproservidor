<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\SolicitudServicio;
use App\ProgramacionVehiculo;
use App\Cliente;

class EnviarRecordatoriosWatiCommand extends Command
{
    protected $signature = 'wati:enviar-recordatorios';
    protected $description = 'Envía recordatorios diarios por WATI a los servicios programados para el día siguiente';

    public function handle()
    {
        // 1. Obtener servicios programados para mañana
        $servicios = $this->obtenerServiciosProgramados();

        Log::info('Servicios programados para mañana:', $servicios->toArray());

        if ($servicios->isEmpty()) {
            $this->info('No hay servicios programados para mañana.');
            return;
        }

        // 2. Recorrer y consumir la API usando el cliente HTTP de Laravel
        $tenantId = '10102814'; // O la variable de tu config/env
        $endpointBase = "https://live-mt-server.wati.io/{$tenantId}/api/v1/sendTemplateMessage";

        foreach ($servicios as $servicio) {
            // Concatenar el Query Parameter directamente en la URL
            $urlConQuery = $endpointBase . '?whatsappNumber=' . $servicio['telefono'];

            $response = Http::withToken(config('services.wati.token'))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($urlConQuery, [
                    'template_name'  => 'confirmaion_servicio_v1',
                    'broadcast_name' => 'Recordatorio_' . Carbon::now()->format('Y_m_d'),
                    'parameters'     => [
                        ['name' => '1', 'value' => (string) $servicio['nombre']],
                        ['name' => '2', 'value' => (string) $servicio['fecha']],
                    ]
                ]);

            Log::info('Respuesta WATI', [
                'cliente'  => $servicio['nombre'],
                'telefono' => $servicio['telefono'],
                'status'   => $response->status(),
                'body'     => $response->json() ?? $response->body(),
            ]);
        }

        $this->info('Recordatorios enviados correctamente.');
    }

    /**
     * Función reutilizable tanto para el Command como para un Endpoint en Controlador
     */
    public function obtenerServiciosProgramados()
    {
        log::info('Obteniendo servicios programados para mañana...');
        $fechaCarbon = Carbon::tomorrow();
        $fechaManana = $fechaCarbon->format('Y-m-d');
        $fechaFormateadaMensaje = $fechaCarbon->format('d/m/Y');

        /* $fechaManana = '2026-08-29';
        $fechaFormateadaMensaje = '29/08/2026'; */

        // 1. Consultar programaciones para mañana asociando el servicio y su fecha
        $programacion = ProgramacionVehiculo::whereDate('ProgVehFecha', $fechaManana)
            ->get(['FK_ProgServi', 'ProgVehFecha']);

        if ($programacion->isEmpty()) {
            return collect();
        }

        $fechasPorServicio = $programacion->pluck('ProgVehFecha', 'FK_ProgServi');

        // 2. Obtener IDs de clientes express
        $clientesExpressIds = Cliente::where('CliCategoria', 'ClientePrepago')
            ->where('CliDelete', 0)
            ->where('CliStatus', 'Autorizado')
            ->where('CliActivo', 1)
            ->pluck('ID_Cli')
            ->toArray();

        // 3. Consulta de Solicitudes
        $servicios = SolicitudServicio::with([
                'cliente' => function ($query) {
                    $query->select('ID_Cli', 'CliName');
                },
                'cliente.sedes.Areas.Cargos.Personal',
                'clienteExpress' => function ($query) {
                    $query->select('id', 'nombreEmpresa', 'numeroEmpresa');
                }
            ])
            ->whereIn('ID_SolSer', $fechasPorServicio->keys())
            ->where(function ($query) use ($clientesExpressIds) {
                $query->whereIn('FK_SolSerCliente', $clientesExpressIds)
                    ->orWhereNotNull('FK_Cliente_Express');
            })
            ->get();

        // 4. Transformación de datos
        return $servicios->map(function ($servicio) use ($fechasPorServicio, $fechaFormateadaMensaje) {
            if (!empty($servicio->FK_Cliente_Express) && $servicio->clienteExpress) {
                $nombre   = $servicio->clienteExpress->nombreEmpresa ?? 'N/A';
                $telefono = $servicio->clienteExpress->numeroEmpresa ?? '';
            } else {
                $cliente = $servicio->cliente;
                $nombre  = $cliente->CliName ?? 'N/A';

                $personalCollection = $cliente ? $cliente->PersonalCliente() : collect();
                $primerPersonal     = $personalCollection->first();

                $telefono = $primerPersonal->PersTelefono ?? '';
            }

            // Sanitizar y validar teléfono
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($telefonoLimpio) === 10) {
                $telefonoFormateado = '57' . $telefonoLimpio;
            } elseif (strlen($telefonoLimpio) === 12 && str_starts_with($telefonoLimpio, '57')) {
                $telefonoFormateado = $telefonoLimpio;
            } else {
                $telefonoFormateado = 'N/A';
            }

            // Formatear la fecha del servicio
            $fechaRaw = $fechasPorServicio[$servicio->ID_SolSer] ?? null;
            $fechaServicio = $fechaRaw ? Carbon::parse($fechaRaw)->format('d/m/Y') : $fechaFormateadaMensaje;

            return [
                'nombre'   => $nombre,
                'telefono' => $telefonoFormateado,
                'fecha'    => $fechaServicio,
            ];
        })->filter(function ($item) {
            return $item['telefono'] !== 'N/A';
        })->values();
    }
}
