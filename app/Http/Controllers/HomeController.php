<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SolicitudServicio;
use App\Vehiculo;
use App\Permisos;
use Carbon\Carbon;
use App\ProgramacionVehiculo;
use App\Calificacion;
use App\CrmActividad;
use App\CrmOportunidadV2;
use App\Personal;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // $Km = DB::table('progvehiculos')
        //     ->select('FK_ProgVehiculo', 'progVehKm', 'ProgVehFecha')
        //     ->where('ProgVehDelete', 0)
        //     ->where('progVehKm', '<>', null)
        //     ->where('FK_ProgVehiculo', 1)
        //     ->whereBetween('ProgVehFecha', [date('Y-m-d', strtotime("first day of last month")), date('Y-m-d', strtotime("last day of last month"))])
        //     ->get();
        
        // date('m', strtotime('2019-07-31'."+1 day"))
        // return $Km;
        // return date('Y-m-d', strtotime("first day of last month"))." - ".date('Y-m-d', strtotime("last day of last month"));

        $Vehiculos = Vehiculo::where('FK_VehiSede', 1)->get();
      

        switch (Auth::user()->UsRol) {

            case 'Cliente':
                if(Auth::user()->FK_UserPers === NULL){
                    return redirect()->route('clientes.create');
                }else{
                    // Obtener calificaciones pendientes del cliente
                    $calificacionesPendientes = Calificacion::with(['servicio.cliente', 'rm'])
                        ->where('ID_Cli', Auth::user()->id)
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    return view('home', compact('Vehiculos', 'calificacionesPendientes'));
                }
                break;
            
            case 'AsistenteLogistica':
                $SolicitudServicios = DB::table('solicitud_servicios')
                    ->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
                    ->where('SolSerDelete', 0)
                    ->get();
                $Recibidas = count($SolicitudServicios->where('SolSerStatus', 'Completado'));
                $Concialiadas = count($SolicitudServicios->where('SolSerStatus', 'Conciliado'));

                $serviciosnoconciliados = DB::table('solicitud_servicios')
                    ->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
                    ->where('SolSerDelete', 0)
                    ->where('SolSerStatus', 'No Conciliado')
                    ->orderBy('solicitud_servicios.updated_at', 'asc')
                    ->limit(5)
                    ->get();
                    
                // $Km = DB::table('progvehiculos')
                //     ->select('FK_ProgVehiculo', 'progVehKm', 'ProgVehFecha')
                //     ->where('ProgVehDelete', 0)
                //     ->where('progVehKm', '<>', null)
                //     ->whereBetween('ProgVehFecha', [date('Y-m-d', strtotime("first day of last month")), date('Y-m-d', strtotime("last day of last month"))])
                //     ->orderBy('ProgVehFecha', 'asc')
                //     ->get();
                setlocale(LC_ALL, "es_CO.UTF-8");

                $serviciosnoprocesados = DB::table('solicitud_servicios')
                    ->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
                    ->where('SolSerDelete', 0)
                    ->where('SolSerStatus', 'Completado')
                    ->orderBy('solicitud_servicios.updated_at', 'asc')
                    ->limit(5)
                    ->get();
                
                return view('home', compact('Vehiculos', 'serviciosnoprocesados',  'serviciosnoconciliados', 'Concialiadas', 'Recibidas', 'SolicitudServicios'));

                break;

            case 'Conductor':
                return redirect()->route('vehicle-programacion.index');
                break;

            case 'JefeLogistica':
                $SolicitudServicios = DB::table('solicitud_servicios')
                    ->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
                    ->where('SolSerDelete', 0)
                    ->get();

                $SolicitudServiciosProg = DB::table('solicitud_servicios')
                    ->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
                    ->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
                    ->select('solicitud_servicios.SolSerSlug','solicitud_servicios.ID_SolSer','solicitud_servicios.updated_at','clientes.CliShortname', 'progvehiculos.ProgVehFecha')
                    ->where('SolSerDelete', 0)
                    ->where('ProgVehDelete', 0)
                    ->where('ProgVehEntrada', null)
                    ->get();

                $Km = DB::table('progvehiculos')
                    ->select('FK_ProgVehiculo', 'progVehKm', 'ProgVehFecha')
                    ->where('ProgVehDelete', 0)
                    ->where('progVehKm', '<>', null)
                    ->whereBetween('ProgVehFecha', [date('Y-m-d', strtotime("first day of last month")), date('Y-m-d', strtotime("last day of last month"))])
                    ->orderBy('ProgVehFecha', 'asc')
                    ->get();
                setlocale(LC_ALL, "es_CO.UTF-8");

                $Pendientes = count($SolicitudServicios->where('SolSerStatus', 'Pendiente'));
                $Aprobadas = count($SolicitudServicios->where('SolSerStatus', 'Aprobado'));
                $Programadas = count($SolicitudServicios->where('SolSerStatus', 'Programado'));
                $Recibidas = count($SolicitudServicios->where('SolSerStatus', 'Completado'));
                $Concialiadas = count($SolicitudServicios->where('SolSerStatus', 'Conciliado'));
                $Tratadas = count($SolicitudServicios->where('SolSerStatus', 'Tratado'));
                $Certificadas = count($SolicitudServicios->where('SolSerStatus', 'Certificacion'));


                $ProgramacionesHoy = $SolicitudServiciosProg->where('ProgVehFecha', '=', date('Y-m-d', strtotime(now())));
                $ProgramacionesMa���ana = $SolicitudServiciosProg->where('ProgVehFecha', '=', date('Y-m-d', strtotime(now()."+1 day")));

                $serviciosnoprogramados = DB::table('solicitud_servicios')
                    ->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
                    ->where('SolSerDelete', 0)
                    ->where('SolSerStatus', 'Aprobado')
                    ->orderBy('solicitud_servicios.updated_at', 'asc')
                    ->limit(5)
                    ->get();
                return view('home', compact('SolicitudServicios', 'SolicitudServiciosProg', 'Km', 'Pendientes', 'Aprobadas', 'Programadas', 'Recibidas', 'Concialiadas', 'Tratadas', 'Certificadas', 'ProgramacionesHoy', 'ProgramacionesMa���ana', 'serviciosnoprogramados', 'Vehiculos'));
                break;

            case 'Supervisor':
                $programacions = DB::table('progvehiculos')
				->join('solicitud_servicios', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', 'clientes.ID_Cli')
				->select('progvehiculos.*', 'solicitud_servicios.ID_SolSer', 'solicitud_servicios.SolSerSlug', 'solicitud_servicios.SolSerVehiculo', 'solicitud_servicios.SolSerConductor', 'clientes.CliName')
				->whereIn('progvehiculos.ProgVehFecha', [today(), Carbon::tomorrow()])
				->get();
                $personals = DB::table('personals')
                    ->select('ID_Pers', 'PersFirstName', 'PersLastName')
                    ->get();
                $vehiculos = DB::table('vehiculos')
                    ->select('ID_Vehic','VehicPlaca')
                    ->get();

                $programacions = $programacions->map(function ($item) {
                    $programacion = ProgramacionVehiculo::with(['puntosderecoleccion.generadors'])
                        ->where('ID_ProgVeh', $item->ID_ProgVeh)
                        // ->where('forevaluation', 0)
                        ->first();
                    
                    $item->puntosderecoleccion =  $programacion->puntosderecoleccion;
                    return $item;
                });
                return view('home', compact('programacions', 'personals', 'vehiculos'));
                break;

            case 'value':
                # code...
                break;

            case 'value':
                # code...
                break;

            default:
                return view('home', compact('Vehiculos'));
                break;
        }
    }
    

    /**
     * Obtener el ID del comercial del usuario autenticado
     */
    private function getComercialId()
    {
        $user = Auth::user();
        if ($user->FK_UserPers) {
            return $user->FK_UserPers;
        }
        
        $personal = Personal::where('PersEmail', $user->email)->first();
        return $personal ? $personal->ID_Pers : null;
    }

    /**
     * API para obtener eventos del calendario (actividades y oportunidades)
     */
    public function obtenerEventosCalendario(Request $request)
    {
        $comercialId = $this->getComercialId();
        
        if (!$comercialId) {
            return response()->json([]);
        }

        $start = $request->input('start');
        $end = $request->input('end');

        $eventos = [];

        // Actividades
        $actividades = CrmActividad::with(['cliente'])
            ->where('FK_ActComercial', $comercialId)
            ->where('ActEstado', 'Pendiente')
            ->whereBetween('ActFechaProgramada', [$start, $end])
            ->get();

        foreach ($actividades as $actividad) {
            $color = '#3c8dbc'; // Azul por defecto
            $icono = 'fa-tasks';
            
            switch($actividad->ActTipo) {
                case 'Llamada':
                    $color = '#007bff';
                    $icono = 'fa-phone';
                    break;
                case 'Visita':
                    $color = '#28a745';
                    $icono = 'fa-map-marker-alt';
                    break;
                case 'Email':
                    $color = '#17a2b8';
                    $icono = 'fa-envelope';
                    break;
                case 'Reunión':
                    $color = '#ffc107';
                    $icono = 'fa-users';
                    break;
            }

            $fechaInicio = Carbon::parse($actividad->ActFechaProgramada);
            $fechaFin = $fechaInicio->copy()->addHour();
            
            // Acortar el título para que sea más legible en el calendario
            $tituloCompleto = $actividad->ActTipo . ': ' . $actividad->ActTitulo;
            $tituloCorto = strlen($tituloCompleto) > 40 ? substr($tituloCompleto, 0, 40) . '...' : $tituloCompleto;
            
            $eventos[] = [
                'id' => 'act_' . $actividad->ID_Actividad,
                'title' => $tituloCorto,
                'start' => $fechaInicio->format('Y-m-d\TH:i:s'),
                'end' => $fechaFin->format('Y-m-d\TH:i:s'),
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'tipo' => 'actividad',
                    'id' => $actividad->ID_Actividad,
                    'titulo' => $actividad->ActTitulo,
                    'tipoActividad' => $actividad->ActTipo,
                    'cliente' => $actividad->cliente ? $actividad->cliente->CliName : 'Sin cliente',
                    'estado' => $actividad->ActEstado,
                    'url' => route('crm.actividades.index')
                ]
            ];
        }

        // Oportunidades (fechas de cierre)
        $oportunidades = CrmOportunidadV2::with(['cliente'])
            ->where('FK_OportComercial', $comercialId)
            ->where('OportEstado', 'Activa')
            ->whereNotNull('OportFechaCierreEsperada')
            ->whereBetween('OportFechaCierreEsperada', [$start, $end])
            ->get();

        foreach ($oportunidades as $oportunidad) {
            $diasRestantes = now()->diffInDays(Carbon::parse($oportunidad->OportFechaCierreEsperada), false);
            $color = '#6c757d'; // Gris por defecto
            
            if ($diasRestantes <= 1) {
                $color = '#dc3545'; // Rojo - urgente
            } elseif ($diasRestantes <= 3) {
                $color = '#ffc107'; // Amarillo - media urgencia
            } else {
                $color = '#28a745'; // Verde - baja urgencia
            }

            // Acortar el título para que sea más legible en el calendario
            $tituloCompleto = 'Cierre: ' . $oportunidad->OportTitulo;
            $tituloCorto = strlen($tituloCompleto) > 35 ? substr($tituloCompleto, 0, 35) . '...' : $tituloCompleto;
            
            $eventos[] = [
                'id' => 'oportunidad_' . $oportunidad->ID_Oportunidad,
                'title' => $tituloCorto,
                'start' => Carbon::parse($oportunidad->OportFechaCierreEsperada)->format('Y-m-d'),
                'allDay' => true,
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'tipo' => 'oportunidad',
                    'id' => $oportunidad->ID_Oportunidad,
                    'titulo' => $oportunidad->OportTitulo,
                    'cliente' => $oportunidad->cliente ? $oportunidad->cliente->CliName : 'Sin cliente',
                    'valor' => $oportunidad->OportValorEstimado,
                    'probabilidad' => $oportunidad->OportProbabilidad,
                    'url' => route('crm.oportunidades.index')
                ]
            ];
        }

        return response()->json($eventos);
    }
}