<?php

namespace App\Http\Controllers;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Mail\VehiculoRecibidoEmail;
use App\Mail\CancelSolServEmail;
use App\Mail\SolSerEmail;
use App\Mail\ProgramacionParafiscales;
use App\Mail\SustanciaControladaProgramada;
use App\Mail\AceiteUsadoProgramado;
use App\Mail\ServicioTipoRecorrido;
use App\audit;
use App\ProgramacionVehiculo;
use App\Vehiculo;
use App\Personal;
use App\SolicitudServicio;
use App\GenerSede;
use App\SolicitudResiduo;
use App\Documento;
use App\Docdato;
use App\Recolect;
use App\Cliente;
use App\Sede;
use App\Requerimiento;
use App\Observacion;
use App\Permisos;

class VehicProgController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
public function index()
	{
		// Permitir acceso a Prosarc y Conductores (la query ya filtra por conductor cuando aplica)
		if(
			in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) ||
			in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) ||
			in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) ||
			in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)
		){
			$programacions = DB::table('progvehiculos')
				->join('solicitud_servicios', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', 'clientes.ID_Cli')
				->select('progvehiculos.*', 'solicitud_servicios.ID_SolSer', 'solicitud_servicios.SolSerSlug', 'solicitud_servicios.SolSerStatus', 'solicitud_servicios.SolSerVehiculo', 'solicitud_servicios.SolSerConductor', 'clientes.CliName', 'clientes.CliCategoria', 'progvehiculos.ProgVehPreoperacionalCompletado', 'progvehiculos.ProgVehKmInicial', 'progvehiculos.ProgVehKmFinal')
				->where(function($query){
					if(!in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
						$query->where('progvehiculos.ProgVehDelete', 0);
					}
					if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR)||in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
						$query->where('progvehiculos.FK_ProgConductor', Auth::user()->FK_UserPers);
						// El conductor debe ver programaciones "Programado" y "Notificado"
						$query->whereIn('solicitud_servicios.SolSerStatus', ['Programado', 'Notificado']);
						$query->whereDate('progvehiculos.ProgVehFecha', '>=', Carbon::today());
					}
					if(in_array(Auth::user()->UsRol, Permisos::TESORERIA)||in_array(Auth::user()->UsRol2, Permisos::TESORERIA)){
						$query->where('progvehiculos.ProgVehStatus', 'Pendiente');
					}
				})
				->where('clientes.CliCategoria', 'Cliente')
				// Filtrar por rango usando la fecha de programación (no created_at del servicio),
				// para no ocultar programaciones de servicios antiguos.
				->whereBetween('progvehiculos.ProgVehFecha', ['2024-07-01', '2026-12-31'])
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
			// return $programacions;
			return view('ProgramacionVehicle.index', compact('programacions', 'personals', 'vehiculos'));
		} else {
			/*Validacion para usuarios no permitidos en esta vista*/
			abort(403);
		}
			// return $programacions;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC)){
			$programacions = DB::table('progvehiculos')
				->join('solicitud_servicios', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
				->select('progvehiculos.*', 'solicitud_servicios.ID_SolSer', 'clientes.CliName', 'clientes.CliCategoria')
				->where('progvehiculos.ProgVehDelete', 0)
				->where('clientes.CliCategoria', 'Cliente')
				->whereBetween('solicitud_servicios.created_at',['2024-07-01 00:00:00','2026-12-31 23:59:00'])
				->get();
			$transportadores = DB::table('clientes')
				->select('CliName', 'CliSlug')
				->where('CliCategoria', 'Transportador')
				->where('CliDelete', 0)
				->get();
			$mantenimientos = DB::table('mantenvehics')
				->join('vehiculos', 'mantenvehics.FK_VehMan', '=', 'vehiculos.ID_Vehic')
				->select('mantenvehics.*','vehiculos.VehicPlaca')
				->where('mantenvehics.HoraMavFin', '>', now())
				->get();
			/*conductores de prosarc*/
			$conductors = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName')
				->where('CargName', 'Conductor')
				->where('ID_Cli', 1)
				->where('PersDelete', '!=' , 1)
				->get();
			/*auxiliares de prosarc*/
			$ayudantes = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName')
				->whereIn('AreaName', ['Operaciones', 'Logística', 'Mantenimiento'])
				->whereNotIn('CargName', ["Asistente", 'Jefe', 'Supervisor'])
				->where('ID_Cli', 1)
				->where('PersDelete', '!=' , 1)
				->get();
			$vehiculos = DB::table('vehiculos')
				->select('ID_Vehic','VehicPlaca')
				->where('vehiculos.FK_VehiSede', 1)
				->where('VehicDelete', 0)
				->get();
			
			if(in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol2, Permisos::SUPERVISOR)){
				$serviciosnoprogramados = DB::table('solicitud_servicios')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
				->select('solicitud_servicios.ID_SolSer', 'solicitud_servicios.SolSerSlug', 'solicitud_servicios.SolSerTipo', 'clientes.CliName', 'clientes.CliCategoria')
				->where('SolSerDelete', 0)
				->where('SolSerStatus', 'Aprobado')
				->where('clientes.CliCategoria', 'Cliente')
				->whereIn('SolSerTipo', ['Cliente', 'Externo', 'Generador'])
				->orderBy('solicitud_servicios.updated_at', 'asc')
				->get();

			}else if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA)){
				$serviciosnoprogramados = DB::table('solicitud_servicios')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
				->select('solicitud_servicios.ID_SolSer', 'solicitud_servicios.SolSerSlug', 'solicitud_servicios.SolSerTipo', 'clientes.CliName', 'clientes.CliCategoria')
				->where('SolSerDelete', 0)
				->where('SolSerStatus', 'Aprobado')
				->where('clientes.CliCategoria', 'Cliente')
				->where('SolSerTipo', 'Interno')
				->orderBy('solicitud_servicios.updated_at', 'asc')
				->get();
			}else{
				$serviciosnoprogramados = DB::table('solicitud_servicios')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', '=', 'clientes.ID_Cli')
				->select('solicitud_servicios.ID_SolSer', 'solicitud_servicios.SolSerSlug', 'solicitud_servicios.SolSerTipo', 'clientes.CliName', 'clientes.CliCategoria')
				->where('SolSerDelete', 0)
				->where('SolSerStatus', 'Aprobado')
				->where('clientes.CliCategoria', 'Cliente')
				->orderBy('solicitud_servicios.updated_at', 'asc')
				->get();
			}
				/*return $programacions;*/
			return view('ProgramacionVehicle.create', compact('programacions', 'conductors', 'ayudantes', 'vehiculos', 'serviciosnoprogramados', 'mantenimientos', 'transportadores'));
		}
		 /*Validacion para usuarios no permitidos en esta vista*/
		else{
			abort(403);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$validate = $request->validate([
			// 'ProgVehPrecintos'   =>   'max:16|min:1'
		]);
		// return $request;
		$programacion = new ProgramacionVehiculo();
		if(date('H', strtotime($request->input('ProgVehSalida'))) >= 12){
			$turno = "0";
		}
		else{
			$turno = "1";
		}
		$programacion->ProgVehTurno = $turno;
		$programacion->ProgVehFecha = $request->input('ProgVehFecha');
		$programacion->ProgVehSalida = $request->input('ProgVehFecha').' '.date('H:i:s', strtotime($request->input('ProgVehSalida')));
		$programacion->ProgVehEntrada = now();

		/*typetransportador = 0 -> transporte prosarc*/
		/*typetransportador = 1 -> transporte alquilado*/
		if(!is_null($request->input('typetransportador'))){
			if($request->input('typetransportador') == 0){
				/*ProgVehtipo = 0 -> transporte externo*/
				/*ProgVehtipo = 1 -> transporte interno prosarc*/
				/*ProgVehtipo = 2 -> transporte alquilado*/

                $programacion->ProgVehtipo = 1;
                $fkVehiculoInput = $request->input('FK_ProgVehiculo');
                $fkVehiculoId = is_array($fkVehiculoInput)
                    ? (count($fkVehiculoInput) ? $fkVehiculoInput[0] : null)
                    : $fkVehiculoInput;
                $programacion->FK_ProgVehiculo = $fkVehiculoId;
				$programacion->ProgVehColor = $request->input('ProgVehColor');

                // Normaliza precintos a JSON o null para evitar "Array to string conversion"
                $precintos = $request->input('ProgVehPrecintos');
                if (is_array($precintos)) {
                    $precintos = array_values(array_filter($precintos, function ($v) { return $v !== null && $v !== ''; }));
                    $programacion->ProgVehPrecintos = count($precintos) ? json_encode($precintos) : null;
                } elseif (is_string($precintos) && trim($precintos) !== '') {
                    $programacion->ProgVehPrecintos = json_encode([$precintos]);
                } else {
                    $programacion->ProgVehPrecintos = null;
                }

                $programacion->FK_ProgConductor = is_array($request->input('FK_ProgConductor'))
                    ? (count($request->input('FK_ProgConductor')) ? $request->input('FK_ProgConductor')[0] : null)
                    : $request->input('FK_ProgConductor');
                $programacion->FK_ProgAyudante = is_array($request->input('FK_ProgAyudante'))
                    ? (count($request->input('FK_ProgAyudante')) ? $request->input('FK_ProgAyudante')[0] : null)
                    : $request->input('FK_ProgAyudante');
                $conductor = Personal::select('PersFirstName', 'PersLastName')->where('ID_Pers', $programacion->FK_ProgConductor)->first();
				$nomConduct = $conductor->PersFirstName." ".$conductor->PersLastName;
                $vehiculo = null;
                if (!is_null($fkVehiculoId)) {
                    $vehiculoObj = Vehiculo::select('VehicPlaca')->where('ID_Vehic', $fkVehiculoId)->first();
                    $vehiculo = $vehiculoObj ? $vehiculoObj->VehicPlaca : null;
                }
				$transportador = DB::table('clientes')
					->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
					->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName', 'municipios.ID_Mun')
					->where('ID_Cli', 1)
					->first();
			}
			else{
				$programacion->ProgVehtipo = 2;
                $programacion->FK_ProgVehiculo = is_array($request->input('vehicalqui'))
                    ? (count($request->input('vehicalqui')) ? $request->input('vehicalqui')[0] : null)
                    : $request->input('vehicalqui');
                $programacion->FK_ProgAyudante = is_array($request->input('FK_ProgAyudante'))
                    ? (count($request->input('FK_ProgAyudante')) ? $request->input('FK_ProgAyudante')[0] : null)
                    : $request->input('FK_ProgAyudante');
                $programacion->ProgVehDocConductorEXT = is_array($request->input('ProgVehDocConductorEXT'))
                    ? (count($request->input('ProgVehDocConductorEXT')) ? $request->input('ProgVehDocConductorEXT')[0] : null)
                    : $request->input('ProgVehDocConductorEXT');
                $programacion->ProgVehNameConductorEXT = is_array($request->input('ProgVehNameConductorEXT'))
                    ? (count($request->input('ProgVehNameConductorEXT')) ? $request->input('ProgVehNameConductorEXT')[0] : null)
                    : $request->input('ProgVehNameConductorEXT');
                $programacion->ProgVehDocAuxiliarEXT = is_array($request->input('ProgVehDocAuxiliarEXT'))
                    ? (count($request->input('ProgVehDocAuxiliarEXT')) ? $request->input('ProgVehDocAuxiliarEXT')[0] : null)
                    : $request->input('ProgVehDocAuxiliarEXT');
                $programacion->ProgVehNameAuxiliarEXT = is_array($request->input('ProgVehNameAuxiliarEXT'))
                    ? (count($request->input('ProgVehNameAuxiliarEXT')) ? $request->input('ProgVehNameAuxiliarEXT')[0] : null)
                    : $request->input('ProgVehNameAuxiliarEXT');
                $programacion->ProgVehPlacaEXT = is_array($request->input('ProgVehPlacaEXT'))
                    ? (count($request->input('ProgVehPlacaEXT')) ? $request->input('ProgVehPlacaEXT')[0] : null)
                    : $request->input('ProgVehPlacaEXT');
                $programacion->ProgVehTipoEXT = is_array($request->input('ProgVehTipoEXT'))
                    ? (count($request->input('ProgVehTipoEXT')) ? $request->input('ProgVehTipoEXT')[0] : null)
                    : $request->input('ProgVehTipoEXT');
				$programacion->ProgVehColor = '#FFFF00';
                // Normaliza precintos a JSON o null para evitar "Array to string conversion"
                $precintos = $request->input('ProgVehPrecintos');
                if (is_array($precintos)) {
                    $precintos = array_values(array_filter($precintos, function ($v) { return $v !== null && $v !== ''; }));
                    $programacion->ProgVehPrecintos = count($precintos) ? json_encode($precintos) : null;
                } elseif (is_string($precintos) && trim($precintos) !== '') {
                    $programacion->ProgVehPrecintos = json_encode([$precintos]);
                } else {
                    $programacion->ProgVehPrecintos = null;
                }
				if ($request->input('vehicalqui')!=null) {
					$vehiculoObj = Vehiculo::select('VehicPlaca')->where('ID_Vehic', $request->input('vehicalqui'))->first();
					$vehiculo = $vehiculoObj ? $vehiculoObj->VehicPlaca : null;
				}else{
					$vehiculo = null;
				}

				$nomConduct = null;
				$transportador = DB::table('clientes')
					->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
					->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName', 'municipios.ID_Mun')
					->where('CliSlug', $request->input('transport'))
					->first();
			}
		}
		else{
			$nomConduct = null;
			$vehiculo = null;
			$programacion->ProgVehtipo = 0;
		}
        $programacion->FK_ProgServi = is_array($request->input('FK_ProgServi'))
            ? (count($request->input('FK_ProgServi')) ? $request->input('FK_ProgServi')[0] : null)
            : $request->input('FK_ProgServi');
		$programacion->ProgVehDelete = 0;
        $programacion->ProgVehStatus = 'Autorizado';
        $exclusiveInput = $request->input('ProgVehExclusive');
        if (is_array($exclusiveInput)) {
            $first = count($exclusiveInput) ? reset($exclusiveInput) : 0;
            $programacion->ProgVehExclusive = (int) (bool) $first;
        } else {
            // soporta 'on', '1', true/false y null
            $programacion->ProgVehExclusive = $request->boolean('ProgVehExclusive') ? 1 : ((is_null($exclusiveInput) || $exclusiveInput === '') ? 0 : (int) $exclusiveInput);
        }
        // Sanitiza cualquier atributo que accidentalmente sea array
        foreach ($programacion->getAttributes() as $attrKey => $attrValue) {
            if (is_array($attrValue)) {
                if ($attrKey === 'ProgVehPrecintos') {
                    $clean = array_values(array_filter($attrValue, function ($v) { return $v !== null && $v !== ''; }));
                    $programacion->$attrKey = count($clean) ? json_encode($clean) : null;
                } else {
                    $programacion->$attrKey = count($attrValue) ? $attrValue[0] : null;
                }
            }
        }

        $programacion->save();
		// return $request->input('FK_ProgServi');

		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();

		if ($SolicitudServicio->SolSerStatus == 'Aprobado') {

			$serviciovalidado = $request->input('FK_ProgServi');
			/*cuenta los diferentes generadores*/
			$generadoresdelasolicitud = GenerSede::whereHas('resgener.solres', function ($query) use ($serviciovalidado) {
			    $query->where('solicitud_residuos.FK_SolResSolSer', $serviciovalidado);
			})
			->with(['resgener' => function ($query) use ($serviciovalidado){
			    $query->with(['solres' => function ($query) use ($serviciovalidado){
			    	$query->where('FK_SolResSolSer', $serviciovalidado);
			    }]);
			    $query->whereHas('solres', function ($query) use ($serviciovalidado){
			    	$query->where('FK_SolResSolSer', $serviciovalidado);
			    });
			}])
			->get();
		}
		// return $generadoresdelasolicitud;
		/*$programacion->ProgVehtipo: 0 = externo; 1 = Prosarc; 2 = alquilado; */
		/*SolSerTypeCollect: 99 = sedes generadores; 98 = sede cliente; 97 = direccion especifica; */
		/*DocType: 0 = manifiesto de carga; 1 = certificado; 2 = manifiesto de envio*/
		switch ($SolicitudServicio->SolSerTypeCollect) {
			/*recolectar en sedes de los generadores*/
			case '99':
				foreach ($generadoresdelasolicitud as $sole) {
					switch ($programacion->ProgVehtipo) {
						/*externo*/
						case '0':
							$nuevodoc = new Documento;
							$nuevodoc->DocType = 0;
							$nuevodoc->DocNumero = 0;
							$nuevodoc->DocEspName = 0;
							$nuevodoc->DocEspValue = 0;
							$nuevodoc->DocObservacion = "no deberia generar documentos";
							$nuevodoc->DocSlug = hash('sha256', rand().time());
							$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
							$nuevodoc->DocNumRm = 0;
							$nuevodoc->DocAuthHseq = 0;
							$nuevodoc->DocAuthJl = 0;
							$nuevodoc->DocAuthDp = 0;
							$nuevodoc->DocAnexo = 0;
							$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
							$nuevodoc->DocEspValue = 0;
							// return $nuevodoc;

							break;

						/*Prosarc*/
						case '1':
							$nuevodoc = new Documento;
							$nuevodoc->DocType = 0;
							$nuevodoc->DocNumero = 1;
							$nuevodoc->DocEspName = 1;
							$nuevodoc->DocEspValue = 1;
							$nuevodoc->DocObservacion = "ok generar varios documentos";
							$nuevodoc->DocSlug = hash('sha256', rand().time());
							$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
							$nuevodoc->DocNumRm = 1;
							$nuevodoc->DocAuthHseq = 0;
							$nuevodoc->DocAuthJl = 0;
							$nuevodoc->DocAuthDp = 0;
							$nuevodoc->DocAnexo = 1;
							$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
							$nuevodoc->DocEspValue = 1;
							// return $nuevodoc;

							break;

						/*Alquilado*/
						case '2':
							$nuevodoc = new Documento;
							$nuevodoc->DocType = 0;
							$nuevodoc->DocNumero = 2;
							$nuevodoc->DocEspName = 2;
							$nuevodoc->DocEspValue = 2;
							$nuevodoc->DocObservacion = "ok generar varios documentos";
							$nuevodoc->DocSlug = hash('sha256', rand().time());
							$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
							$nuevodoc->DocNumRm = 2;
							$nuevodoc->DocAuthHseq = 0;
							$nuevodoc->DocAuthJl = 0;
							$nuevodoc->DocAuthDp = 0;
							$nuevodoc->DocAnexo = 2;
							$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
							$nuevodoc->DocEspValue = 2;
							// return $nuevodoc;

							break;

						default:
							return "no encontro el tipo de servicio";
							break;
					}
						$nuevodoc->save();

						foreach ($sole->resgener as $resgener) {
							foreach ($resgener->solres as $key) {
								$nuevodocdato = new Docdato;
								$nuevodocdato->FK_DatoDoc = $nuevodoc->ID_Doc;
								$nuevodocdato->FK_DatoSolRes = $key->ID_SolRes;
								$nuevodocdato->save();
							}
						}
					$puntoderecoleccion = new Recolect;
					$puntoderecoleccion->FK_ColectSgen = $sole->ID_GSede;
					$puntoderecoleccion->FK_ColectProg = $programacion->ID_ProgVeh;
					$puntoderecoleccion->save();

					// return $puntoderecoleccion;

				}
				break;

			/*recolectar en sede del cliente*/
			case '98':
					switch ($programacion->ProgVehtipo) {
						/*externo*/
						case '0':
							$nuevodoc = new Documento;
							$nuevodoc->DocType = 0;
							$nuevodoc->DocNumero = 0;
							$nuevodoc->DocEspName = 0;
							$nuevodoc->DocEspValue = 0;
							$nuevodoc->DocObservacion = "no deberia generar documentos";
							$nuevodoc->DocSlug = hash('sha256', rand().time());
							$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
							$nuevodoc->DocNumRm = 0;
							$nuevodoc->DocAuthHseq = 0;
							$nuevodoc->DocAuthJl = 0;
							$nuevodoc->DocAuthDp = 0;
							$nuevodoc->DocAnexo = 0;
							$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
							$nuevodoc->DocEspValue = 0;
							// return $nuevodoc;

							break;

						/*Prosarc*/
						case '1':
							$nuevodoc = new Documento;
							$nuevodoc->DocType = 0;
							$nuevodoc->DocNumero = 1;
							$nuevodoc->DocEspName = 1;
							$nuevodoc->DocEspValue = 1;
							$nuevodoc->DocObservacion = "documento con la sede del generador";
							$nuevodoc->DocSlug = hash('sha256', rand().time());
							$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
							$nuevodoc->DocNumRm = 1;
							$nuevodoc->DocAuthHseq = 0;
							$nuevodoc->DocAuthJl = 0;
							$nuevodoc->DocAuthDp = 0;
							$nuevodoc->DocAnexo = 1;
							$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
							$nuevodoc->DocEspValue = 1;
							// return $nuevodoc;

							break;

						/*Alquilado*/
						case '2':
							$nuevodoc = new Documento;
							$nuevodoc->DocType = 0;
							$nuevodoc->DocNumero = 2;
							$nuevodoc->DocEspName = 2;
							$nuevodoc->DocEspValue = 2;
							$nuevodoc->DocObservacion = "documento con la sede del generador";
							$nuevodoc->DocSlug = hash('sha256', rand().time());
							$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
							$nuevodoc->DocNumRm = 2;
							$nuevodoc->DocAuthHseq = 0;
							$nuevodoc->DocAuthJl = 0;
							$nuevodoc->DocAuthDp = 0;
							$nuevodoc->DocAnexo = 2;
							$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
							$nuevodoc->DocEspValue = 2;
							// return $nuevodoc;

							break;

						default:
							return "no encontro el tipo de servicio";
							break;
					}
						$nuevodoc->save();

					foreach ($generadoresdelasolicitud as $sole) {
						foreach ($sole->resgener as $resgener) {
							foreach ($resgener->solres as $key) {
								$nuevodocdato = new Docdato;
								$nuevodocdato->FK_DatoDoc = $nuevodoc->ID_Doc;
								$nuevodocdato->FK_DatoSolRes = $key->ID_SolRes;
								$nuevodocdato->save();
							}
						}
					}
					$puntoderecoleccion = new Recolect;
					$puntoderecoleccion->FK_ColectProg = $programacion->ID_ProgVeh;
					$puntoderecoleccion->save();



					// return $puntoderecoleccion;
				break;

			/*recolectar en direccion especifica*/
			case '97':
				switch ($programacion->ProgVehtipo) {
					/*externo*/
					case '0':
						$nuevodoc = new Documento;
						$nuevodoc->DocType = 0;
						$nuevodoc->DocNumero = 0;
						$nuevodoc->DocEspName = 0;
						$nuevodoc->DocEspValue = 0;
						$nuevodoc->DocObservacion = "no deberia generar documento";
						$nuevodoc->DocSlug = hash('sha256', rand().time());
						$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
						$nuevodoc->DocNumRm = 0;
						$nuevodoc->DocAuthHseq = 0;
						$nuevodoc->DocAuthJl = 0;
						$nuevodoc->DocAuthDp = 0;
						$nuevodoc->DocAnexo = 0;
						$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
						$nuevodoc->DocEspValue = 0;
						// return $nuevodoc;

						break;

					/*Prosarc*/
					case '1':
						$nuevodoc = new Documento;
						$nuevodoc->DocType = 0;
						$nuevodoc->DocNumero = 1;
						$nuevodoc->DocEspName = 1;
						$nuevodoc->DocEspValue = 1;
						$nuevodoc->DocObservacion = "documento con la direccion especifica";
						$nuevodoc->DocSlug = hash('sha256', rand().time());
						$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
						$nuevodoc->DocNumRm = 1;
						$nuevodoc->DocAuthHseq = 0;
						$nuevodoc->DocAuthJl = 0;
						$nuevodoc->DocAuthDp = 0;
						$nuevodoc->DocAnexo = 1;
						$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
						$nuevodoc->DocEspValue = 1;
						// return $nuevodoc;

						break;

					/*Alquilado*/
					case '2':
						$nuevodoc = new Documento;
						$nuevodoc->DocType = 0;
						$nuevodoc->DocNumero = 2;
						$nuevodoc->DocEspName = 2;
						$nuevodoc->DocEspValue = 2;
						$nuevodoc->DocObservacion = "documento con la direccion especifica";
						$nuevodoc->DocSlug = hash('sha256', rand().time());
						$nuevodoc->DocSrc = $nuevodoc->DocSlug.'.pdf';
						$nuevodoc->DocNumRm = 2;
						$nuevodoc->DocAuthHseq = 0;
						$nuevodoc->DocAuthJl = 0;
						$nuevodoc->DocAuthDp = 0;
						$nuevodoc->DocAnexo = 2;
						$nuevodoc->FK_CertSolser = $SolicitudServicio->ID_SolSer;
						$nuevodoc->DocEspValue = 2;
						// return $nuevodoc;

						break;

					default:
						return "no encontro el tipo de servicio";
						break;
				}
					$nuevodoc->save();

				foreach ($generadoresdelasolicitud as $sole) {
					foreach ($sole->resgener as $resgener) {
						foreach ($resgener->solres as $key) {
							$nuevodocdato = new Docdato;
							$nuevodocdato->FK_DatoDoc = $nuevodoc->ID_Doc;
							$nuevodocdato->FK_DatoSolRes = $key->ID_SolRes;
							$nuevodocdato->save();
						}
					}
				}
				$puntoderecoleccion = new Recolect;
				$puntoderecoleccion->FK_ColectProg = $programacion->ID_ProgVeh;
				$puntoderecoleccion->save();
				break;

			default:
				// return "el tipo de servicio es externo";
				break;
		}

        $SolicitudServicio->SolSerStatus = 'Programado';
        if(!is_null($request->input('typetransportador'))){
            $SolicitudServicio->SolSerConductor = $nomConduct;
            $SolicitudServicio->SolSerVehiculo = $vehiculo;
            $SolicitudServicio->SolSerNameTrans = $transportador->CliName ?? null;
            $SolicitudServicio->SolSerNitTrans = $transportador->CliNit ?? null;
            $SolicitudServicio->SolSerAdressTrans = $transportador->SedeAddress ?? null;
            $SolicitudServicio->SolSerCityTrans = $transportador->ID_Mun ?? null;
        }
		$SolicitudServicio->save();

		// return redirect()->route('vehicle-programacion.create');
		//return redirect()->route('vehicle-programacion.edit' , ['id' => $programacion->ID_ProgVeh]);
		return redirect()->route('vehicle-programacion.edit', ['vehicle_programacion' => $programacion->ID_ProgVeh]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$Programacion = ProgramacionVehiculo::with('puntosderecoleccion')
		->where('ID_ProgVeh', $id)
		->first();
		// return $Programacion;
		if (!$Programacion) {
			abort(404, 'La programación de servicio no existe');
		}
		$SolicitudServicio = DB::table('solicitud_servicios')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
			->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
			->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
			->select('solicitud_servicios.*','personals.PersFirstName','personals.PersLastName', 'personals.PersEmail', 'personals.PersCellphone', 'personals.PersPhoneNumber', 'cargos.CargName', 'areas.AreaName')
			->where('solicitud_servicios.ID_SolSer', $Programacion->FK_ProgServi)
			->first();
		if (!$SolicitudServicio) {
			abort(404, 'La solicitud de servicio no existe');
		}
		$SolSerCollectAddress = $SolicitudServicio->SolSerCollectAddress;
		$SolSerConductor = $SolicitudServicio->SolSerConductor;
		// if($SolicitudServicio->SolSerTipo == 'Interno'){
		// 	$SolSerConductor = Personal::where('ID_Pers', $SolicitudServicio->SolSerConductor)->first();
		// }
		if($SolicitudServicio->SolSerTypeCollect == 98){
			$Address = Sede::select('SedeAddress')->where('ID_Sede',$SolicitudServicio->SolSerCollectAddress)->first();
			$SolSerCollectAddress = $Address->SedeAddress;
		}
		if($SolicitudServicio->SolSerCityTrans <> null){
			$Municipio1 = DB::table('municipios')
				->select('MunName')
				->where('ID_Mun', $SolicitudServicio->SolSerCityTrans)
				->first();
			$Municipio = $Municipio1->MunName;
		}
		if($SolicitudServicio->FK_SolSerCollectMun <> null){
			$Municipio2 = DB::table('municipios')
				->join('departamentos', 'municipios.FK_MunCity', '=', 'departamentos.ID_Depart')
				->select('municipios.MunName', 'departamentos.DepartName')
				->where('municipios.ID_Mun', $SolicitudServicio->FK_SolSerCollectMun)
				->first();
			$SolSerCollectAddress = $SolSerCollectAddress." (".$Municipio2->MunName." - ".$Municipio2->DepartName.")";
		}
		$TextProgramacion = null;
		if($SolicitudServicio->SolSerStatus == 'Programado'){
			setlocale(LC_ALL, "es_CO.UTF-8");
			// $Programacion = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->first();
			if(date('H', strtotime($Programacion->ProgVehSalida)) >= 12){
				$horas = " en las horas de la tarde";
			}
			else{
				$horas = " en las horas de la mañana";
			}
			$TextProgramacion = "El día ".strftime("%d", strtotime($Programacion->ProgVehFecha))." del mes de ".strftime("%B", strtotime($Programacion->ProgVehFecha)).$horas;
			$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
			// ->where('ProgVehEntrada', null)
			->where('ProgVehDelete', 0)
			->get();
			$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
			->where('ProgVehEntrada', null)
			->where('ProgVehDelete', 0)
			->get());
			// $ProgramacionesActivas = ($Programaciones);
		}
		$Cliente = DB::table('clientes')
			->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
			->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'sedes.SedePhone1', 'sedes.SedeExt1', 'sedes.SedePhone2', 'sedes.SedeExt2', 'municipios.MunName')
			->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
			->first();
		/*puntos de recoleccion de la solicitud array de ID_Gsede*/
		$puntos = $Programacion->puntosderecoleccion->map(function ($item) {
		  	return $item->ID_GSede;
		});
		$GenerResiduos = DB::table('solicitud_residuos')
			->distinct()
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			->join('municipios', 'gener_sedes.FK_GSedeMun', '=', 'municipios.ID_Mun')
			->select('gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.GenerName','gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress', 'municipios.MunName')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->whereIn('gener_sedes.ID_GSede', $puntos)
			->get();
		// $Residuos = DB::table('solicitud_residuos')
		// 	->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
		// 	->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
		// 	->select('solicitud_residuos.*','residuos_geners.FK_SGener', 'respels.RespelName','respels.RespelSlug', 'respels.RespelStatus')
		// 	->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
		// 	->get();
		$Residuosoriginal = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->join('requerimientos' , 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
			->join('tratamientos' , 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
			->join('sedes' , 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
			->join('clientes' , 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
			->select('solicitud_residuos.*','residuos_geners.FK_SGener', 'respels.*', 'requerimientos.ID_Req', 'tratamientos.TratName', 'clientes.CliName')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->whereIn('residuos_geners.FK_SGener', $puntos)
			// ->where('requerimientos.ofertado', 1)
	        // ->where('forevaluation', 0)
			->get();
		$Observaciones = Observacion::where('FK_ObsSolSer', $SolicitudServicio->ID_SolSer)
			->first();	
		$Residuos = $Residuosoriginal->map(function ($item) {
		  $requerimientos = Requerimiento::with(['pretratamientosSelected'])
	        ->where('ID_Req', $item->FK_SolResRequerimiento)
	        // ->where('forevaluation', 0)
	        ->first();

	        $item->pretratamientosSelected = $requerimientos->pretratamientosSelected;
		  	return $item;
		});
		return view('documentos.ManifCarga2', compact('SolicitudServicio','Observaciones', 'Residuos', 'GenerResiduos', 'Cliente', 'SolSerCollectAddress', 'SolSerConductor', 'TextProgramacion', 'Programacion', 'Municipio'));
	}

/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $programacion->FK_ProgServi
	 * @return \Illuminate\Http\Response
	 */
/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $programacion->FK_ProgServi
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
	if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2)){

			$programacion = ProgramacionVehiculo::where('ID_ProgVeh', $id)->with('servicio')->first();
			// Normaliza precintos para que siempre sea array en la vista
			if ($programacion && !is_null($programacion->ProgVehPrecintos)) {
				if (is_string($programacion->ProgVehPrecintos)) {
					$decodedPrecintos = json_decode($programacion->ProgVehPrecintos, true);
					if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPrecintos)) {
						$programacion->ProgVehPrecintos = $decodedPrecintos;
					} else {
						$programacion->ProgVehPrecintos = [$programacion->ProgVehPrecintos];
					}
				}
			}
			if (!$programacion) {
				abort(404);
			}
			$vehiculos = DB::table('vehiculos')
				->select('ID_Vehic','VehicPlaca')
				->where('VehicDelete', 0)
				->get();
			if($programacion->ProgVehtipo <> 0){
				$SedeVehiculo = DB::table('sedes')
					->join('vehiculos', 'sedes.ID_Sede', '=', 'vehiculos.FK_VehiSede')
					->select('sedes.ID_Sede')
					->where('vehiculos.ID_Vehic', $programacion->FK_ProgVehiculo)
					->first();
					$Vehiculos2 = $SedeVehiculo 
					? DB::table('vehiculos')
						->select('VehicPlaca', 'ID_Vehic')
						->where('FK_VehiSede', $SedeVehiculo->ID_Sede)
						->where('VehicDelete', 0)
						->get()
					: collect();
				} elseif ($programacion->ProgVehtipo == 2) { // Vehículo alquilado
					// Para vehículos alquilados no se relaciona directamente la sede,
					// así que se obtiene el vehículo por su ID
					$Vehiculos2 = DB::table('vehiculos')
						->select('VehicPlaca', 'ID_Vehic')
						->where('ID_Vehic', $programacion->FK_ProgVehiculo)
						->where('VehicDelete', 0)
						->get();
					$SedeVehiculo = null;
				} else {
					$SedeVehiculo = null;
					$Vehiculos2 = collect();
				}
			/*conductores de prosarc*/
			$conductors = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName')
				->where('CargName', 'Conductor')
				->where('ID_Cli', 1)
				->where('PersDelete', '!=' , 1)
				->get();
			/*auxiliares de prosarc*/
			$ayudantes = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName', 'PersDocNumber')
				->whereIn('AreaName', ['Operaciones', 'Logística', 'Mantenimiento'])
				->whereNotIn('CargName', ["Asistente", 'Jefe'])
				->where('ID_Cli', 1)
				->get();

			$personalconparafiscales = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName', 'PersDocNumber', 'PersParafiscales', 'PersParafiscalesExpire', 'AreaName')
				->whereIn('AreaName', ['Operaciones', 'Logística', 'Mantenimiento'])
				->whereNotIn('CargName', ["Asistente", 'Jefe'])
				->where('ID_Cli', 1)
				->where('PersParafiscales' , '!=', null)
				->get();

			$transportadores = DB::table('clientes')
				->select('CliName', 'CliSlug')
				->where('CliCategoria', 'Transportador')
				->where('CliDelete', 0)
				->get();

			$serviciovalidado = $programacion->FK_ProgServi;
			/*cuenta los diferentes generadores*/
			$generadoresdelasolicitud = GenerSede::whereHas('resgener.solres', function ($query) use ($serviciovalidado) {
			    $query->where('solicitud_residuos.FK_SolResSolSer', $serviciovalidado);
			})
			// ->with(['resgener' => function ($query) use ($serviciovalidado){
			//     $query->with(['solres' => function ($query) use ($serviciovalidado){
			//     	$query->where('FK_SolResSolSer', $serviciovalidado);
			//     }]);
			//     $query->whereHas('solres', function ($query) use ($serviciovalidado){
			//     	$query->where('FK_SolResSolSer', $serviciovalidado);
			//     });
			// }])
			->get('ID_GSede');

			/*se crea array de las sedes de generador en la solicitud de servicio*/
			$sedesgenerfiltrado = collect([]);
			foreach ($generadoresdelasolicitud as $key => $value) {
				$sedesgenerfiltrado = $sedesgenerfiltrado->concat([$value->ID_GSede]);
			}
			$recolectPointsService = GenerSede::with('generadors')
				->whereIn('ID_GSede', $sedesgenerfiltrado)
				->get();

			// return $recolectPointsService;

			$recolectPointsProg = Recolect::with('sedegen.generadors')
				->where('FK_ColectProg', $programacion->ID_ProgVeh)
				->get();

			/*return $programacion*/;

			return view('ProgramacionVehicle.edit', compact('programacion', 'vehiculos', 'conductors', 'ayudantes', 'Vehiculos2', 'transportadores', 'recolectPointsService', 'recolectPointsProg', 'personalconparafiscales'));
		}
		 /*Validacion para usuarios no permitidos en esta vista*/
		else{
			abort(403);
		}
	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$validate = $request->validate([
			// 'ProgVehPrecintos'   =>   'max:16|min:6'
		]);

		// return $request->input('ProgVehPrecintos');
		$programacion = ProgramacionVehiculo::where('ID_ProgVeh', $id)->first();
		if (!$programacion) {
			abort(404);
		}
		// return $request;
		$programacion->ProgVehFecha = $request->input('ProgVehFecha');
		$programacion->ProgVehExclusive = $request->input('ProgVehExclusive');
		$salida = date('H:i:s', strtotime($request->input('ProgVehSalida')));
		$llegada = date('H:i:s', strtotime($request->input('ProgVehEntrada')));
		if($salida >= 12){
			$turno = "0";
		}
		else{
			$turno = "1";
		}
		$programacion->ProgVehTurno = $turno;
		$programacion->ProgVehSalida = $request->input('ProgVehFecha').' '.$salida;
		if($programacion->ProgVehtipo == 1){
			if($request->input('ProgVehEntrada')){
				$programacion->ProgVehEntrada = $request->input('ProgVehFecha').' '.$llegada;
				$programacion->progVehKm = $request->input('progVehKm');



				$programacion->ProgVehStatus = 'Cerrada';/*se cierra la programacion del vehiculo*/

				$vehiculo = Vehiculo::where('ID_Vehic', $request->input('FK_ProgVehiculo'))->first();
				$vehiculo->VehicKmActual = $request->input('progVehKm');
				$vehiculo->save();

			}
			else{
				$programacion->ProgVehEntrada = null;
				$programacion->progVehKm = null;
			}
			$conductor = Personal::select('PersFirstName', 'PersLastName')->where('ID_Pers', $request->input('FK_ProgConductor'))->first();
			$nomConduct = $conductor->PersFirstName." ".$conductor->PersLastName;
			$vehiculo = Vehiculo::select('VehicPlaca')->where('ID_Vehic', $request->input('FK_ProgVehiculo'))->first()->VehicPlaca;
			$programacion->FK_ProgVehiculo = $request->input('FK_ProgVehiculo');
			$programacion->FK_ProgConductor = $request->input('FK_ProgConductor');
			$programacion->FK_ProgAyudante = $request->input('FK_ProgAyudante');


			$programacion->ProgVehPrecintos = $request->input('ProgVehPrecintos');


			$programacion->ProgVehColor = $request->input('ProgVehColor');
		}
		else if($programacion->ProgVehtipo == 2){
			if($request->input('ProgVehEntrada')){
				$programacion->ProgVehEntrada = $request->input('ProgVehFecha').' '.$llegada;
				$programacion->ProgVehStatus = 'Cerrada';/*se cierra la programacion del vehiculo*/
			}
			$programacion->ProgVehDocConductorEXT = $request->input('ProgVehDocConductorEXT');
			$programacion->ProgVehNameConductorEXT = $request->input('ProgVehNameConductorEXT');
			$programacion->ProgVehDocAuxiliarEXT = $request->input('ProgVehDocAuxiliarEXT');
			$programacion->ProgVehNameAuxiliarEXT = $request->input('ProgVehNameAuxiliarEXT');
			$programacion->ProgVehPlacaEXT = $request->input('ProgVehPlacaEXT');
			$programacion->ProgVehTipoEXT = $request->input('ProgVehTipoEXT');
			$programacion->FK_ProgAyudante = $request->input('FK_ProgAyudante');
			$programacion->FK_ProgVehiculo = $request->input('vehicalqui');

			$programacion->ProgVehPrecintos = $request->input('ProgVehPrecintos');

			// Usar SIEMPRE el NOMBRE del conductor externo para reflejarlo en la Solicitud de Servicio
			$nomConduct = $programacion->ProgVehNameConductorEXT;
			$vehiculo = $programacion->ProgVehPlacaEXT;
		}
		else{
			if($request->input('ProgVehEntrada')){
				$programacion->ProgVehEntrada = $request->input('ProgVehFecha').' '.$llegada;
				$programacion->ProgVehStatus = 'Cerrada';/*se cierra la programacion del vehiculo*/
			}
			$programacion->FK_ProgVehiculo = $request->input('vehicalqui');
			$programacion->FK_ProgAyudante = $request->input('FK_ProgAyudante');
			// $programacion->ProgVehPrecintos = $request->input('ProgVehPrecintos');

			// $vehiculo = Vehiculo::select('VehicPlaca')->where('ID_Vehic', $request->input('vehicalqui'))->first()->VehicPlaca;
			$nomConduct = null;
		}
		$programacion->update();
		// return $request->input('ProgGenerSedes');
		$programacion->puntosderecoleccion()->sync($request->input('ProgGenerSedes'));

		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();
		// $SolicitudServicio->SolSerStatus = 'Programado';
		if($programacion->ProgVehtipo <> 0){
			$SolicitudServicio->SolSerConductor = $nomConduct;
			$SolicitudServicio->SolSerVehiculo = $vehiculo;
		}
		$SolicitudServicio->save();

		// if ($programacion->ProgVehStatus == 'Cerrada') {
		// 	$destinatarios = ['recepcionpda@prosarc.com.co'];
		// 	Mail::to($destinatarios)->send(new VehiculoRecibidoEmail($SolicitudServicio));
		// }

		$log = new audit();
		$log->AuditTabla="progvehiculos";
		$log->AuditType="Modificado";
		$log->AuditRegistro=$programacion->ID_ProgVeh;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$request->all();
		$log->save();
		return redirect()->route('vehicle-programacion.edit',['vehicle_programacion' => $id])->with('mensaje', __('adminlte::message.progvehceditsuccess'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$programacion = ProgramacionVehiculo::where('ID_ProgVeh', $id)->first();
		if (!$programacion) {
			abort(404, 'la programación de vehículo que trata de eliminar no se encuentra en la base de datos');
		}
		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();
		switch ($SolicitudServicio->SolSerStatus) {
			case 'Aprobado':
			case 'Programado':
				if ($programacion->ProgVehDelete == 0){
					$programacion->ProgVehDelete = 1;
					$programacion->save();
					$programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->where('ID_ProgVeh', '<>', $programacion->ID_ProgVeh)->first();
					if($programaciones == null && ($SolicitudServicio->SolSerStatus == 'Programado')){
						$SolicitudServicio->SolSerStatus = 'Aprobado';
						if($SolicitudServicio->SolSerTipo == 'Interno'){
							$transportador = DB::table('clientes')
								->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
								->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
								->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName',  'municipios.ID_Mun')
								->where('ID_Cli', 1)
								->first();
							$SolicitudServicio->SolSerConductor = null;
							$SolicitudServicio->SolSerVehiculo = null;
							$SolicitudServicio->SolSerNameTrans = $transportador->CliName;
							$SolicitudServicio->SolSerNitTrans = $transportador->CliNit;
							$SolicitudServicio->SolSerAdressTrans = $transportador->SedeAddress;
							$SolicitudServicio->SolSerCityTrans = $transportador->ID_Mun;
						}
						$SolicitudServicio->save();

						/*inicio de espacio para notificacion de programacion cancelada*/
						$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();

						$emailCliente = DB::table('solicitud_servicios')
						->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
						->select('personals.PersEmail', 'solicitud_servicios.*')
						->where('solicitud_servicios.SolSerSlug', '=', $SolicitudServicio->SolSerSlug)
						->first();


						// se establece la lista de destinatarios
						if ($SolicitudServicio['cliente']->CliComercial <> null) {
							$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
							$destinatarios = [/*'dirtecnica@prosarc.com.co',*/
												'asistentelogistica@prosarc.com.co',
												'auxiliarlogistico@prosarc.com.co',
												'recepcionpda@prosarc.com.co',
												'gerenteplanta@prosarc.com.co',
												$emailCliente->PersEmail,
												$comercial->PersEmail
											];
						}else{
							$comercial = "";
							$destinatarios = ['dirtecnica@prosarc.com.co',
												'asistentelogistica@prosarc.com.co',
												'gerenteplanta@prosarc.com.co',
												'recepcionpda@prosarc.com.co',
												$emailCliente->PersEmail
											];
						}

						$SolicitudServicio['comercial'] = $comercial;
						$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();

						if ($SolicitudServicio->SolServMailCopia == "null") {
							Mail::to($destinatarios)
							->send(new CancelSolServEmail($SolicitudServicio));
						}else{
							Mail::to($destinatarios)
							->cc(json_decode($SolicitudServicio->SolServMailCopia))
							->send(new CancelSolServEmail($SolicitudServicio));
						}

						/*fin de espacio para notificacion de programacion cancelada*/

					}

					$log = new audit();
					$log->AuditTabla = "progvehiculos";
					$log->AuditType = "Eliminado";
					$log->AuditRegistro = $programacion->ID_ProgVeh;
					$log->AuditUser = Auth::user()->email;
					$log->Auditlog = $programacion->ProgVehDelete;
					$log->save();
					return redirect()->route('vehicle-programacion.create')->with('Delete', __('adminlte::message.progvehcdeletesuccess'));
				}
				else{
					$programacion->ProgVehDelete = 0;
					if($SolicitudServicio->SolSerStatus == 'Aprobado'){
						$SolicitudServicio->SolSerStatus = 'Programado';
						if($SolicitudServicio->SolSerTipo == 'Interno'){
							$SolicitudServicio->SolSerConductor = $programacion->FK_ProgConductor;
							$SolicitudServicio->SolSerVehiculo = $programacion->FK_ProgVehiculo;
						}
						$SolicitudServicio->save();
					}

					$log = new audit();
					$log->AuditTabla = "progvehiculos";
					$log->AuditType = "Restaurado";
					$log->AuditRegistro = $programacion->ID_ProgVeh;
					$log->AuditUser = Auth::user()->email;
					$log->Auditlog = $programacion->ProgVehDelete;
					$log->save();
					$programacion->save();
					return redirect()->route('vehicle-programacion.edit',['id' => $id])->with('mensaje', __('adminlte::message.progvehcdelete2success'));
				}
			break;

			case 'Notificado':
				if ($programacion->ProgVehDelete == 0){
					$programacion->ProgVehDelete = 1;
					$programacion->save();
					$programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->where('ID_ProgVeh', '<>', $programacion->ID_ProgVeh)->first();
					if($programaciones == null && ($SolicitudServicio->SolSerStatus == 'Notificado')){
						$SolicitudServicio->SolSerStatus = 'Aprobado';
						if($SolicitudServicio->SolSerTipo == 'Interno'){
							$transportador = DB::table('clientes')
								->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
								->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
								->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName',  'municipios.ID_Mun')
								->where('ID_Cli', 1)
								->first();
							$SolicitudServicio->SolSerConductor = null;
							$SolicitudServicio->SolSerVehiculo = null;
							$SolicitudServicio->SolSerNameTrans = $transportador->CliName;
							$SolicitudServicio->SolSerNitTrans = $transportador->CliNit;
							$SolicitudServicio->SolSerAdressTrans = $transportador->SedeAddress;
							$SolicitudServicio->SolSerCityTrans = $transportador->ID_Mun;
						}
						$SolicitudServicio->save();

						/*inicio de espacio para notificacion de programacion cancelada*/
						$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();

						$emailCliente = DB::table('solicitud_servicios')
						->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
						->select('personals.PersEmail', 'solicitud_servicios.*')
						->where('solicitud_servicios.SolSerSlug', '=', $SolicitudServicio->SolSerSlug)
						->first();


						// se establece la lista de destinatarios
						if ($SolicitudServicio['cliente']->CliComercial <> null) {
							$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
							$destinatarios = ['dirtecnica@prosarc.com.co',
												'asistentelogistica@prosarc.com.co',
												'recepcionpda@prosarc.com.co',
												'gerenteplanta@prosarc.com.co',
												$emailCliente->PersEmail,
												$comercial->PersEmail
											];
						}else{
							$comercial = "";
							$destinatarios = ['dirtecnica@prosarc.com.co',
												'asistentelogistica@prosarc.com.co',
												'gerenteplanta@prosarc.com.co',
												'recepcionpda@prosarc.com.co',
												$emailCliente->PersEmail
											];
						}

						$SolicitudServicio['comercial'] = $comercial;
						$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();

						if ($SolicitudServicio->SolServMailCopia == "null") {
							Mail::to($destinatarios)
							->send(new CancelSolServEmail($SolicitudServicio));
						}else{
							Mail::to($destinatarios)
							->cc(json_decode($SolicitudServicio->SolServMailCopia))
							->send(new CancelSolServEmail($SolicitudServicio));
						}

						/*fin de espacio para notificacion de programacion cancelada*/

					}

					$log = new audit();
					$log->AuditTabla = "progvehiculos";
					$log->AuditType = "Eliminado";
					$log->AuditRegistro = $programacion->ID_ProgVeh;
					$log->AuditUser = Auth::user()->email;
					$log->Auditlog = $programacion->ProgVehDelete;
					$log->save();

					/*se guarda la observacion de la modificación del servicio hacia status aprobado*/
					$Observacion = new Observacion();
					$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
					$Observacion->ObsMensaje = 'Todas las programaciones de vehículos relativas a la Solicitud de Servicio N° '.$SolicitudServicio->ID_SolSer.' han sido canceladas por el área de Logistica... dado que la solicitud de servicio vuelve al status de Aprobado, el cliente puede nuevamente eliminar o editar la solicitud de servicio según necesite.';
					$Observacion->ObsTipo = 'prosarc';
					$Observacion->ObsRepeat = 1;
					$Observacion->ObsDate = now();
					$Observacion->ObsUser = Auth::user()->email;
					$Observacion->ObsRol = Auth::user()->UsRol;
					$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
					$Observacion->save();

					return redirect()->route('vehicle-programacion.create')->with('Delete', __('adminlte::message.progvehcdeletesuccess'));
				}
				else{
					$programacion->ProgVehDelete = 0;
					$programacion->save();

					if($SolicitudServicio->SolSerTipo == 'Interno'){
						$SolicitudServicio->SolSerConductor = $programacion->FK_ProgConductor;
						$SolicitudServicio->SolSerVehiculo = $programacion->FK_ProgVehiculo;
						$SolicitudServicio->save();
					}

					$log = new audit();
					$log->AuditTabla = "progvehiculos";
					$log->AuditType = "Restaurado";
					$log->AuditRegistro = $programacion->ID_ProgVeh;
					$log->AuditUser = Auth::user()->email;
					$log->Auditlog = $programacion->ProgVehDelete;
					$log->save();

					return redirect()->route('vehicle-programacion.edit',['id' => $id])->with('mensaje', __('adminlte::message.progvehcdelete2success'));
				}
			break;

			default:
			abort(503, 'No se puede eliminar la programación de vehículo ya que la solicitud de servicio fue completada');
				break;
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function updateStatus(Request $request, $id)
	{
		$programacion = ProgramacionVehiculo::where('ID_ProgVeh', $id)->first();
		if (!$programacion) {
			abort(404, 'la programación de vehículo que trata de actualizar no se encuentra en la base de datos');
		}
		$SolicitudServicio = SolicitudServicio::with(['SolicitudResiduo.requerimiento.respel'])
		->where('ID_SolSer', $programacion->FK_ProgServi)->first();
		$programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
		->where('ProgVehDelete', 0)
		->get();

		$SolicitudServicio->SolSerStatus='Notificado';
		$SolicitudServicio->SolSerDescript=$request->input('solserdescript');
		$SolicitudServicio->save();

		// $residuosdelservicio = $SolicitudServicio->SolicitudResiduo()->requerimiento()->respel()->get();

		$cantidadDeResiduosControlados = 0;

		foreach ($SolicitudServicio->SolicitudResiduo as $key => $value) {
			$respel = $value->requerimiento->respel;
			if ($respel->SustanciaControlada == 1) {
				$cantidadDeResiduosControlados++;
			}
		}

		$cantidadDeAceitesUsados = 0;

		$programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
		->where('ProgVehDelete', 0)
		//->where('FK_ProgVehiculo', 8)
		->first();
		
		foreach ($SolicitudServicio->SolicitudResiduo as $key => $value){
			$respel = $value->requerimiento->respel;
			
			if($respel->AceiteUsado == 1 && $programaciones->FK_ProgVehiculo == 8){
				$cantidadDeAceitesUsados++;
				}
			}	

		// return $cantidadDeResiduosControlados;

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="Notificado";
		$log->AuditRegistro=$SolicitudServicio->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$SolicitudServicio->SolSerStatus;
		$log->save();

		/*se guarda la observacion inicial de la creación del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
		$Observacion->ObsMensaje = $SolicitudServicio->SolSerDescript;
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
		$Observacion->save();

		$email = DB::table('solicitud_servicios')
			->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->select('personals.*', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
			->where('solicitud_servicios.SolSerSlug', '=', $SolicitudServicio->SolSerSlug)
			->where('progvehiculos.FK_ProgServi', '=', $SolicitudServicio->ID_SolSer)
			->where('progvehiculos.ProgVehDelete', 0)
			->first();
		$comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
		$destinatarios = ['asistentelogistica@prosarc.com.co',
							'recepcionpda@prosarc.com.co',
							'asistentepda@prosarc.com.co',
							$comercial->PersEmail
						];
		if ($cantidadDeResiduosControlados > 0) {
			//enviar notificacion de servicion con sustancia controladas
			Mail::to('dirtecnica@prosarc.com.co')->cc(['sistemas@prosarc.com.co','asistentepda@prosarc.com.co', 'logistica@prosarc.com.co', 'recepcionpda@prosarc.com.co', 'jefedetratamiento@prosarc.com.co', 'asistentelogistica@prosarc.com.co', 'auxiliarlogistico@prosarc.com.co', 'conciliaciones@prosarc.com.co'])->send(new SustanciaControladaProgramada($email, $SolicitudServicio));
		}else{
			array_push($destinatarios, 'dirtecnica@prosarc.com.co');
		}

		if($cantidadDeAceitesUsados > 0){
			//enviar notificacion de servicion con Aceite Usado
			Mail::to('dirtecnica@prosarc.com.co')->cc(['sistemas@prosarc.com.co', 'logistica@prosarc.com.co', 'asistentelogistica@prosarc.com.co', 'auxiliarlogistico@prosarc.com.co', ])->send(new AceiteUsadoProgramado($email, $SolicitudServicio));
		}else{
			array_push($destinatarios, 'dirtecnica@prosarc.com.co');
		}
		if ($SolicitudServicio->SolServMailCopia == "null") {
			if ($programacion->ProgVehExclusive == 0) {
				Mail::to($email->PersEmail)->cc($destinatarios)->send(new ServicioTipoRecorrido($email));
			}else{
				Mail::to($email->PersEmail)->cc($destinatarios)->send(new SolSerEmail($email));
			}

		}else{
			foreach (json_decode($SolicitudServicio->SolServMailCopia) as $key => $value) {
				array_push($destinatarios, $value);
			}
			if ($programacion->ProgVehExclusive == 0) {
				Mail::to($email->PersEmail)->cc($destinatarios)->send(new ServicioTipoRecorrido($email));
			}else{
				Mail::to($email->PersEmail)->cc($destinatarios)->send(new SolSerEmail($email));
			}
		}

		if($request->input('destino') == 'vehiprog-edit'){
			return redirect()->route('vehicle-programacion.edit', ['vehicle_programacion' => $id]);
			//return redirect()->route('vehicle-programacion.edit', ['id' => $id]);
		}else{
			return redirect()->route('vehicle-programacion.index');
		}


	}

		/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function añadirVehiculo(Request $request, $id)
	{
		// return $request;
		$programacion = new ProgramacionVehiculo();
		if(date('H', strtotime($request->input('ProgVehSalida'))) >= 12){
			$turno = "0";
		}
		else{
			$turno = "1";
		}
		$programacion->ProgVehTurno = $turno;
		$programacion->ProgVehFecha = $request->input('ProgVehFecha');
		$programacion->ProgVehSalida = $request->input('ProgVehFecha').' '.date('H:i:s', strtotime($request->input('ProgVehSalida')));
		/*typetransportador = 0 -> transporte prosarc*/
		/*typetransportador = 1 -> transporte alquilado*/
		if(!is_null($request->input('typetransportador'))){
			if($request->input('typetransportador') == 0){
				/*ProgVehtipo = 0 -> transporte externo*/
				/*ProgVehtipo = 1 -> transporte interno prosarc*/
				/*ProgVehtipo = 2 -> transporte alquilado*/

				$programacion->ProgVehtipo = 1;
				$programacion->FK_ProgVehiculo = $request->input('FK_ProgVehiculo');
				$programacion->ProgVehColor = $request->input('ProgVehColor');
				$programacion->FK_ProgConductor = $request->input('FK_ProgConductor');
				$programacion->FK_ProgAyudante = $request->input('FK_ProgAyudante');
				$conductor = Personal::select('PersFirstName', 'PersLastName')->where('ID_Pers', $request->input('FK_ProgConductor'))->first();
				$nomConduct = $conductor->PersFirstName." ".$conductor->PersLastName;
				$vehiculo = Vehiculo::select('VehicPlaca')->where('ID_Vehic', $request->input('FK_ProgVehiculo'))->first()->VehicPlaca;
				$transportador = DB::table('clientes')
					->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
					->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName', 'municipios.ID_Mun')
					->where('ID_Cli', 1)
					->first();
			}
			else{
				$programacion->ProgVehtipo = 2;
				$programacion->FK_ProgVehiculo = $request->input('vehicalqui');
				$programacion->FK_ProgAyudante = $request->input('FK_ProgAyudante');
				$programacion->ProgVehDocConductorEXT = $request->input('ProgVehDocConductorEXT');
				$programacion->ProgVehNameConductorEXT = $request->input('ProgVehNameConductorEXT');
				$programacion->ProgVehDocAuxiliarEXT = $request->input('ProgVehDocAuxiliarEXT');
				$programacion->ProgVehNameAuxiliarEXT = $request->input('ProgVehNameAuxiliarEXT');
				$programacion->ProgVehPlacaEXT = $request->input('ProgVehPlacaEXT');
				$programacion->ProgVehTipoEXT = $request->input('ProgVehTipoEXT');
				$programacion->ProgVehColor = '#FFFF00';
				if ($request->input('vehicalqui')!=null) {
					$vehiculo = Vehiculo::select('VehicPlaca')->where('ID_Vehic', $request->input('vehicalqui'))->first()->VehicPlaca;
				}else{
					$vehiculo = null;
				}

				$nomConduct = null;
				$transportador = DB::table('clientes')
					->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
					->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName', 'municipios.ID_Mun')
					->where('CliSlug', $request->input('transport'))
					->first();
			}
		}
		else{
			$nomConduct = null;
			$vehiculo = null;
			$programacion->ProgVehtipo = 0;
		}
		$programacion->FK_ProgServi = $id;
		$programacion->ProgVehDelete = 0;
		$programacion->ProgVehStatus =  $request->input('StatusProgServi');
		$programacion->ProgVehExclusive =  $request->input('ProgVehExclusive');
		$programacion->save();

		// $SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();
		// $SolicitudServicio->SolSerStatus = 'Programado';
		// if(!is_null($request->input('typetransportador'))){
		// 	$SolicitudServicio->SolSerConductor = $nomConduct;
		// 	$SolicitudServicio->SolSerVehiculo = $vehiculo;
		// 	$SolicitudServicio->SolSerNameTrans = $transportador->CliName;
		// 	$SolicitudServicio->SolSerNitTrans = $transportador->CliNit;
		// 	$SolicitudServicio->SolSerAdressTrans = $transportador->SedeAddress;
		// 	$SolicitudServicio->SolSerCityTrans = $transportador->ID_Mun;
		// }
		// $SolicitudServicio->save();

		return redirect()->route('vehicle-programacion.index');

	}

			/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function sendParafiscales(Request $request, $id)
	{
		$programacion = ProgramacionVehiculo::where('ID_ProgVeh', $id)->first();
		// return $request;
		if (!$programacion) {
			abort(404, 'la programación de vehículo que trata de notificar no se encuentra en la base de datos');
		}
		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();
		$SolicitudServicio->SolSerDescript=$request->input('solserdescript');
		$SolicitudServicio->save();

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="parafiscales";
		$log->AuditRegistro=$SolicitudServicio->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$SolicitudServicio->SolSerStatus;
		$log->save();

		/*se guarda la observacion inicial de la creación del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = 'Parafiscales Enviados';
		$Observacion->ObsMensaje = $SolicitudServicio->SolSerDescript;
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
		$Observacion->save();

		if($request->input('destino') == 'vehiprog-edit'){
			$email = DB::table('solicitud_servicios')
				->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
				->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
				->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
				->where('solicitud_servicios.SolSerSlug', '=', $SolicitudServicio->SolSerSlug)
				->where('progvehiculos.FK_ProgServi', '=', $SolicitudServicio->ID_SolSer)
				->where('progvehiculos.ProgVehDelete', 0)
				->first();
			$comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
			$destinatarios = ['asistentelogistica@prosarc.com.co',
								$comercial->PersEmail
							];

			$Parafiscales = [];

			foreach ($request->input('personalParafiscales') as $key => $value) {
				$pdf = Personal::where('ID_Pers', $value)->first('PersParafiscales');
				if ($pdf) {
					$Parafiscales = Arr::prepend($Parafiscales, $pdf->PersParafiscales);
				}
			}

			if ($SolicitudServicio->SolServMailCopia == "null") {
				Mail::to($email->PersEmail)
				->cc($destinatarios)
				->send(new ProgramacionParafiscales($email, $Observacion, $programacion, $Parafiscales));
			}else{
				foreach (json_decode($SolicitudServicio->SolServMailCopia) as $key => $value) {
					array_push($destinatarios, $value);
				}
				Mail::to($email->PersEmail)
				->cc($destinatarios)
				->send(new ProgramacionParafiscales($email, $Observacion, $programacion, $Parafiscales));
			}

			return redirect()->route('vehicle-programacion.edit', ['id' => $id]);
		}else{
			return redirect()->route('email-solser', ['slug' => $SolicitudServicio->SolSerSlug]);
		}
	}
    public function updateTransportador(Request $request, $id)
	{
		try {
			DB::beginTransaction();

			$programacion = ProgramacionVehiculo::findOrFail($id);
			
			// Validar que la programación existe
			if (!$programacion) {
				throw new \Exception('No se encontró la programación');
			}

			// Obtener el tipo de transportador seleccionado
			$tipoTransportador = $request->input('ProgVehtipo');

			if ($tipoTransportador == 1) { // Transportador interno (Prosarc)
				// Validar campos requeridos para Prosarc
				$request->validate([
					'FK_ProgVehiculo' => 'required',
					'FK_ProgConductor' => 'required'
				]);

				// Actualizar datos de transportador interno
				$programacion->ProgVehtipo = 1;
				$programacion->FK_ProgVehiculo = $request->FK_ProgVehiculo;
				$programacion->FK_ProgConductor = $request->FK_ProgConductor;
				$programacion->FK_ProgAyudante = $request->FK_ProgAyudante;
				
				// Limpiar campos de transportador alquilado
				$programacion->ProgVehDocConductorEXT = null;
				$programacion->ProgVehNameConductorEXT = null;
				$programacion->ProgVehDocAuxiliarEXT = null;
				$programacion->ProgVehNameAuxiliarEXT = null;
				$programacion->ProgVehPlacaEXT = null;
				$programacion->ProgVehTipoEXT = null;
				// Mantener FK_ProgAyudante ya que también se usa en Prosarc
				
				// Obtener datos del conductor y vehículo
				$conductor = Personal::select('PersFirstName', 'PersLastName')
					->where('ID_Pers', $request->FK_ProgConductor)
					->first();
				
				if (!$conductor) {
					throw new \Exception('No se encontró el conductor seleccionado');
				}
				
				$vehiculo = Vehiculo::select('VehicPlaca')
					->where('ID_Vehic', $request->FK_ProgVehiculo)
					->first();
				
				if (!$vehiculo) {
					throw new \Exception('No se encontró el vehículo seleccionado');
				}
				
				// Actualizar datos en SolicitudServicio
				$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();
				$SolicitudServicio->SolSerConductor = $conductor->PersFirstName . " " . $conductor->PersLastName;
				$SolicitudServicio->SolSerVehiculo = $vehiculo->VehicPlaca;
				$SolicitudServicio->save();

			} else if ($tipoTransportador == 2) { // Transportador alquilado
				// Para alquilado no validamos nada específico, solo guardamos los datos

				// Actualizar datos de transportador alquilado
				$programacion->ProgVehtipo = 2;
				$programacion->ProgVehDocConductorEXT = $request->ProgVehDocConductorEXT;
				$programacion->ProgVehNameConductorEXT = $request->ProgVehNameConductorEXT;
				$programacion->ProgVehDocAuxiliarEXT = $request->ProgVehDocAuxiliarEXT;
				$programacion->ProgVehNameAuxiliarEXT = $request->ProgVehNameAuxiliarEXT;
				$programacion->ProgVehPlacaEXT = $request->ProgVehPlacaEXT;
				$programacion->ProgVehTipoEXT = $request->ProgVehTipoEXT;
				$programacion->FK_ProgAyudante = $request->FK_ProgAyudante;
				
				// Limpiar campos de transportador Prosarc
				$programacion->FK_ProgVehiculo = null;
				$programacion->FK_ProgConductor = null;
				
				// Actualizar datos en SolicitudServicio
				$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $programacion->FK_ProgServi)->first();
				$SolicitudServicio->SolSerConductor = $request->ProgVehNameConductorEXT;
				$SolicitudServicio->SolSerVehiculo = $request->ProgVehPlacaEXT;
				$SolicitudServicio->save();
			}

			// Guardar los cambios
			$programacion->save();

			// Registrar la auditoría
			$log = new audit();
			$log->AuditTabla = "progvehiculos";
			$log->AuditType = "Modificado";
			$log->AuditRegistro = $programacion->ID_ProgVeh;
			$log->AuditUser = Auth::user()->email;
			$log->Auditlog = $request->all();
			$log->save();

			DB::commit();

			return redirect()->back()->with('mensaje', 'Transportador actualizado correctamente');

		} catch (\Exception $e) {
			DB::rollBack();
			return redirect()->back()->with('error', 'Error al actualizar el transportador: ' . $e->getMessage());
		}
	}

	/**
	 * Mostrar formulario preoperacional para conductores
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function preoperacional($id)
	{
		// Verificar que el usuario es conductor y que la programación le pertenece
		if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
			$vehiculosDisponibles = Vehiculo::select('ID_Vehic', 'VehicPlaca', 'VehicKmActual',
				'VehicTarjetaPropiedadVencimiento', 'VehicSoatVencimiento', 'VehicTecnomecanicaVencimiento', 'VehicPolizaVencimiento',
				'VehicExt1FechaRecarga', 'VehicExt2FechaRecarga')
				->where('VehicDelete', 0)
				// Solo vehículos Prosarc (sede 1)
				->where('FK_VehiSede', 1)
				->orderBy('VehicPlaca', 'asc')
				->get();

			// Ayudantes Prosarc (para permitir escoger/ajustar desde el formulario)
			$ayudantesDisponibles = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('personals.ID_Pers', 'personals.PersFirstName', 'personals.PersLastName')
				->whereIn('areas.AreaName', ['Operaciones', 'Logística', 'Mantenimiento'])
				->whereNotIn('cargos.CargName', ['Asistente', 'Jefe', 'Supervisor', 'Conductor'])
				->where('clientes.ID_Cli', 1)
				->where('personals.PersDelete', '!=', 1)
				->orderBy('personals.PersFirstName', 'asc')
				->orderBy('personals.PersLastName', 'asc')
				->get();

			// Modo prueba: permitir abrir formulario sin programación (id=0)
			if ((int)$id === 0) {
				$programacion = new ProgramacionVehiculo();
				$programacion->ID_ProgVeh = 0;
				$programacion->ProgVehFecha = Carbon::today()->toDateString();
				$programacion->ProgVehSalida = Carbon::now()->toDateTimeString();
				$programacion->ProgVehEntrada = null;
				$programacion->ProgVehCheckPreoperacional = null;
				$programacion->ProgVehObsPreoperacional = null;
				$programacion->ProgVehEstadoVehiculo = null;
				$programacion->ProgVehKmInicial = null;
				$programacion->ProgVehKmFinal = null;
				$programacion->ProgVehPreoperacionalCompletado = false;
				$programacion->ProgVehPdfPreoperacional = null;
				$programacion->SolSerVehiculo = '';
				$programacion->puntosderecoleccion = [];

				$vehiculo = null;
				$conductor = Personal::where('ID_Pers', Auth::user()->FK_UserPers)->first();
				$ayudante = null;

				// Opciones de ruta (fallback para pruebas)
				$ordenRutaOptions = GenerSede::with('generadors')
					->select('ID_GSede', 'FK_GSede', 'GSedeName', 'GSedeAddress', 'GSedeDelete')
					->where('GSedeDelete', 0)
					->orderBy('ID_GSede', 'desc')
					->limit(80)
					->get()
					->map(function ($s) {
						$genName = '';
						try { $genName = $s->generadors ? ($s->generadors->GenerName ?? '') : ''; } catch (\Exception $e) { $genName = ''; }
						$label = trim(($genName ? $genName.' - ' : '').($s->GSedeName ?? ''));
						return [
							'id' => $s->ID_GSede,
							'label' => $label ?: ('Sede #' . $s->ID_GSede),
							'direccion' => $s->GSedeAddress ?? '',
						];
					})
					->values();

				$prefillDocumentacion = [];
				$prefillEquipo = [];
				$prefillMercanciasPeligrosas = [];
				if ($conductor) {
					$licenciaVigente = !empty(trim($conductor->PersPase ?? ''));
					if (!$licenciaVigente) {
						$licenciaVigente = DB::table('training_personals')
							->join('trainings', 'training_personals.FK_Capa', '=', 'trainings.ID_Capa')
							->where('training_personals.FK_Pers', $conductor->ID_Pers)
							->where(function ($q) {
								$q->where('training_personals.CapaPersDelete', 0)->orWhereNull('training_personals.CapaPersDelete');
							})
							->where('training_personals.CapaPersExpire', '>=', Carbon::today())
							->where('trainings.CapaName', 'like', '%Licencia%')
							->exists();
					}
					if ($licenciaVigente) {
						$prefillDocumentacion['licencia'] = 'SI';
					}
					$certMpVigente = DB::table('training_personals')
						->join('trainings', 'training_personals.FK_Capa', '=', 'trainings.ID_Capa')
						->where('training_personals.FK_Pers', $conductor->ID_Pers)
						->where(function ($q) {
							$q->where('training_personals.CapaPersDelete', 0)->orWhereNull('training_personals.CapaPersDelete');
						})
						->where('training_personals.CapaPersExpire', '>=', Carbon::today())
						->where(function ($q) {
							$q->where('trainings.CapaName', 'like', '%mercanc%')
								->orWhere('trainings.CapaName', 'like', '%peligros%')
								->orWhere('trainings.CapaName', 'like', '%transporte%');
						})
						->exists();
					if ($certMpVigente) {
						$prefillMercanciasPeligrosas['cert_conductor'] = 'SI';
					}
				}
				return view('ProgramacionVehicle.preoperacional', compact('programacion', 'vehiculo', 'conductor', 'ayudante', 'vehiculosDisponibles', 'ayudantesDisponibles', 'ordenRutaOptions', 'prefillDocumentacion', 'prefillEquipo', 'prefillMercanciasPeligrosas'))
					->with('warning', 'Modo prueba: no hay programación asignada. Este formulario no se guardará en la base de datos.');
			}

			$programacion = ProgramacionVehiculo::with(['servicio', 'puntosderecoleccion.generadors'])
				->where('ID_ProgVeh', $id)
				->where('FK_ProgConductor', Auth::user()->FK_UserPers)
				->first();

			if (!$programacion) {
				abort(404, 'La programación no existe o no tiene permiso para acceder a ella');
			}

			// Obtener información del vehículo
			$vehiculo = null;
			if ($programacion->FK_ProgVehiculo) {
				$vehiculo = Vehiculo::where('ID_Vehic', $programacion->FK_ProgVehiculo)->first();
			}

			// Conductor y ayudante (para mostrar en el formato)
			$conductor = Personal::where('ID_Pers', $programacion->FK_ProgConductor)->first();
			$ayudante = null;
			if ($programacion->FK_ProgAyudante) {
				$ayudante = Personal::where('ID_Pers', $programacion->FK_ProgAyudante)->first();
			}

			// Prellenado automático desde datos del vehículo (documentación y extintor vigentes)
			$prefillDocumentacion = [];
			$prefillEquipo = [];
			$prefillMercanciasPeligrosas = [];
			$hoy = Carbon::today();

			if ($vehiculo) {
				// Documentación: SI si vencimiento >= hoy, NO si vencido
				foreach (['tarjeta_propiedad' => 'VehicTarjetaPropiedadVencimiento', 'soat' => 'VehicSoatVencimiento', 'tecnomecanica' => 'VehicTecnomecanicaVencimiento', 'poliza_rc' => 'VehicPolizaVencimiento'] as $key => $campo) {
					$venc = $vehiculo->$campo ?? null;
					if ($venc) {
						$fecha = \Carbon\Carbon::parse($venc);
						$prefillDocumentacion[$key] = $fecha->greaterThanOrEqualTo($hoy) ? 'SI' : 'NO';
					}
				}
				// Extintor: vigente si al menos uno tiene VehicExt*FechaRecarga >= hoy
				$ext1Vig = $vehiculo->VehicExt1FechaRecarga ? \Carbon\Carbon::parse($vehiculo->VehicExt1FechaRecarga)->greaterThanOrEqualTo($hoy) : false;
				$ext2Vig = $vehiculo->VehicExt2FechaRecarga ? \Carbon\Carbon::parse($vehiculo->VehicExt2FechaRecarga)->greaterThanOrEqualTo($hoy) : false;
				if ($ext1Vig || $ext2Vig) {
					$prefillEquipo['extintor'] = 'SI';
				} elseif ($vehiculo->VehicExt1FechaRecarga || $vehiculo->VehicExt2FechaRecarga) {
					$prefillEquipo['extintor'] = 'NO';
				}
			}

			// Prellenado desde datos del conductor (licencia, certificación MP)
			if ($conductor) {
				// Licencia de conducción: SI si tiene PersPase registrado o training "Licencia de conducción" vigente
				$licenciaVigente = !empty(trim($conductor->PersPase ?? ''));
				if (!$licenciaVigente) {
					$licenciaVigente = DB::table('training_personals')
						->join('trainings', 'training_personals.FK_Capa', '=', 'trainings.ID_Capa')
						->where('training_personals.FK_Pers', $conductor->ID_Pers)
						->where(function ($q) {
							$q->where('training_personals.CapaPersDelete', 0)->orWhereNull('training_personals.CapaPersDelete');
						})
						->where('training_personals.CapaPersExpire', '>=', $hoy)
						->where('trainings.CapaName', 'like', '%Licencia%')
						->exists();
				}
				if ($licenciaVigente) {
					$prefillDocumentacion['licencia'] = 'SI';
				}
				// Certificación conductor mercancías peligrosas: desde training_personals vigente
				$certMpVigente = DB::table('training_personals')
					->join('trainings', 'training_personals.FK_Capa', '=', 'trainings.ID_Capa')
					->where('training_personals.FK_Pers', $conductor->ID_Pers)
					->where(function ($q) {
						$q->where('training_personals.CapaPersDelete', 0)->orWhereNull('training_personals.CapaPersDelete');
					})
					->where('training_personals.CapaPersExpire', '>=', $hoy)
					->where(function ($q) {
						$q->where('trainings.CapaName', 'like', '%mercanc%')
							->orWhere('trainings.CapaName', 'like', '%peligros%')
							->orWhere('trainings.CapaName', 'like', '%transporte%');
					})
					->exists();
				if ($certMpVigente) {
					$prefillMercanciasPeligrosas['cert_conductor'] = 'SI';
				}
			}

			// Opciones de ruta: puntos de recolección asignados a la programación (fallback a sedes generales si no hay)
			$ordenRutaOptions = collect($programacion->puntosderecoleccion ?? [])
				->map(function ($pp) {
					$id = $pp->ID_GSede ?? null;
					if (!$id) return null;
					$genName = '';
					try { $genName = $pp->generadors ? ($pp->generadors->GenerName ?? '') : ''; } catch (\Exception $e) { $genName = ''; }
					$label = trim(($genName ? $genName.' - ' : '').($pp->GSedeName ?? ''));
					return [
						'id' => $id,
						'label' => $label ?: ('Sede #' . $id),
						'direccion' => $pp->GSedeAddress ?? '',
					];
				})
				->filter()
				->values();

			if ($ordenRutaOptions->isEmpty()) {
				$ordenRutaOptions = GenerSede::with('generadors')
					->select('ID_GSede', 'FK_GSede', 'GSedeName', 'GSedeAddress', 'GSedeDelete')
					->where('GSedeDelete', 0)
					->orderBy('ID_GSede', 'desc')
					->limit(80)
					->get()
					->map(function ($s) {
						$genName = '';
						try { $genName = $s->generadors ? ($s->generadors->GenerName ?? '') : ''; } catch (\Exception $e) { $genName = ''; }
						$label = trim(($genName ? $genName.' - ' : '').($s->GSedeName ?? ''));
						return [
							'id' => $s->ID_GSede,
							'label' => $label ?: ('Sede #' . $s->ID_GSede),
							'direccion' => $s->GSedeAddress ?? '',
						];
					})
					->values();
			}

			return view('ProgramacionVehicle.preoperacional', compact('programacion', 'vehiculo', 'conductor', 'ayudante', 'vehiculosDisponibles', 'ayudantesDisponibles', 'ordenRutaOptions', 'prefillDocumentacion', 'prefillEquipo', 'prefillMercanciasPeligrosas'));
		}
		
		abort(403, 'No tiene permisos para acceder a esta funcionalidad');
	}

	/**
	 * Guardar formulario preoperacional desde conductores
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function storePreoperacional(Request $request, $id)
	{
		// Validar que el usuario es conductor y que la programación le pertenece
		if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
			// Modo prueba (id=0): permite validar UI sin depender de programaciones en BD
			if ((int)$id === 0) {
				$request->validate([
					'FK_ProgVehiculo' => 'nullable|integer|exists:vehiculos,ID_Vehic',
					'FK_ProgAyudante' => 'nullable|integer|exists:personals,ID_Pers',
					'ProgVehKmInicial' => 'required|integer|min:0',
					'ProgVehKmFinal' => 'nullable|integer|min:0|gte:ProgVehKmInicial',
					'ProgVehCheckPreoperacional' => 'nullable|array',
					'ProgVehObsPreoperacional' => 'nullable|string|max:2000',
					'ProgVehEstadoVehiculo' => 'required|in:Funcional,Requiere Mantenimiento,No Funcional',
					'ProgVehFirmaConductor' => 'required|string',
				]);

				return redirect()->back()->with('success', 'Preoperacional guardado (modo prueba). No se registró en base de datos.');
			}
			
			$programacion = ProgramacionVehiculo::where('ID_ProgVeh', $id)
				->where('FK_ProgConductor', Auth::user()->FK_UserPers)
				->first();

			if (!$programacion) {
				abort(404, 'La programación no existe o no tiene permiso para acceder a ella');
			}

			// Validar datos del formulario
			$request->validate([
				'FK_ProgVehiculo' => 'nullable|integer|exists:vehiculos,ID_Vehic',
				'FK_ProgAyudante' => 'nullable|integer|exists:personals,ID_Pers',
				'ProgVehKmInicial' => 'required|integer|min:0',
				'ProgVehKmFinal' => 'nullable|integer|min:0|gte:ProgVehKmInicial',
				'ProgVehCheckPreoperacional' => 'nullable|array',
				'ProgVehObsPreoperacional' => 'nullable|string|max:2000',
				'ProgVehEstadoVehiculo' => 'required|in:Funcional,Requiere Mantenimiento,No Funcional',
				'ProgVehFirmaConductor' => 'required|string',
			]);

			// Permitir seleccionar placa/vehículo desde el formulario
			$vehiculoIdSeleccionado = $request->input('FK_ProgVehiculo');
			if (!empty($vehiculoIdSeleccionado)) {
				$programacion->FK_ProgVehiculo = (int) $vehiculoIdSeleccionado;
			}

			// Permitir seleccionar ayudante desde el formulario
			$ayudanteIdSeleccionado = $request->input('FK_ProgAyudante');
			$programacion->FK_ProgAyudante = !empty($ayudanteIdSeleccionado) ? (int) $ayudanteIdSeleccionado : null;

			// Obtener información del conductor y vehículo para el PDF
			$conductor = Personal::where('ID_Pers', Auth::user()->FK_UserPers)->first();
			$vehiculo = null;
			if ($programacion->FK_ProgVehiculo) {
				$vehiculo = Vehiculo::where('ID_Vehic', $programacion->FK_ProgVehiculo)->first();
			}

			// Guardar firma del conductor (base64)
			$firmaBase64 = $request->input('ProgVehFirmaConductor');

			// Checklist completo (sin orden de ruta)
			$checkData = $request->input('ProgVehCheckPreoperacional') ?: [];

			// Guardar datos del formulario preoperacional
			$programacion->ProgVehKmInicial = $request->input('ProgVehKmInicial');
			$programacion->ProgVehKmFinal = $request->input('ProgVehKmFinal');
			$programacion->ProgVehFechaPreoperacional = now();
			$programacion->ProgVehCheckPreoperacional = is_array($checkData) && count($checkData) ? json_encode($checkData) : null;
			$programacion->ProgVehObsPreoperacional = $request->input('ProgVehObsPreoperacional');
			$programacion->ProgVehEstadoVehiculo = $request->input('ProgVehEstadoVehiculo');
			$programacion->ProgVehFirmaConductor = $firmaBase64;
			$programacion->ProgVehPreoperacionalCompletado = true;

			// Si se proporciona kilometraje final, actualizar también progVehKm
			if ($request->input('ProgVehKmFinal')) {
				$programacion->progVehKm = $request->input('ProgVehKmFinal');
				
				// Actualizar kilometraje actual del vehículo
				if ($vehiculo) {
					$vehiculo->VehicKmActual = $request->input('ProgVehKmFinal');
					$vehiculo->save();
				}
			}

			// Generar PDF del formulario preoperacional
			try {
				// OJO: usar el facade importado (Pdf), no depender del alias global PDF
				$pdf = Pdf::setPaper('letter', 'portrait')
					->loadView('ProgramacionVehicle.pdf-preoperacional', compact('programacion', 'vehiculo', 'conductor', 'firmaBase64'));
				
				// Generar nombre único para el PDF
				$nombrePdf = 'preoperacional_' . $programacion->ID_ProgVeh . '_' . date('YmdHis') . '.pdf';
				$path = 'public/Preoperacionales/' . $nombrePdf;
				
				// Crear directorio si no existe
				Storage::makeDirectory('public/Preoperacionales');
				
				// Guardar PDF (disco local: storage/app/public/Preoperacionales)
				Storage::put($path, $pdf->output(), ['visibility' => 'public']);
				
				// Guardar nombre del PDF en la programación
				$programacion->ProgVehPdfPreoperacional = $nombrePdf;
			} catch (\Throwable $e) {
				// Si falla la generación del PDF, continuar sin él
				Log::error('Error generando PDF preoperacional', [
					'ID_ProgVeh' => $programacion->ID_ProgVeh,
					'error' => $e->getMessage(),
				]);
			}

			$programacion->save();

			// Registrar auditoría
			$log = new audit();
			$log->AuditTabla = "progvehiculos";
			$log->AuditType = "Formulario Preoperacional";
			$log->AuditRegistro = $programacion->ID_ProgVeh;
			$log->AuditUser = Auth::user()->email;
			$log->Auditlog = json_encode($request->except('ProgVehFirmaConductor')); // Excluir firma del log
			$log->save();

			// Verificar si hay más programaciones pendientes para hoy
			$programacionPendiente = ProgramacionVehiculo::where('FK_ProgConductor', Auth::user()->FK_UserPers)
				->where('ProgVehDelete', 0)
				->whereDate('ProgVehFecha', Carbon::today())
				->where('ID_ProgVeh', '!=', $programacion->ID_ProgVeh) // Excluir la que acaba de completar
				->where(function($query) {
					$query->where('ProgVehPreoperacionalCompletado', false)
						  ->orWhereNull('ProgVehPreoperacionalCompletado');
				})
				->whereNull('ProgVehEntrada')
				->orderBy('ProgVehFecha', 'asc')
				->orderBy('ProgVehSalida', 'asc')
				->first();
			
			if ($programacionPendiente) {
				return redirect()->route('vehicle-programacion.preoperacional', $programacionPendiente->ID_ProgVeh)
					->with('success', 'Formulario preoperacional guardado exitosamente. Complete el siguiente formulario.');
			}
			
			return redirect()->route('vehicle-programacion.index')->with('success', 'Formulario preoperacional guardado exitosamente');
		}
		
		abort(403, 'No tiene permisos para realizar esta acción');
	}

	/**
	 * Botón "Crear preoperacional" (siempre habilitado):
	 * redirige al primer formulario pendiente desde hoy en adelante,
	 * o regresa al index con mensaje si no hay pendientes.
	 */
	public function createPreoperacional()
	{
		if(!(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))){
			abort(403, 'No tiene permisos para acceder a esta funcionalidad');
		}

		$programacionPendiente = ProgramacionVehiculo::where('FK_ProgConductor', Auth::user()->FK_UserPers)
			->where('ProgVehDelete', 0)
			->whereDate('ProgVehFecha', '>=', Carbon::today())
			->where(function($query) {
				$query->where('ProgVehPreoperacionalCompletado', false)
					  ->orWhereNull('ProgVehPreoperacionalCompletado');
			})
			->whereIn('FK_ProgServi', function($q){
				$q->select('ID_SolSer')
				  ->from('solicitud_servicios')
				  ->whereIn('SolSerStatus', ['Programado', 'Notificado']);
			})
			->orderBy('ProgVehFecha', 'asc')
			->orderBy('ProgVehSalida', 'asc')
			->first();

		if ($programacionPendiente) {
			return redirect()->route('vehicle-programacion.preoperacional', $programacionPendiente->ID_ProgVeh);
		}

		// Si no hay programaciones, permitir pruebas del formulario sin bloqueo (id=0)
		return redirect()->route('vehicle-programacion.preoperacional', 0);
	}

	/**
	 * Mostrar historial de formularios preoperacionales
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function historialPreoperacional()
	{
		// Verificar permisos - conductores solo ven los suyos, otros roles ven todos
		if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
			
			$query = DB::table('progvehiculos')
				->join('solicitud_servicios', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				->join('clientes', 'solicitud_servicios.FK_SolSerCliente', 'clientes.ID_Cli')
				->select('progvehiculos.*', 'solicitud_servicios.ID_SolSer', 'clientes.CliName', 'clientes.CliCategoria')
				->where('progvehiculos.ProgVehDelete', 0)
				->where('progvehiculos.ProgVehPreoperacionalCompletado', true)
				->whereNotNull('progvehiculos.ProgVehFechaPreoperacional');

			// Si es conductor, solo ver sus propios formularios
			if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
				$query->where('progvehiculos.FK_ProgConductor', Auth::user()->FK_UserPers);
			}

			$programaciones = $query->orderBy('progvehiculos.ProgVehFechaPreoperacional', 'desc')
				->get();

			// Obtener datos adicionales
			$personals = DB::table('personals')
				->select('ID_Pers', 'PersFirstName', 'PersLastName')
				->get();
			
			$vehiculos = DB::table('vehiculos')
				->select('ID_Vehic','VehicPlaca')
				->get();

			return view('ProgramacionVehicle.historial-preoperacional', compact('programaciones', 'personals', 'vehiculos'));
		}
		
		abort(403, 'No tiene permisos para acceder a esta funcionalidad');
	}

	/**
	 * Descargar PDF del formulario preoperacional
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function downloadPdfPreoperacional($id)
	{
		// Verificar permisos
		if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
			
			$programacion = ProgramacionVehiculo::with(['servicio', 'puntosderecoleccion'])
				->where('ID_ProgVeh', $id)
				->where('ProgVehPreoperacionalCompletado', true)
				->first();

			// Si es conductor, solo puede ver sus propios PDFs
			if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)){
				$programacion = ProgramacionVehiculo::with(['servicio', 'puntosderecoleccion'])
					->where('ID_ProgVeh', $id)
					->where('FK_ProgConductor', Auth::user()->FK_UserPers)
					->where('ProgVehPreoperacionalCompletado', true)
					->first();
			}

			if (!$programacion) {
				abort(404, 'El formulario preoperacional no existe');
			}

			// Ruta real donde se guardan los PDFs
			$diskPath = 'public/Preoperacionales/';

			// Si no hay nombre guardado, lo generamos y lo persistimos
			if (!$programacion->ProgVehPdfPreoperacional) {
				$programacion->ProgVehPdfPreoperacional = 'preoperacional_' . $programacion->ID_ProgVeh . '_' . date('YmdHis') . '.pdf';
				$programacion->save();
			}

			$storageFile = $diskPath . $programacion->ProgVehPdfPreoperacional;

			// Si el archivo no existe, regenerar y guardar
			if (!Storage::exists($storageFile)) {
				$conductor = Personal::where('ID_Pers', $programacion->FK_ProgConductor)->first();
				$vehiculo = null;
				if ($programacion->FK_ProgVehiculo) {
					$vehiculo = Vehiculo::where('ID_Vehic', $programacion->FK_ProgVehiculo)->first();
				}
				$firmaBase64 = $programacion->ProgVehFirmaConductor;

				$pdf = Pdf::setPaper('letter', 'portrait')
					->loadView('ProgramacionVehicle.pdf-preoperacional', compact('programacion', 'vehiculo', 'conductor', 'firmaBase64'));

				Storage::makeDirectory('public/Preoperacionales');
				Storage::put($storageFile, $pdf->output(), ['visibility' => 'public']);
			}

			$pdfPath = storage_path('app/' . $storageFile);
			return response()->download($pdfPath, 'preoperacional_' . $programacion->ID_ProgVeh . '.pdf');
		}
		
		abort(403, 'No tiene permisos para acceder a esta funcionalidad');
	}
}
