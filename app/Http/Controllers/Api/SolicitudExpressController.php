<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\SolicitudExpress;
use App\SedeExpress;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CreateSolicitudExpressService;

class SolicitudExpressController extends Controller
{
    /**
     * Endpoint unificado de solicitudes express.
     * - INSERT cuando llega `tipoResiduo`.
     * - UPDATE (idCliente, localidad, estado, RequiereContrato, peso/precio) en caso contrario.
     */
    public function solicitud(Request $request)
    {
        try {
            log::info('Esta es la información que esta llegando: ', $request->all());
            $idSolicitud  = $request->input('idSolicitud');
            $tipoResiduo  = $request->input('tipoResiduo');

            // INSERT: llega tipoResiduo
            if ($tipoResiduo) {
                if (!$idSolicitud || !is_numeric($idSolicitud) || $idSolicitud == '@idSolicitud') {
                    $maxId       = DB::table('solicitudes_express')->max('idSolicitud');
                    $idSolicitud = $maxId ? $maxId + 1 : 1;
                } else {
                    $idSolicitud = (int) $idSolicitud;
                }

                $datos = ['idSolicitud' => $idSolicitud, 'tipoResiduo' => $tipoResiduo];

                if ($request->has('idCliente'))        $datos['idCliente']        = $request->input('idCliente');
                if ($request->filled('idSede'))        $this->aplicarSede($request->input('idSede'), $datos);
                if ($request->has('localidad'))        $datos['localidad']        = $request->input('localidad');
                if ($request->has('estado'))           $datos['estado']           = $request->input('estado');
                if ($request->has('RequiereContrato')) $datos['RequiereContrato'] = $request->input('RequiereContrato');
                if ($request->has('peso')) {
                    $parsed = $this->parsearPesoYPrecio($request->input('peso'));
                    $datos['peso']   = $parsed['peso'];
                    $datos['precio'] = $parsed['precio'];
                }

                $solicitud = SolicitudExpress::create($datos);

                return response()->json([
                    'success'     => true,
                    'message'     => 'Solicitud creada exitosamente',
                    'idSolicitud' => $idSolicitud,
                    'data'        => $solicitud,
                ], 201);
            }

            // UPDATE: no llega tipoResiduo, actualiza todas las filas del idSolicitud
            if (!$idSolicitud || !is_numeric($idSolicitud)) {
                return response()->json([
                    'success' => false,
                    'message' => 'idSolicitud válido es requerido para actualizar',
                ], 422);
            }

            $idSolicitud = (int) $idSolicitud;
            $datos       = [];

            if ($request->has('idCliente'))        $datos['idCliente']        = $request->input('idCliente');
            if ($request->filled('idSede'))        $this->aplicarSede($request->input('idSede'), $datos);
            if ($request->has('localidad'))        $datos['localidad']        = $request->input('localidad');
            if ($request->has('estado'))           $datos['estado']           = $request->input('estado');
            if ($request->has('RequiereContrato')) $datos['RequiereContrato'] = $request->input('RequiereContrato');
            if ($request->has('peso')) {
                $parsed = $this->parsearPesoYPrecio($request->input('peso'));
                $datos['peso']   = $parsed['peso'];
                $datos['precio'] = $parsed['precio'];
            }

            if (empty($datos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibió ningún campo para actualizar',
                ], 422);
            }

            $afectados = DB::table('solicitudes_express')
                ->where('idSolicitud', $idSolicitud)
                ->update($datos);

            if ($afectados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron registros para ese idSolicitud',
                ], 404);
            }

            if (isset($datos['estado']) && $datos['estado'] === 'Pagado') {
                try {
                    log::info('Creando SolicitudServicio para idSolicitud API: ' . $idSolicitud);
                    $service = new CreateSolicitudExpressService();
                    $solicitudServicio = $service->createSolicitud($datos, $idSolicitud);
                    return response()->json([
                        'success'     => true,
                        'message'     => 'Solicitud programada correctamente',
                        'idSolicitud' => $idSolicitud,
                        'fecha'        => $solicitudServicio->ProgVehFecha ?? null,
                    ], 200);
                } catch (\Exception $e) {
                    Log::error('Error al crear SolicitudServicio: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error interno al procesar la solicitud: ' . $e->getMessage(),
                    ], 500);
                }
            }

            return response()->json([
                'success'     => true,
                'message'     => 'Solicitud actualizada correctamente',
                'idSolicitud' => $idSolicitud,
                'data'        => $datos,
            ], 200);

        } catch (Exception $e) {
            Log::error('Error en solicitud unificada: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la solicitud',
            ], 500);
        }
    }

    /**
     * Guarda el idSede en la solicitud y, si la sede tiene localidad, la copia
     * como localidad de recolección (un `localidad` explícito en el request la
     * sobreescribe luego porque se procesa después). Si la sede no existe, se
     * guarda 0 (la solicitud usa la dirección del cliente).
     */
    private function aplicarSede($idSede, array &$datos): void
    {
        $idSede = (int) $idSede;
        $sede   = SedeExpress::find($idSede);

        $datos['idSede'] = $sede ? $sede->id : 0;

        if ($sede && $sede->localidad) {
            $datos['localidad'] = $sede->localidad;
        }
    }

     private function calcularPrecio(string $peso): int|string
    {
        // Opcion numerica (Wati envia "1", "2", etc.)
        if (is_numeric(trim($peso))) {
            return $this->parsearPesoYPrecio($peso)['precio'];
        }

        // Nuevo formato (con o sin prefijo): "Microgenerador < 12 Kg - $60.000"
        if (preg_match('/\$\s*([\d.]+)/', $peso, $m)) {
            return (int) str_replace('.', '', $m[1]);
        }

        // Formato antiguo: "Menos de 20 kg"
        $mapa = [
            'menos de 12 kg'  => 60000,
            'menos de 20 kg'  => 80000,
            'menos de 40 kg'  => 115000,
            'menos de 60 kg'  => 150000,
            'menos de 100 kg' => 240000,
            'menos de 150 kg' => 342000,
        ];

        return $mapa[strtolower(trim($peso))] ?? 'Precio no definido';
    }
    /*
     * Parsea el peso recibido y devuelve [peso, precio].
     * Soporta 3 formatos: opcion numerica, texto nuevo Wati, texto antiguo.
     */
     private function parsearPesoYPrecio(string $peso): array
    {
        $peso = trim($peso);

        // Formato: opcion numerica "2"
        if (is_numeric($peso)) {
            $opciones = [
                1 => ['peso' => 'Menos de 12 kg',  'precio' => 60000],
                2 => ['peso' => 'Menos de 20 kg',  'precio' => 80000],
                3 => ['peso' => 'Menos de 40 kg',  'precio' => 115000],
                4 => ['peso' => 'Menos de 60 kg',  'precio' => 150000],
                5 => ['peso' => 'Menos de 100 kg', 'precio' => 240000],
                6 => ['peso' => 'Menos de 150 kg', 'precio' => 342000],
                7 => ['peso' => 'Mas de 200 kg',   'precio' => 'Precio no definido'],
            ];
            $opcion = (int) $peso;
            if (isset($opciones[$opcion])) {
                return $opciones[$opcion];
            }
            return ['peso' => $peso, 'precio' => 'Precio no definido'];
        }

        // Formato nuevo (con o sin prefijo): "2. ... - $80.000" o "... - $80.000"
        if (preg_match('/^(?:\d+\.\s*)?(.+?)\s*-\s*\$\s*([\d.]+)\s*$/u', $peso, $m)) {
            $precioNum = (int) str_replace('.', '', $m[2]);
            return ['peso' => trim($m[1]), 'precio' => $precioNum];
        }

        // Formato antiguo: "Menos de 20 kg"
        return ['peso' => $peso, 'precio' => $this->calcularPrecio($peso)];
    }
}
