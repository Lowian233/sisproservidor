<?php

namespace App\Services;

use App\SolicitudServicio;
use App\SolicitudResiduo;
use App\ClienteExpress;
use App\Generador;
use App\GenerSede;
use App\ResiduosGener;
use App\Observacion;
use App\ProgramacionVehiculo;
use App\SedeExpress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateSolicitudExpressService
{
    /**
     * Crear una solicitud de servicio express completa
     *
     * @param array $datos
     * @param int|string $idSolicitud
     * @return SolicitudServicio
     * @throws Exception
     */
    public function createSolicitud(array $datos, $idSolicitud): SolicitudServicio
    {
        $idCliente = $datos['idCliente'] ?? null;
        if(!$idCliente) {
            $idCliente = DB::table('solicitudes_express')
                ->where('idSolicitud', $idSolicitud)
                ->first();
            $idSede = $idCliente->idSede;
        }

        $direccionObj = DB::table('sedes_express')
            ->where('id', $idSede)
            ->select('direccion')
            ->first();

        $direccion = $direccionObj->direccion ?? '';

        $solExpress = new SolicitudServicio();
        $solExpress->SolSerStatus = 'Programado';
        $solExpress->SolSerTipo = 'Interno';
        $solExpress->SolSerAuditable = 0;
        $solExpress->SolSerConductor = 'RAFAEL CAMACHO ACEVEDO';
        $solExpress->SolSerVehiculo = 'NUV-586';
        $solExpress->SolSerSlug = hash('sha256', rand() . time() . $direccion);
        $solExpress->created_at = now();
        $solExpress->updated_at = now();
        $solExpress->SolSerDelete = 0;
        $solExpress->SolResAuditoriaTipo = 0;
        $solExpress->SolSerNameTrans = 'PROTECCIÓN SERVICIOS AMBIENTALES RESPEL DE COLOMBIA S.A. ESP.';
        $solExpress->SolSerNitTrans = '900.079.188-0';
        $solExpress->SolSerAdressTrans = 'KM 6 VÍA LA MESA SUB ESTACIÓN BALSILLAS';
        $solExpress->SolSerCityTrans = 584;
        $solExpress->SolSerTypeCollect = 99;
        $solExpress->SolSerCollectAddress = $direccion;
        $solExpress->FK_SolSerCollectMun = null;
        $solExpress->SolSerBascula = 0;
        $solExpress->SolSerCapacitacion = 0;
        $solExpress->SolSerMasPerson = 0;
        $solExpress->SolSerVehicExclusive = 0;
        $solExpress->SolSerPlatform = 0;
        $solExpress->SolSerDevolucion = 0;
        $solExpress->SolSerDevolucionTipo = null;
        $solExpress->FK_SolSerPersona = null;
        // clientes/personals no aplican al flujo express: no hay un ID valido al cual apuntar
        $solExpress->FK_SolSerCliente = null;
        $solExpress->FK_Cliente_Express = $idCliente->idCliente;
        $solExpress->SolSerDescript = null;
        $solExpress->SolSerSupport = null;
        $solExpress->SolServCertStatus = 0;
        $solExpress->SolServMailCopia = json_encode([]);
        $solExpress->SolSerRMs = null;
        $solExpress->SolSerTranspPrecio = 0;
        $solExpress->FK_ReciboSolserv = null;
        $solExpress->save();

        Log::info('SolicitudServicio creada con ID: ' . $solExpress->ID_SolSer . ' y Slug: ' . $solExpress->SolSerSlug);

        $sGener = $this->addGenerador($solExpress);

        Log::info('GenerSede encontrado o creado con ID: ' . $sGener->ID_GSede . ' para Generador ID: ' . $sGener->FK_GSede);

        $this->createRespel($idSolicitud, $sGener, $solExpress->ID_SolSer);

        /* Se guarda la observación inicial del servicio */
        $observacion = new Observacion();
        $observacion->ObsStatus = $solExpress->SolSerStatus;
        $observacion->ObsTipo = 'prosarc';
        $observacion->ObsRepeat = 1;
        $observacion->ObsDate = now();
        $observacion->ObsUser = 'chatbot@prosarc.com.co';
        $observacion->ObsRol = 'programador';
        $observacion->FK_ObsSolSer = $solExpress->ID_SolSer;
        $observacion->save();

        $programacion = $this->createProgramacion($solExpress, $datos['localidad'] ?? '');

        log::info('Programación creada con ID: ' . $programacion->ID_ProgVehiculo . ' para SolicitudServicio ID: ' . $solExpress->ID_SolSer . 'En la fecha: ' . $programacion->ProgVehFecha);

        $solExpress->ProgVehFecha = $programacion->ProgVehFecha;

        return $solExpress;
    }

    public function addGenerador(SolicitudServicio $solicitudServicio)
    {
        Log::info('Datos de la SolicitudServicio: ' . $solicitudServicio);

        $cliente = ClienteExpress::findOrFail($solicitudServicio->FK_Cliente_Express);
        Log::info('ClienteExpress encontrado: ' . $cliente->nombreEmpresa);

        $sede = SedeExpress::where('idClienteExpress', $cliente->id)->first();
        $generador = Generador::where('GenerNit', $cliente->nit)->first();

        if (!$generador) {
            $generador = new Generador();
            $generador->GenerNit = $cliente->nit;
            $generador->GenerName = $cliente->nombreEmpresa;
            $generador->GenerShortName = null;
            $generador->GenerCode = null;
            $generador->GenerType = null;
            $generador->GenerSlug = hash('sha256', rand() . time() . $cliente->nit);
            // FK_GenerCli referencia la tabla legada "sedes", que no aplica al flujo express
            $generador->FK_GenerCli = null;
            $generador->GenerDelete = 0;
            $generador->save();
        }

        $sGener = GenerSede::where('FK_GSede', $generador->ID_Gener)->first();

        if (!$sGener) {
            $sGener = new GenerSede();
            $sGener->GSedeName = $sede->nombreSede ?? $cliente->nombreEmpresa;
            $sGener->GSedeAddress = $sede->direccion ?? '';
            $sGener->GSedePhone1 = $cliente->telefono;
            $sGener->GSedeExt1 = null;
            $sGener->GSedeEmail = $cliente->correoEmpresa;
            $sGener->GSedeSlug = hash('sha256', rand() . time() . ($sede->nombreSede ?? 'Sede'));
            $sGener->FK_GSede = $generador->ID_Gener;
            $sGener->FK_GSedeMun = 544;
            $sGener->GSedeDelete = 0;
            $sGener->GSedeMapAddressSearch = $sede->direccion ?? '';
            $sGener->GSedeMapLocalidad = $sede->localidad ?? null;
            $sGener->GSedeMapAddressResult = $sede->direccion ?? '';
            $sGener->GSedeMapLat = 4.6875167;
            $sGener->GSedeMapLong = -74.0739892;
            $sGener->save();
        }

        return $sGener;
    }

    public function createRespel($idSolicitud, $sGener, $solExpress)
    {
        $cotizaciones = DB::table('solicitudes_express')->where('idSolicitud', $idSolicitud)->get();

        Log::info('Cotizaciones encontradas para idSolicitud ' . $idSolicitud . ': ' . $cotizaciones);

        foreach ($cotizaciones as $cotizacion) {
            $respel = DB::table('respels')
                ->where('RespelName', $cotizacion->tipoResiduo)
                ->first();

            if (!$respel) {
                throw new Exception('No se encontró el residuo: ' . $cotizacion->tipoResiduo);
            }

            $respelRequerimiento = DB::table('requerimientos')
                ->where('FK_ReqRespel', $respel->ID_Respel)
                ->first();

            Log::info('Respel encontrado: ' . json_encode($respel));

            $generResiduo = DB::table('residuos_geners')
                ->where('FK_SGener', $sGener->ID_GSede)
                ->where('FK_Respel', $respel->ID_Respel)
                ->first();

            if (!$generResiduo) {
                $generResiduo = new ResiduosGener();
                $generResiduo->FK_SGener = $sGener->ID_GSede;
                $generResiduo->FK_Respel = $respel->ID_Respel;
                $generResiduo->SlugSGenerRes = hash('sha256', rand() . time() . $sGener->ID_GSede . $respel->ID_Respel);
                $generResiduo->DeleteSGenerRes = 0;
                $generResiduo->save();
            }

            $solicitudResiduo = new SolicitudResiduo();
            $solicitudResiduo->SolResKgEnviado = 1;
            $solicitudResiduo->SolResDelete = 0;
            $solicitudResiduo->SolResSlug = hash('sha256', rand() . time() . $idSolicitud . $sGener->ID_GSede . $respel->ID_Respel);
            $solicitudResiduo->FK_SolResSolSer = $solExpress;
            $solicitudResiduo->FK_SolResRg = $generResiduo->ID_SGenerRes;
            $solicitudResiduo->SolResTypeUnidad = null;
            $solicitudResiduo->SolResCantiUnidad = null;
            $solicitudResiduo->SolResEmbalaje = 'Sacos/Bolsas';
            $solicitudResiduo->SolResAuditoria = 0;
            $solicitudResiduo->SolResPrecio = $cotizacion->precio;
            $solicitudResiduo->auto_SolResFotoDescargue_Pesaje = 0;
            $solicitudResiduo->auto_SolResFotoTratamiento = 0;
            $solicitudResiduo->auto_SolResVideoDescargue_Pesaje = 0;
            $solicitudResiduo->auto_SolResVideoTratamiento = 0;
            $solicitudResiduo->auto_SolResDevolucion = 0;
            $solicitudResiduo->auto_SolResAuditoria = 0;
            $solicitudResiduo->SolResTypePrecio = 0;
            $solicitudResiduo->FK_SolResRequerimiento = $respelRequerimiento->ID_Req ?? null;
            $solicitudResiduo->save();
        }
    }

    public function createProgramacion(SolicitudServicio $solicitudServicio, $localidad)
    {
        $localidadDefinida = [
            1 => ['suba', 'engativa', 'barrios unidos'],
            2 => ['centro', 'restrepo', 'usme', 'ciudad bolivar', 'rafael uribe', 'san cristobal', 'antonio narino', 'tunjuelito'],
            3 => ['usaquen', 'chapinero', 'suba'],
            4 => ['kennedy', 'puente aranda', 'bosa', 'fontibon', 'modelia'],
            5 => ['chapinero', 'teusaquillo', 'barrios unidos', 'engativa']
        ];

        $unwanted = ['á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n'];
        $localidadLimpia = strtr(mb_strtolower(trim($localidad)), $unwanted);

        $fechaEvaluada = \Carbon\Carbon::now()->addDay();
        $fechaCalculada = null;

        for ($i = 0; $i < 14; $i++) {
            $diaSemana = $fechaEvaluada->dayOfWeekIso;

            if ($diaSemana <= 5 && isset($localidadDefinida[$diaSemana])) {
                $localidadesDelDia = $localidadDefinida[$diaSemana];

                if (in_array($localidadLimpia, $localidadesDelDia, true)) {
                    $fechaCalculada = $fechaEvaluada->copy()->startOfDay();
                    break;
                }
            }

            $fechaEvaluada->addDay();
        }

        if (!$fechaCalculada) {
            $fechaCalculada = \Carbon\Carbon::now()->addDay();
            while ($fechaCalculada->isWeekend()) {
                $fechaCalculada->addDay();
            }
            $fechaCalculada->startOfDay();
        }

        $programacion = new ProgramacionVehiculo();
        $programacion->ProgVehFecha = $fechaCalculada->format('Y-m-d');
        $programacion->ProgVehTurno = 0;
        $programacion->ProgVehtipo = 1;
        $programacion->ProgVehEntrada = now();
        $programacion->ProgVehSalida = $fechaCalculada->format('Y-m-d') . ' 08:00:00';
        $programacion->ProgVehColor = null;
        $programacion->FK_ProgVehiculo = 23;
        $programacion->FK_ProgMan = null;
        $programacion->FK_ProgServi = $solicitudServicio->ID_SolSer;
        $programacion->FK_ProgConductor = 2951;
        $programacion->FK_ProgAyudante = null;
        $programacion->ProgVehDelete = 0;
        $programacion->ProgVehStatus = 'Autorizado';
        $programacion->save();

        return $programacion;
    }
}
