<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SolServStoreRequest;
use App\Http\Requests\StoreServExpressRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\userController;
use App\Http\Controllers\ProgramacionExpressController;
use App\Http\Controllers\SolicitudResiduoController;
use App\Mail\NewSolServEmail;
use App\Mail\SolSerLeftRespel;
use App\Mail\NewSolServProsarcEmail;
use App\Mail\SolSerExpressEmail;
use App\Mail\CertExpressRetenidoEmail;
use App\Mail\SolSerExpressConciliado;
use App\Mail\SolSerExpressRecibo;
use App\Mail\SolSerRME;
use App\Mail\CertExpressSinSaldoEmail;
use App\Mail\SolSerExpressCertificado;
use App\SolicitudServicio;
use App\SolicitudResiduo;
use App\audit;
use App\Sede;
use App\GenerSede;
use App\Respel;
use App\ResiduosGener;
use App\Cliente;
use App\Tratamiento;
use App\Generador;
use App\Personal;
use App\Departamento;
use App\Municipio;
use App\Tarifa;
use App\CTarifa;
use App\Rango;
use App\CertificadoExpress;
use App\CertExpressdato;
use App\Certificado;
use App\Certdato;
use App\Manifiesto;
use App\Manifdato;
use App\Requerimiento;
use App\Documento;
use App\Docdato;
use App\ProgramacionVehiculo;
use App\RequerimientosCliente;
use App\Observacion;
use App\ReciboDePago;
use App\FirmasServicios;
use App\Permisos;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Barryvdh\DomPDF\Facade\Pdf as PDF;use Endroid\QrCode\Response\QrCodeResponse;

class ServiceExpressController extends Controller
{
    /**
     * Buzón interno único para notificaciones de Servicios Express (sustituye listas de correos individuales).
     */
    private const MAIL_EXPRESS_INTERNO = 'express@prosarc.com.co';
    /** Mismo buzón que solicitud regular: cliente añade residuos faltantes (SolSerLeftRespel). */
    private const MAIL_PROGRAMACIONES_INTERNO = 'programaciones@prosarc.com.co';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Mostrar vista de selección de año para servicios Express
        return view('serviciosexpress.año');
    }

	/**
	 * Index optimizado por año: filtros mes y cliente dentro del año seleccionado.
	 * Carga diferida: solo ejecuta query cuando el usuario hace Buscar o Ver todos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $anio
	 * @return \Illuminate\Http\Response
	 */
	protected function soliPorAnio(Request $request, $anio)
	{
		$subquery = DB::table('progvehiculos')
			->select('FK_ProgServi', DB::raw('MIN(ProgVehSalida) as primera_recepcion'))
			->where('ProgVehDelete', 0)
			->groupBy('FK_ProgServi');

		$query = DB::table('solicitud_servicios')
            // LEFT JOINs para evitar que la ausencia de datos en una tabla elimine la fila
            ->leftJoin('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
            ->leftJoin('clientes_express', 'clientes_express.id', '=', 'solicitud_servicios.FK_Cliente_Express')
            ->leftJoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
            ->leftJoin('personals as Comercial', 'Comercial.ID_Pers', '=', 'clientes.CliComercial')

            // Subconsulta de programaciones
            ->leftJoinSub($subquery, 'primera_prog', function($join) {
                $join->on('solicitud_servicios.ID_SolSer', '=', 'primera_prog.FK_ProgServi');
            })
            ->leftJoin('progvehiculos', function($join) {
                $join->on('progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
                    ->on('progvehiculos.ProgVehSalida', '=', 'primera_prog.primera_recepcion')
                    ->where('progvehiculos.ProgVehDelete', '=', 0);
            })

            ->select(
                'solicitud_servicios.ID_SolSer',
                'solicitud_servicios.SolSerStatus',
                'solicitud_servicios.SolSerTipo',
                'solicitud_servicios.SolSerAuditable',
                'solicitud_servicios.SolSerConductor',
                'solicitud_servicios.SolSerVehiculo',
                'solicitud_servicios.SolSerSlug',
                'solicitud_servicios.created_at',
                'solicitud_servicios.updated_at',
                'solicitud_servicios.SolSerDelete',
                'solicitud_servicios.SolResAuditoriaTipo',
                'solicitud_servicios.SolSerNameTrans',
                'solicitud_servicios.SolSerNitTrans',
                'solicitud_servicios.SolSerAdressTrans',
                'solicitud_servicios.SolSerTypeCollect',
                'solicitud_servicios.SolSerCollectAddress',
                'solicitud_servicios.SolServCertStatus',
                'solicitud_servicios.SolNumeroFactura',
                'solicitud_servicios.FK_SolSerCliente',
                'solicitud_servicios.FK_Cliente_Express',

                // --- Nombre Unificado del Cliente (Tradicional o Express) ---
                DB::raw("COALESCE(clientes.CliName, clientes_express.nombreEmpresa) as CliName"),

                // Datos específicos de Cliente Tradicional
                'clientes.ID_Cli',
                'clientes.CliSlug',
                'clientes.CliStatus',
                'clientes.TipoFacturacion',
                'clientes.CliCategoria',

                // Datos específicos de Cliente Express (si necesitas algún otro campo)
                'clientes_express.id as ID_ClienteExpress',
                'clientes_express.localidad as ExpressLocalidad',

                // Datos del personal/contacto
                'personals.PersFirstName',
                'personals.PersLastName',
                'personals.PersSlug',
                'personals.PersEmail',
                'personals.PersCellphone',

                // Comercial asignado (si aplica)
                'Comercial.ID_Pers as ComercialID_Pers',
                'Comercial.PersFirstName as ComercialPersFirstName',
                'Comercial.PersLastName as ComercialPersLastName',
                'Comercial.PersSlug as ComercialPersSlug',
                'Comercial.PersEmail as ComercialPersEmail',
                'Comercial.PersCellphone as ComercialPersCellphone',

                'progvehiculos.ProgVehSalida as recepcion'
            )
            ->where(function($q) {
                // Validación de permisos por Rol
                if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
                    $idCliente = userController::IDClienteSegunUsuario();
                    $q->where('solicitud_servicios.FK_SolSerCliente', $idCliente)
                    ->orWhere('solicitud_servicios.FK_Cliente_Express', $idCliente);
                }

                if (in_array(Auth::user()->UsRol, Permisos::SOLSERACEPTADO) || in_array(Auth::user()->UsRol2, Permisos::SOLSERACEPTADO)) {
                    if (!in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)) {
                        $q->where('solicitud_servicios.SolSerStatus', 'Pendiente')
                        ->orWhere('solicitud_servicios.SolServCertStatus', 1);
                    }
                }

                if (in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES)) {
                    if (in_array(Auth::user()->UsRol, Permisos::COMERCIAL)) {
                        $q->where('Comercial.ID_Pers', Auth::user()->FK_UserPers);
                    }
                }
            })
            // Condición para permitir Clientes Prepago O Clientes Express (sin importar la categoría)
            ->where(function($q) {
                $q->where('clientes.CliCategoria', 'ClientePrepago')
                ->orWhereNotNull('solicitud_servicios.FK_Cliente_Express');
            })
            ->whereYear('progvehiculos.ProgVehSalida', $anio)
            ->orderBy('progvehiculos.ProgVehSalida', 'desc');
		// Filtro por mes (1-12)
		if ($request->filled('mes')) {
			$mes = (int) $request->mes;
			if ($mes >= 1 && $mes <= 12) {
				$query->whereMonth('progvehiculos.ProgVehSalida', $mes);
			}
		}
		// Filtro por cliente
		if ($request->filled('cliente')) {
			$query->where('clientes.ID_Cli', $request->cliente);
		}

		// Carga diferida: solo ejecutar cuando el usuario hace Buscar o Ver todos
		$buscar = $request->has('buscar') && $request->buscar == '1';
		$verTodos = $request->has('ver') && $request->ver === 'todos';
		$Servicios = ($buscar || $verTodos) ? $query->get() : collect();

		$cliId = userController::IDClienteSegunUsuario();
		$Cliente = $cliId ? Cliente::select('CliName', 'ID_Cli', 'CliStatus')->where('ID_Cli', $cliId)->first() : null;

		foreach ($Servicios as $servicio) {
			$sedeExpress = Sede::where('FK_SedeCli', $servicio->ID_Cli)->first();
			if ($sedeExpress) {
				$servicio->FK_SedeMun = $sedeExpress->FK_SedeMun;
				$servicio->SedeMapLocalidad = $sedeExpress->SedeMapLocalidad;
				$servicio->SedeMapLat = $sedeExpress->SedeMapLat;
				$servicio->SedeMapLong = $sedeExpress->SedeMapLong;
			} else {
				$servicio->FK_SedeMun = null;
				$servicio->SedeMapLocalidad = null;
				$servicio->SedeMapLat = null;
				$servicio->SedeMapLong = null;
			}
			if ($servicio->SolSerTypeCollect == 98) {
				$Address = Sede::select('SedeAddress')->where('ID_Sede', $servicio->SolSerCollectAddress)->first();
				$servicio->SolSerCollectAddress = $Address ? $Address->SedeAddress : $servicio->SolSerCollectAddress;
			}
		}

		// Clientes con servicios Express en este año (por ProgVehSalida)
		$qClientes = DB::table('solicitud_servicios')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('progvehiculos', function($j) {
				$j->on('progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				  ->where('progvehiculos.ProgVehDelete', '=', 0);
			})
			->where('clientes.CliCategoria', 'ClientePrepago')
			->whereYear('progvehiculos.ProgVehSalida', $anio)
			->select('clientes.ID_Cli', 'clientes.CliName')
			->distinct()
			->orderBy('clientes.CliName');

		if (in_array(Auth::user()->UsRol, Permisos::COMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::COMERCIAL)) {
			$qClientes->join('personals as Comercial', 'Comercial.ID_Pers', '=', 'clientes.CliComercial')
				->where('Comercial.ID_Pers', Auth::user()->FK_UserPers);
		}
		$clientesFiltro = $qClientes->get();

		// Meses del año (Enero a Diciembre)
		$nombresMes = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
		$mesesFiltro = collect();
		for ($m = 1; $m <= 12; $m++) {
			$mesesFiltro->push((object)['valor' => $m, 'label' => $nombresMes[$m - 1]]);
		}

		return view('serviciosexpress.anioFiltrado', compact('Servicios', 'Cliente', 'clientesFiltro', 'mesesFiltro', 'anio'));
	}

	/**
	 * Display a listing of the resource for year 2020.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function soli2020(Request $request)
	{
		return $this->soliPorAnio($request, 2020);
	}

	public function soli2021(Request $request)
	{
		return $this->soliPorAnio($request, 2021);
	}

	public function soli2022(Request $request)
	{
		return $this->soliPorAnio($request, 2022);
	}

	public function soli2023(Request $request)
	{
		return $this->soliPorAnio($request, 2023);
	}

	public function soli2024(Request $request)
	{
		return $this->soliPorAnio($request, 2024);
	}

	public function soli2025(Request $request)
	{
		return $this->soliPorAnio($request, 2025);
	}

	public function soli2026(Request $request)
	{
		return $this->soliPorAnio($request, 2026);
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(in_array(Auth::user()->UsRol, Permisos::COMERCIALEXPRESS) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALEXPRESS) || in_array(Auth::user()->UsRol, Permisos::USAQUEN)){

			$Clientes = Cliente::with('sedes')
			->where('CliCategoria', 'ClientePrepago')
			->where('CliDelete', '0')
			->orderBy('created_at', 'desc')
			->get();

			// Cliente preseleccionado si viene en la URL
			$clientePreseleccionado = null;
			if ($request->has('ID_Cli')) {
				$clientePre = Cliente::find($request->input('ID_Cli'));
				if ($clientePre) {
					$clientePreseleccionado = $clientePre->CliSlug;
				}
			}

			return view('serviciosexpress.create', compact('Clientes', 'clientePreseleccionado'));
		}
		else{
			abort(403, 'Solo los Roles autorizados pueden realizar nuevas solicitudes de servicio Express');
		}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServExpressRequest $request)
    {
		// return $request;

        //sede segun input

        $sede = Sede::where('SedeSlug', $request->input('SedeSlug'))->first();

		$Cliente = Cliente::where('ID_Cli', $sede->FK_SedeCli)->first();

       /* $file = $request->file('pagoComprobante');

        switch ($file->getClientOriginalExtension()) {
            case 'pdf':
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'jpe':
                $foldername = $Cliente->CliNit;
                $foldername = str_replace('.', '', $foldername);
                $foldername = str_replace(' ', '_', $foldername);
                $foldername = str_replace('(', '_', $foldername);
                $foldername = str_replace(')', '_', $foldername);
                $foldername = str_replace('__', '_', $foldername);

                $fileName = $request->input('Referencia');
                $fileName = str_replace('.', '', $fileName);
                $fileName = str_replace(' ', '_', $fileName);
                $fileName = str_replace('(', '_', $fileName);
                $fileName = str_replace(')', '_', $fileName);
                $fileName = str_replace('__', '_', $fileName);
                $fileName = time().$fileName;

                // Storage::put('comprobantes/'.$fileName.$file->getClientOriginalExtension(), $file, 'public');
                $filePath = $file->storeAs('comprobantes/'.$foldername.'/', $fileName.'.'.$file->getClientOriginalExtension(), 'public');
                break;

            default:
                abort(422, 'El archivo debe estar de un formato permitido png, jpg o pdf');
                break;
        }*/
        // return $filePath;

        // generar el registro para el recibo de pago
        /* $recibo = new ReciboDePago();
        $recibo->fecha_de_pago = $request->input('fechadepago');
        $recibo->monto = $request->input('montodepago');
        $recibo->referencia = $request->input('Referencia');
        $recibo->medio_de_pago = $request->input('mediodepago');
        $recibo->observacion = $request->input('SolSerDescript');
        $recibo->url_comprobante = '';
        $recibo->url_recibo = '';
        $recibo->FK_ReciboCliente = $Cliente->ID_Cli;
        $recibo->ReciboSlug = hash('md5', rand().time().$recibo->Referencia);
        $recibo->save();

        /**crear el pdf de recibo */

       /*  $qrCode = new QrCode(route('recibosdepago.show', ['recibosdepago' => $recibo->ReciboSlug]));
		$qrCode->setLogoPath(asset('img/LogoQR.png'));
		$qrCode->setLogoSize(60, 60);
		$qrCode->setSize(300);
		$qrCode->setMargin(0);
		$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);

        $pdf = PDF::setPaper('letter', 'portrait')->loadView('recibos.recibotopdf', compact(['recibo','Cliente','qrCode','sede']));
        Storage::put('recibosdepago/'.'/RP-'.sprintf("%07s", $recibo->ID_Recibo).'.pdf', $pdf->output(), 'public');

        $recibo->url_recibo = 'recibosdepago/'.'/RP-'.sprintf("%07s", $recibo->ID_Recibo).'.pdf';
        $recibo->save();  */

        // return $request;


		$Persona = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->select('personals.*')
				->where('sedes.ID_Sede', $sede->ID_Sede)
				->where('personals.PersDelete', 0)
				->first();

		for ($i=0; $i < $request->input('SolServCantidad'); $i++) {
			$SolicitudServicio = new SolicitudServicio();
			$SolicitudServicio->SolSerStatus = 'Aprobado';
			$SolicitudServicio->SolSerAuditable = 0;
			$SolicitudServicio->SolResAuditoriaTipo = "No Auditable";
           // $SolicitudServicio->SolSerSupport = 'comprobantes/'.$fileName.'.'.$file->getClientOriginalExtension();
			$SolicitudServicio->SolSerTipo = "Interno";
			$SolicitudServicio->SolSerNameTrans = 'Prosarc S.A. ESP.';
			$SolicitudServicio->SolSerNitTrans = '900.079.188-0';
			$SolicitudServicio->SolSerAdressTrans = 'KM 6 VÍA LA MESA SUB ESTACIÓN BALSILLAS';
			$SolicitudServicio->SolSerCityTrans = 584;
			$SolicitudServicio->SolSerConductor = null;
			$SolicitudServicio->SolSerVehiculo = null;
			$SolicitudServicio->SolSerDescript = 'Frecuencia:'.$request->input('SolServFrecuencia').'  '.$request->input('SolSerDescript');
			$SolicitudServicio->SolSerTypeCollect = 99;
			$SolicitudServicio->SolSerCollectAddress = $sede->SedeAddress;
			$SolicitudServicio->SolSerBascula = 0;
			$SolicitudServicio->SolSerCapacitacion = 0;
			$SolicitudServicio->SolSerMasPerson = 0;
			$SolicitudServicio->SolSerVehicExclusive = 0;
			$SolicitudServicio->SolSerPlatform = 0;
			$SolicitudServicio->SolSerDevolucion = 0;
			$SolicitudServicio->SolSerDevolucionTipo = null;
			$SolicitudServicio->SolSerSlug = hash('sha256', rand().time().$SolicitudServicio->SolSerNameTrans);
			$SolicitudServicio->SolSerDelete = 0;

			$SolicitudServicio->FK_SolSerPersona = $Persona->ID_Pers;
			$SolicitudServicio->FK_SolSerCliente = $Cliente->ID_Cli;
			/* $SolicitudServicio->FK_ReciboSolserv = $recibo->ID_Recibo; */
			$SolicitudServicio->save();
            if ($request->input('SolServTypeRecolection') == 'Especifica') {
                $this->createSolRes($request, $SolicitudServicio->ID_SolSer);
            }else{
                $this->addAllRespels($SolicitudServicio);
            }

			/*se guarda la observacion inicial de la creación del servicio*/
			$Observacion = new Observacion();
			$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
			/* $Observacion->ObsMensaje = 'Recibo:'.$recibo->ID_Recibo.' '.$SolicitudServicio->SolSerDescript; */
			$Observacion->ObsTipo = 'prosarc';
			$Observacion->ObsRepeat = 1;
			$Observacion->ObsDate = now();
			$Observacion->ObsUser = Auth::user()->email;
			$Observacion->ObsRol = Auth::user()->UsRol;
			$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
			$Observacion->save();

		}
		if ($Cliente->CliComercial <> null) {
			$comercial = Personal::where('ID_Pers', $Cliente->CliComercial)->first();
		} else {
			$comercial = "";
		}
		$destinatarios = [self::MAIL_EXPRESS_INTERNO];
		$SolicitudServicio['comercial'] = $comercial;
		$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();
		// se envia un correo por personal interesado
		// Mail::to($sede->SedeEmail)->cc($destinatarios)->send(new NewSolServEmail($SolicitudServicio));
        // se envia correo al cliente con el recibo de pado
        /* Mail::to($sede->SedeEmail)->cc($destinatarios)->send(new SolSerExpressRecibo($pdf, $comercial, $Cliente, $sede)); */
		return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $SolicitudServicio->SolSerSlug]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        log::info("Mostrando servicio Express con ID: $id");
		$SolicitudServicio = DB::table('solicitud_servicios')
			->leftJoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->select('solicitud_servicios.*','personals.PersFirstName','personals.PersLastName', 'personals.PersEmail', 'personals.PersCellphone')
			->where('solicitud_servicios.SolSerSlug', $id)
			->first();
        log::info("SolicitudServicio encontrada: " . json_encode($SolicitudServicio));
		if (!$SolicitudServicio) {
			abort(404);
		}

		// Asegurar que la propiedad 'recepcion' siempre exista
		$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)->programacionesrecibidas()->first();
		if($fechaRecepcion){
			$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
		}else{
			$SolicitudServicio->recepcion = null;
		}

		$Observaciones = Observacion::where('FK_ObsSolSer', $SolicitudServicio->ID_SolSer)->orderBy('ObsDate', 'desc')->get();

		if($SolicitudServicio->SolSerStatus == 'Completado'||$SolicitudServicio->SolSerStatus == 'Corregido'){
			$ultimoRecordatorio = Observacion::where('FK_ObsSolSer', $SolicitudServicio->ID_SolSer)
								->where('ObsStatus', 'Recordatorio+')
								->orderBy('ObsDate', 'desc')
								->first();
			if(!$ultimoRecordatorio){
				$ultimoRecordatorio = Observacion::where('FK_ObsSolSer', $SolicitudServicio->ID_SolSer)
								->where('ObsStatus', 'Completado')
								->orderBy('ObsDate', 'asc')
								->first();
				if(!$ultimoRecordatorio){
					$ultimoRecordatorio = (object)[];
					$ultimoRecordatorio->ObsDate = $SolicitudServicio->updated_at;
				}
				$ultimoRecordatorio->ObsRepeat = 0;
			}
		}


		$SolSerCollectAddress = $SolicitudServicio->SolSerCollectAddress;
		$SolSerConductor = $SolicitudServicio->SolSerConductor;
		if($SolicitudServicio->SolSerTipo == 'Interno'){
			// SolSerConductor puede ser ID (numérico) o nombre (string) cuando viene de la programación
			$SolSerConductor = is_numeric($SolicitudServicio->SolSerConductor)
				? Personal::find($SolicitudServicio->SolSerConductor)
				: null;
			if (!$SolSerConductor) {
				$programacionConductor = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
					->where('ProgVehDelete', 0)
					->first();
				if ($programacionConductor) {
					if ($programacionConductor->ProgVehtipo == 1 && $programacionConductor->FK_ProgConductor) {
						$SolSerConductor = Personal::find($programacionConductor->FK_ProgConductor);
					} elseif ($programacionConductor->ProgVehtipo == 2 && $programacionConductor->ProgVehNameConductorEXT) {
						$nombre = trim($programacionConductor->ProgVehNameConductorEXT);
						$partes = explode(' ', $nombre, 2);
						$SolSerConductor = (object)['PersFirstName' => $partes[0] ?? $nombre, 'PersLastName' => $partes[1] ?? ''];
					}
				}
			}
			if (!$SolSerConductor && $SolicitudServicio->SolSerConductor) {
				// Fallback: SolSerConductor tiene el nombre, crear objeto para la vista
				$nombre = trim($SolicitudServicio->SolSerConductor);
				$partes = explode(' ', $nombre, 2);
				$SolSerConductor = (object)['PersFirstName' => $partes[0] ?? $nombre, 'PersLastName' => $partes[1] ?? ''];
			}
		}
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
		switch ($SolicitudServicio->SolSerStatus) {
			case 'Notificado':
			case 'Programado':
				setlocale(LC_ALL, "es_CO.UTF-8");
				$Programacion = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->first();
				if(date('H', strtotime($Programacion->ProgVehSalida)) >= 12){
					$horas = " en las horas de la tarde";
				}
				else{
					$horas = " en las horas de la mañana";
				}
				$TextProgramacion = "El día ".strftime("%d", strtotime($Programacion->ProgVehFecha))." del mes de ".strftime("%B", strtotime($Programacion->ProgVehFecha)).$horas;
				$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehDelete', 0)
				->get();
				$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get());
				// $ProgramacionesActivas = ($Programaciones);
				break;

			case 'Residuo Faltante':
				setlocale(LC_ALL, "es_CO.UTF-8");
				$Programacion = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->first();
				if(date('H', strtotime($Programacion->ProgVehSalida)) >= 12){
					$horas = " en las horas de la tarde";
				}
				else{
					$horas = " en las horas de la mañana";
				}
				$TextProgramacion = "";
				$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehDelete', 0)
				->get();
				$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get());
				// $ProgramacionesActivas = ($Programaciones);
				break;

            case 'Aprobado':
				setlocale(LC_ALL, "es_CO.UTF-8");
				$TextProgramacion = "";
				$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehDelete', 0)
				->get();
				$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get());
				// $ProgramacionesActivas = ($Programaciones);
				break;

			default:
				$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				// ->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get();
                $Programacion = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->first();
				if(date('H', strtotime($Programacion->ProgVehSalida)) >= 12){
					$horas = " en las horas de la tarde";
				}
				else{
					$horas = " en las horas de la mañana";
				}
				$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get());
				break;
		}
		/* $Cliente = DB::table('clientes')
			->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
			->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
			->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
			->first(); */

        // 1. Definir la consulta de clientes tradicionales
        $clienteTradicional = DB::table('clientes')
            ->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
            ->leftJoin('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
            ->select(
                'clientes.ID_Cli as id',
                'clientes.CliNit as CliNit',
                'clientes.CliName as CliName',
                'sedes.SedeAddress as SedeAddress',
                'municipios.MunName as MunName'
            )
            ->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente);

        // 2. Definir la consulta de clientes express (ajusta los nombres de tabla/columnas según tu BD)
        $Cliente = DB::table('clientes_express')
            //->leftJoin('municipios', 'clientes_express.FK_CliExpressMun', '=', 'municipios.ID_Mun')
            ->select(
                'clientes_express.id as id',
                'clientes_express.nit as CliNit',
                'clientes_express.nombreEmpresa as CliName',
                'clientes_express.direccion as SedeAddress',
                'clientes_express.localidad as MunName'
            )
            ->where('clientes_express.id', $SolicitudServicio->FK_Cliente_Express) // O FK_SolSerCliente
            ->union($clienteTradicional)
            ->first();
		$GenerResiduos = DB::table('solicitud_residuos')
			->distinct()
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			->select('gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.GenerName','gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->get();
        log::info("GenerResiduos obtenidos: " . json_encode($GenerResiduos));
		// $Residuos = DB::table('solicitud_residuos')
		// 	->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
		// 	->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
		// 	->select('solicitud_residuos.*','residuos_geners.FK_SGener', 'respels.RespelName','respels.RespelSlug', 'respels.RespelStatus')
		// 	->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
		// 	->get();
		// Optimización: Usar Eloquent con eager loading en lugar de Query Builder + loops
		$Residuos = SolicitudResiduo::with([
			'generespel.respels',
			'requerimiento.pretratamientosSelected',
			'requerimiento.tratamiento.gestor.clientes'
		])
		->where('FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
		->where('SolResDelete', 0)
		->get()
		->map(function ($item) {
			// Agregar SolResRM2 si existe
			$item->SolResRM2 = $item->SolResRM;
			// Agregar FK_SGener desde la relación
			if ($item->generespel) {
				$item->FK_SGener = $item->generespel->FK_SGener;
			}
			// Agregar datos del respel desde la relación
			if ($item->generespel && $item->generespel->respels) {
				$respel = $item->generespel->respels;
				$item->RespelName = $respel->RespelName;
				$item->RespelSlug = $respel->RespelSlug;
				$item->RespelStatus = $respel->RespelStatus;
			}
			// Agregar datos del tratamiento desde la relación
			if ($item->requerimiento && $item->requerimiento->tratamiento) {
				$item->TratName = $item->requerimiento->tratamiento->TratName;
				$item->ID_Trat = $item->requerimiento->tratamiento->ID_Trat;
			}
			// Agregar CliShortName desde la relación
			if ($item->requerimiento && $item->requerimiento->tratamiento && $item->requerimiento->tratamiento->gestor && $item->requerimiento->tratamiento->gestor->clientes) {
				$item->CliShortName = $item->requerimiento->tratamiento->gestor->clientes->CliShortName;
			}
			return $item;
		});

        log::info("Residuos obtenidos: " . json_encode($Residuos));

		$SolicitudServicio->Repetible = 0;

		/* se convierte el tipo de dato a aray mediante la consulta en el modelo de la columna SolSerRMs usando eloquent*/
		$rms = SolicitudServicio::where('SolSerSlug', $SolicitudServicio->SolSerSlug)->first('SolSerRMs');
		$SolicitudServicio->SolSerRMs = $rms->SolSerRMs;

		// Optimización: Agrupar requerimientos para evitar consultas duplicadas
		$requerimientosIds = $Residuos->pluck('FK_SolResRequerimiento')->unique();
		$requerimientosCache = Requerimiento::with(['pretratamientosSelected'])
			->whereIn('ID_Req', $requerimientosIds)
			->get()
			->keyBy('ID_Req');

		foreach ($Residuos as $residuo => $value) {
			$requerimientos = $requerimientosCache->get($value->FK_SolResRequerimiento);
			if ($requerimientos) {
				$residuoSinTratamiento = Requerimiento::where('FK_ReqRespel', $requerimientos->FK_ReqRespel)
				->where('ofertado', 1)
				->where('forevaluation', 1)
		        ->first();

				if ($residuoSinTratamiento == null) {
					$SolicitudServicio->Repetible++;
				}
			}
		}

		// Optimización: Agrupar residuos por generador para evitar foreach anidado en la vista
		$ResiduosPorGenerador = $Residuos->groupBy('FK_SGener');

		$recibo = ReciboDePago::where('ID_Recibo', $SolicitudServicio->FK_ReciboSolserv)->first();

		// Calcular totales por tratamiento
		$total = [
			'estimado' => 0,
			'recibido' => 0,
			'conciliado' => 0,
			'tratado' => 0
		];
		$cantidadesXtratamiento = [];
		foreach ($Residuos as $residuo) {
			$tratamiento = $residuo->TratName ?? 'Sin Tratamiento';
			if (!isset($cantidadesXtratamiento[$tratamiento])) {
				$cantidadesXtratamiento[$tratamiento] = [
					'estimado' => 0,
					'recibido' => 0,
					'conciliado' => 0,
					'tratado' => 0
				];
			}
			$cantidadesXtratamiento[$tratamiento]['estimado'] += $residuo->SolResKgEnviado ?? 0;
			$cantidadesXtratamiento[$tratamiento]['recibido'] += $residuo->SolResKgRecibido ?? 0;
			$cantidadesXtratamiento[$tratamiento]['conciliado'] += $residuo->SolResKgConciliado ?? 0;
			$cantidadesXtratamiento[$tratamiento]['tratado'] += $residuo->SolResKgTratado ?? 0;

			$total['estimado'] += $residuo->SolResKgEnviado ?? 0;
			$total['recibido'] += $residuo->SolResKgRecibido ?? 0;
			$total['conciliado'] += $residuo->SolResKgConciliado ?? 0;
			$total['tratado'] += $residuo->SolResKgTratado ?? 0;
		}

		// Optimización: Agrupar residuos por generador para evitar foreach anidado en la vista
		$ResiduosPorGenerador = $Residuos->groupBy('FK_SGener');
        log::info("Residuos agrupados por generador: " . json_encode($ResiduosPorGenerador));

		$vehiculos = collect();
		$conductores = collect();
		$ayudantes = collect();
		$programacionOriginal = null;
		if (in_array(Auth::user()->UsRol, Permisos::COMERCIALEXPRESS) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALEXPRESS)) {
			$conductores = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName')
				->where('CargName', 'Conductor')
				->where('ID_Cli', 1)
				->where('PersDelete', '!=', 1)
				->get();
			$ayudantes = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('ID_Pers', 'PersFirstName', 'PersLastName')
				->whereIn('AreaName', ['Operaciones', 'Logística', 'Mantenimiento'])
				->whereNotIn('CargName', ["Asistente", 'Jefe'])
				->where('ID_Cli', 1)
				->get();
			$vehiculos = DB::table('vehiculos')
				->select('ID_Vehic', 'VehicPlaca')
				->where('FK_VehiSede', 1)
				->where('VehicDelete', 0)
				->get();
			$programacionOriginal = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)->where('ProgVehDelete', 0)->first();
		}

        /* return response()->json(compact(
    'SolicitudServicio',
    'Observaciones',
    'ResiduosPorGenerador',
    'Cliente',
    'GenerResiduos',
    'Residuos',
    'SolSerCollectAddress',
    'SolSerConductor',
    'Municipio',
    'TextProgramacion',
    'Programaciones',
    'ProgramacionesActivas',
    'recibo',
    'cantidadesXtratamiento',
    'total',
    'vehiculos',
    'conductores',
    'ayudantes',
    'programacionOriginal'
)); */
		return view('serviciosexpress.show', compact(
			'SolicitudServicio',
			'Observaciones',
			'ResiduosPorGenerador',
			'Cliente',
			'GenerResiduos',
			'Residuos',
			'SolSerCollectAddress',
			'SolSerConductor',
			'Municipio',
			'TextProgramacion',
			'Programaciones',
			'ProgramacionesActivas',
			'recibo',
			'cantidadesXtratamiento',
			'total',
			'vehiculos',
			'conductores',
			'ayudantes',
			'programacionOriginal'
		));
    }

	public function changestatus(Request $request)
	{
		$Solicitud = SolicitudServicio::where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}
		if ($Solicitud->SolSerStatus <> 'Certificacion') {
			if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
				if ($Solicitud->SolSerStatus == 'Completado' || $Solicitud->SolSerStatus == 'Corregido') {
					if($request->input('solserstatus') == 'No Deacuerdo'){
						$Solicitud->SolSerStatus = 'No Conciliado';
					}
					if($request->input('solserstatus') == 'Conciliada'){
						$Solicitud->SolSerStatus = 'Conciliado';
					}
				} else {
					if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
						abort(403, 'el servicio no esta habilitado para la conciliación de pesos');
					}
				}
			}

			if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::COMERCIALEXPRESS) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALEXPRESS)){
				switch ($request->input('solserstatus')) {
					case 'Aprobada':
						if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2 ) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2 )){
							$Solicitud->SolSerStatus = 'Aprobado';
						}
						break;
					case 'Aceptada':
						if(in_array(Auth::user()->UsRol, Permisos::SOLSERACEPTADO) || in_array(Auth::user()->UsRol2, Permisos::SOLSERACEPTADO)){
							$Solicitud->SolSerStatus = 'Aceptado';
						}
						break;
					case 'Recibida':
						if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::SolSer1)){
							$Solicitud->SolSerStatus = 'Completado';
						}
						break;
					case 'Residuo Faltante':
						if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::SolSer1)){
							$Solicitud->SolSerStatus = 'Residuo Faltante';
						}
						break;
					case 'Conciliación':
						if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2)){
							$Solicitud->SolSerStatus = 'Corregido';
						}
						break;
					case 'Tratada':
						if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::SolSer1)){
							$Solicitud->SolSerStatus = 'Tratado';
						}
						break;
					case 'Conciliada':
						if(in_array(Auth::user()->UsRol, Permisos::ADMINPLANTA) || in_array(Auth::user()->UsRol2, Permisos::ADMINPLANTA)){
							$Solicitud->SolSerStatus = 'Conciliado';
						}
						if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOREXPRESS ) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOREXPRESS )){
							$Solicitud->SolSerStatus = 'Conciliado';
						}
						break;
					case 'Certificada':
						if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi)){
							$Solicitud->SolSerStatus = 'Certificacion';
							$Solicitud->SolServCertStatus = 2;

							// Obtener el certificado más reciente
							$certificado = CertificadoExpress::where('FK_CertSolser', $Solicitud->ID_SolSer)
								->orderBy('created_at', 'desc')
								->first();

							if($certificado) {
								// Obtener la firma del cliente desde firmas_servicio (guardada al firmar el RM)
								// Para servicios Express, buscar primero el registro principal (FK_Gener = 0, FK_SGener = 0)
								$firmaCliente = DB::table('firmas_servicio')
									->where('FK_SolSer', $Solicitud->ID_SolSer)
									->where('FK_Gener', 0)
									->where('FK_SGener', 0)
									->whereNotNull('FirmaCliente')
									->where('FirmaCliente', '!=', '0')
									->first();

								// Si no se encuentra con FK_Gener = 0, buscar cualquier registro del servicio (fallback)
								if (!$firmaCliente) {
									$firmaCliente = DB::table('firmas_servicio')
										->where('FK_SolSer', $Solicitud->ID_SolSer)
										->whereNotNull('FirmaCliente')
										->where('FirmaCliente', '!=', '0')
										->first();
								}

								$rutaFirmaCliente = null;
								$firmaClienteBase64 = null;
								if ($firmaCliente && !empty($firmaCliente->FirmaCliente)) {
									// La ruta para la web (usando asset)
									$rutaFirma = 'storage/FirmasClientesExpress/' . $firmaCliente->FirmaCliente . '.png';
									// La ruta física del archivo en el sistema de archivos
									$rutaFirmaCompleta = storage_path('app/public/FirmasClientesExpress/' . $firmaCliente->FirmaCliente . '.png');
									if (file_exists($rutaFirmaCompleta)) {
										$rutaFirmaCliente = $rutaFirma;
										// Convertir la imagen a base64 para el PDF
										$imageData = file_get_contents($rutaFirmaCompleta);
										$firmaClienteBase64 = 'data:image/png;base64,' . base64_encode($imageData);
									}
								}

								// Asignar la ruta de la firma del cliente al objeto Solicitud
								$Solicitud->rutaFirmaCliente = $rutaFirmaCliente;
								$Solicitud->firmaClienteBase64 = $firmaClienteBase64;

								// Obtener datos del email
								$email = DB::table('solicitud_servicios')
									->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
									->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
									->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
									->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha','progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
									->where('solicitud_servicios.SolSerSlug', '=', $Solicitud->SolSerSlug)
									->where('progvehiculos.FK_ProgServi', '=', $Solicitud->ID_SolSer)
									->where('progvehiculos.ProgVehDelete', 0)
									->first();

								// Generar PDF según tipo
								if($certificado->CertType == 0) {
									$fechaEmision = Carbon::now();
									$primeraRecepcion = $Solicitud->programacionesrecibidas()->orderBy('ProgVehEntrada', 'asc')->first();
									$fechaRecepcion = ($primeraRecepcion && $primeraRecepcion->ProgVehEntrada)
										? Carbon::parse($primeraRecepcion->ProgVehEntrada)
										: $fechaEmision;
									$pdf = PDF::loadView('certificadosExpress.topdf', ['certificado' => $certificado, 'Solicitud' => $Solicitud, 'fechaEmision' => $fechaEmision, 'fechaRecepcion' => $fechaRecepcion]);
								} else {
									$fechaEmision = Carbon::now();
									$primeraRecepcion = $Solicitud->programacionesrecibidas()->orderBy('ProgVehEntrada', 'asc')->first();
									$fechaRecepcion = ($primeraRecepcion && $primeraRecepcion->ProgVehEntrada)
										? Carbon::parse($primeraRecepcion->ProgVehEntrada)
										: $fechaEmision;
									$pdf = PDF::loadView('certificadosExpress.topdfmanifesto', ['certificado' => $certificado, 'Solicitud' => $Solicitud, 'fechaEmision' => $fechaEmision, 'fechaRecepcion' => $fechaRecepcion]);
								}

								// Determinar email del cliente: preferir email de la sede si existe, si no usar el personal
								$clienteEmail = null;
								if (!empty($Solicitud->FK_SolSerSede)) {
									$sede = Sede::where('ID_Sede', $Solicitud->FK_SolSerSede)->first();
									if ($sede && !empty($sede->SedeEmail)) {
										$clienteEmail = $sede->SedeEmail;
									}
								}
							// fallback al email del personal relacionado
							if (!$clienteEmail && !empty($email->PersEmail)) {
								$clienteEmail = $email->PersEmail;
							}

							// Enviar un solo correo: PARA el cliente y con COPIA al buzón Express interno
							// Validar que todos los parámetros necesarios estén presentes
							if ($email && $pdf && $certificado && isset($certificado->CertType)) {
								try {
									if (!empty($clienteEmail)) {
										Mail::to($clienteEmail)
											->cc(self::MAIL_EXPRESS_INTERNO)
											->send(new SolSerExpressEmail($email, $pdf, $certificado));
									} else {
										// Si no hay correo de cliente, al menos notificar al buzón Express
										Mail::to(self::MAIL_EXPRESS_INTERNO)
											->send(new SolSerExpressEmail($email, $pdf, $certificado));
									}
							} catch (\Exception $e) {
								// Log del error pero no detener el flujo
								Log::error('Error al enviar correo en changestatus: ' . $e->getMessage(), [
									'SolSerSlug' => $Solicitud->SolSerSlug,
									'certificado_id' => $certificado->ID_Cert ?? null
								]);
							}
						} else {
							Log::warning('No se pudo enviar correo en changestatus - Parámetros faltantes', [
								'SolSerSlug' => $Solicitud->SolSerSlug,
								'email' => $email ? 'OK' : 'NULL',
								'pdf' => $pdf ? 'OK' : 'NULL',
								'certificado' => $certificado ? 'OK' : 'NULL',
								'CertType' => isset($certificado->CertType) ? $certificado->CertType : 'NO DEFINIDO'
							]);
							}
						}

						$Solicitud->SolSerDescript = $request->input('solserdescript');
							$Solicitud->save();

							$log = new audit();
							$log->AuditTabla="solicitud_servicios";
							$log->AuditType="Modificado Status";
							$log->AuditRegistro=$Solicitud->ID_SolSer;
							$log->AuditUser=Auth::user()->email;
							$log->Auditlog=$Solicitud->SolSerStatus;
							$log->save();

							$slug = $Solicitud->SolSerSlug;
							return redirect()->route('email-solser', compact('slug'));

						}
						break;
					case 'Facturada':
						if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES)){
							$Solicitud->SolSerStatus = 'Facturado';
						}
						break;
				}
			}
		}else{
			abort(403, 'el servicio ya ha sido certificado y no admite cambios de status');
		}
		$Solicitud->SolSerDescript = $request->input('solserdescript');
		$Solicitud->save();

		// Recargar el modelo desde la base de datos para asegurar que tenemos el estado actualizado
		$Solicitud->refresh();

		// Express: el recibo de material y demás avisos van por ServiceExpressController (p. ej. SolSerRME), no por el flujo de calificación de regulares.

		if ($Solicitud->SolSerStatus == 'Conciliado') {
			$this->solservdocstore($Solicitud->ID_SolSer);
		}

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="Modificado Status";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=[$Solicitud->SolSerStatus, $Solicitud->SolSerDescript];
		$log->save();


		/*se guarda la observacion de la modificacion del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = $Solicitud->SolSerStatus;
		$Observacion->ObsMensaje = $Solicitud->SolSerDescript;
		switch ($Solicitud->SolSerStatus) {
			case 'Aprobado':
				$Observacion->ObsTipo = 'cliente';
				break;

			case 'Programado':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Notificado':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Completado':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Conciliado':
				if (in_array(Auth::user()->UsRol, Permisos::CONDUCTOREXPRESS) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOREXPRESS)) {
					$Observacion->ObsTipo = 'prosarc';
				}else{
					$Observacion->ObsTipo = 'cliente';
				}
				break;

			case 'No Conciliado':
				$Observacion->ObsTipo = 'cliente';
				break;

			case 'Tratado':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Certificacion':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Corregido':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Residuo Faltante':
				$Observacion->ObsTipo = 'prosarc';
				break;

			case 'Facturado':
				$Observacion->ObsTipo = 'prosarc';
				break;

			default:
			$Observacion->ObsTipo = 'prosarc';
				break;
		}
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $Solicitud->ID_SolSer;
		$Observacion->save();

		switch($Solicitud->SolSerStatus){
			case 'Conciliado':
			case 'Tratado':
			case 'Facturado':
				return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $Solicitud->SolSerSlug]);
				break;
			case 'Aceptado':
				return redirect()->route('serviciosexpress.index');
				break;
			default:
				$slug = $Solicitud->SolSerSlug;
				return redirect()->route('email-solser', compact('slug'));
		}
	}

	public function repeat(Request $request, $slug)
	{
		$SolicitudOld = SolicitudServicio::where('SolSerSlug', $slug)->first();
		if (!$SolicitudOld) {
			abort(404, 'la solicitud que esta tratando de repetir no se encuentra en la base de datos');
		}

		$Cliente = Cliente::where('ID_Cli', $SolicitudOld->FK_SolSerCliente)->first();
        $Requerimiento = RequerimientosCliente::where('FK_RequeClient', $Cliente->ID_Cli)->first();

		if(!is_null($SolicitudOld)){
				$SolResOlds = SolicitudResiduo::where('FK_SolResSolSer', $SolicitudOld->ID_SolSer)->get();
				$SolicitudNew = new SolicitudServicio();
				$SolicitudNew->SolSerStatus = 'Aprobado';
				$SolicitudNew->SolSerAuditable = $SolicitudOld->SolSerAuditable;
				$SolicitudNew->SolResAuditoriaTipo = $SolicitudOld->SolResAuditoriaTipo;
				$SolicitudNew->SolSerTipo = $SolicitudOld->SolSerTipo;
				$SolicitudNew->SolSerNameTrans = $SolicitudOld->SolSerNameTrans;
				$SolicitudNew->SolSerNitTrans = $SolicitudOld->SolSerNitTrans;
				$SolicitudNew->SolSerAdressTrans = $SolicitudOld->SolSerAdressTrans;
				$SolicitudNew->SolSerCityTrans = $SolicitudOld->SolSerCityTrans;
				$SolicitudNew->SolSerConductor = $SolicitudOld->SolSerConductor;
				$SolicitudNew->SolSerVehiculo = $SolicitudOld->SolSerVehiculo;
				$SolicitudNew->SolSerTypeCollect = $SolicitudOld->SolSerTypeCollect;
				$SolicitudNew->SolSerCollectAddress = $SolicitudOld->SolSerCollectAddress;
				if ($Requerimiento->RequeCliBascula==0) {
					$SolicitudNew->SolSerBascula = 0;
				}else{
					$SolicitudNew->SolSerBascula = $SolicitudOld->SolSerBascula;
				}

				if ($Requerimiento->RequeCliCapacitacion==0) {
					$SolicitudNew->SolSerCapacitacion = 0;
				}else{
					$SolicitudNew->SolSerCapacitacion = $SolicitudOld->SolSerCapacitacion;
				}

				if ($Requerimiento->RequeCliMasPerson==0) {
					$SolicitudNew->SolSerMasPerson = 0;
				}else{
					$SolicitudNew->SolSerMasPerson = $SolicitudOld->SolSerMasPerson;
				}

				if ($Requerimiento->RequeCliVehicExclusive==0) {
					$SolicitudNew->SolSerVehicExclusive = 0;
				}else{
					$SolicitudNew->SolSerVehicExclusive = $SolicitudOld->SolSerVehicExclusive;
				}

				if ($Requerimiento->RequeCliPlatform==0) {
					$SolicitudNew->SolSerPlatform = 0;
				}else{
					$SolicitudNew->SolSerPlatform = $SolicitudOld->SolSerPlatform;
				}
				$SolicitudNew->SolSerDevolucion = $SolicitudOld->SolSerDevolucion;
				$SolicitudNew->SolSerDevolucionTipo = $SolicitudOld->SolSerDevolucionTipo;
				$SolicitudNew->FK_SolSerPersona = $SolicitudOld->FK_SolSerPersona;
				$SolicitudNew->FK_SolSerCliente = $SolicitudOld->FK_SolSerCliente;
				$SolicitudNew->SolServMailCopia = $SolicitudOld->SolServMailCopia;
				$SolicitudNew->SolSerSlug = hash('sha256', rand().time().$SolicitudNew->SolSerNameTrans);
				$SolicitudNew->SolSerDelete = 0;
				$SolicitudNew->SolSerDescript = $request->input('solserdescript');
				$SolicitudNew->save();

				foreach ($SolResOlds as $SolResOld) {
					$SolResNew = new SolicitudResiduo();
					$SolResNew->SolResKgEnviado = $SolResOld->SolResKgEnviado;
					$SolResNew->SolResKgRecibido = 0;
					$SolResNew->SolResKgConciliado = 0;
					$SolResNew->SolResKgTratado = 0;
					$SolResNew->SolResDelete = 0;
					$SolResNew->SolResTypeUnidad = $SolResOld->SolResTypeUnidad;
					$SolResNew->SolResCantiUnidad = $SolResOld->SolResCantiUnidad;
					$SolResNew->SolResEmbalaje = $SolResOld->SolResEmbalaje;
					$SolResNew->SolResAlto = $SolResOld->SolResAlto;
					$SolResNew->SolResAncho = $SolResOld->SolResAncho;
					$SolResNew->SolResProfundo = $SolResOld->SolResProfundo;
					$SolResNew->SolResSlug = hash('sha256', rand().time().$SolResNew->SolResKgEnviado);
					$SolResNew->FK_SolResRg = $SolResOld->FK_SolResRg;
					$SolResNew->FK_SolResSolSer = $SolicitudNew->ID_SolSer;
					/*se verifica el requerimiento actualmente ofertado para el residuo*/
					$respelgener= ResiduosGener::find($SolResOld->FK_SolResRg);

					$requerimientoOfertado = Requerimiento::with(['pretratamientosSelected'])
						->where('FK_ReqRespel', '=', $respelgener->FK_Respel)
						->where('ofertado', '=', 1)
						->where('forevaluation', '=', 1)
						->first();

					if ($requerimientoOfertado == null) {
						$SolicitudNew->delete();

						$log = new audit();
						$log->AuditTabla="solicitud_servicios";
						$log->AuditType="repetir fallido";
						$log->AuditRegistro=$SolicitudNew->ID_SolSer;
						$log->AuditUser=Auth::user()->email;
						$log->Auditlog=$SolicitudNew;
						$log->save();

						abort(404, 'el servicio no se puede repetir debido a que alguno de los residuos no posee tratamiento ofertado, Verifique con su asesor Comercial');
					}
					if ($requerimientoOfertado->ReqFotoDescargue==0) {
						$SolResNew->SolResFotoDescargue_Pesaje = 0;
					}else{
						$SolResNew->SolResFotoDescargue_Pesaje = $SolResOld->SolResFotoDescargue_Pesaje;
					}

					if ($requerimientoOfertado->ReqFotoDestruccion==0) {
						$SolResNew->SolResFotoTratamiento = 0;
					}else{
						$SolResNew->SolResFotoTratamiento = $SolResOld->SolResFotoTratamiento;
					}

					if ($requerimientoOfertado->ReqVideoDescargue==0) {
						$SolResNew->SolResVideoDescargue_Pesaje = 0;
					}else{
						$SolResNew->SolResVideoDescargue_Pesaje = $SolResOld->SolResVideoDescargue_Pesaje;
					}

					if ($requerimientoOfertado->ReqVideoDestruccion==0) {
						$SolResNew->SolResVideoTratamiento = 0;
					}else{
						$SolResNew->SolResVideoTratamiento = $SolResOld->SolResVideoTratamiento;
					}

					if ($requerimientoOfertado->ReqDevolucion==0) {
						$SolResNew->SolResDevolucion = 0;
					}else{
						$SolResNew->SolResDevolucion = $SolResOld->SolResDevolucion;
					}

					if ($requerimientoOfertado->ReqAuditoria==0) {
						$SolResNew->SolResAuditoria = 0;
					}else{
						$SolResNew->SolResAuditoria = $SolResOld->SolResAuditoria ?? 0;
					}
					$SolResNew->SolResDevolCantidad = $SolResOld->SolResDevolCantidad ?? 0;
					$SolResNew->SolResAuditoriaTipo = $SolResOld->SolResAuditoriaTipo;
					/*se verifica los requerimientos y pretratamientos seleccionados para copiarlos*/

					$nuevorequerimiento = $requerimientoOfertado->replicate();
					$nuevorequerimiento->ReqSlug= hash('md5', rand().time().$respelgener->FK_Respel);
					$nuevorequerimiento->forevaluation=0;
					$nuevorequerimiento->ofertado=0;
					$nuevorequerimiento->save();
					$nuevorequerimiento->pretratamientosSelected()->attach($requerimientoOfertado['pretratamientosSelected']);

					$tarifaparacopiar = Tarifa::with(['rangos'])
					->where('FK_TarifaReq', $requerimientoOfertado->ID_Req)->first();
					$nuevatarifa = $tarifaparacopiar->replicate();
					$nuevatarifa->FK_TarifaReq=$nuevorequerimiento->ID_Req;
					$nuevatarifa->save();

					foreach ($tarifaparacopiar->rangos as $rango) {
						$rangoparacopiar = Rango::find($rango->ID_Rango);
						$nuevarango = $rangoparacopiar->replicate();
						$nuevarango->FK_RangoTarifa = $nuevatarifa->ID_Tarifa;
						$nuevarango->save();
					}
					$SolResNew->FK_SolResRequerimiento = $nuevorequerimiento->ID_Req;
					$SolResNew->save();
				}

			$SolicitudServicio = $SolicitudNew;

			if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
				/*se guarda la observacion inicial del servicio repetido*/
				$Observacion = new Observacion();
				$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
				$Observacion->ObsMensaje = $SolicitudServicio->SolSerDescript;
				$Observacion->ObsTipo = 'cliente';
				$Observacion->ObsRepeat = 1;
				$Observacion->ObsDate = now();
				$Observacion->ObsUser = Auth::user()->email;
				$Observacion->ObsRol = Auth::user()->UsRol;
				$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
				$Observacion->save();

			} else {

				/* se incluye la primera observacion del cliente del servicio original */
				$observacionOriginal = Observacion::where('FK_ObsSolSer', $SolicitudOld->ID_SolSer)->first();
				/*se guarda la observacion inicial del servicio repetido*/
				$Observacion = new Observacion();
				$Observacion->ObsStatus = $observacionOriginal->ObsStatus;
				$Observacion->ObsMensaje = $observacionOriginal->ObsMensaje;
				$Observacion->ObsTipo = $observacionOriginal->ObsTipo;
				$Observacion->ObsRepeat = 1;
				$Observacion->ObsDate = $observacionOriginal->ObsDate;
				$Observacion->ObsUser = $observacionOriginal->ObsUser;
				$Observacion->ObsRol = $observacionOriginal->ObsRol;
				$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
				$Observacion->save();


				/*se guarda la observacion inicial del servicio repetido*/
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
			}


						// se verifica si el cliente tiene comercial asignado
			$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudNew->FK_SolSerCliente)->first();
			if ($SolicitudServicio['cliente']->CliComercial <> null) {
				$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
			} else {
				$comercial = "";
			}
			$destinatarios = [self::MAIL_EXPRESS_INTERNO];

			$SolicitudServicio['comercial'] = $comercial;
			$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudNew->FK_SolSerPersona)->first();

			if ($SolicitudServicio->SolServMailCopia && $SolicitudServicio->SolServMailCopia != "null") {
				$mailsCopia = json_decode($SolicitudServicio->SolServMailCopia);
				if (is_array($mailsCopia)) {
					foreach ($mailsCopia as $key => $value) {
						array_push($destinatarios, $value);
					}
				}
			}

			// Notificación de creación de solicitud deshabilitada - el cliente solo debe recibir recibo de material y certificado
			// if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			// 	Mail::to($SolicitudServicio['personalcliente']->PersEmail)->cc($destinatarios)->send(new NewSolServEmail($SolicitudServicio));
			// }else{
			// 	Mail::to($SolicitudServicio['personalcliente']->PersEmail)->cc($destinatarios)->send(new NewSolServProsarcEmail($SolicitudServicio));
			// }

			// Si se indicó fecha y hora, crear programación directamente sin pasar por el calendario
			if ($request->filled('progveh_fecha') && $request->filled('progveh_salida')) {
				$progOld = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudOld->ID_SolSer)
					->where('ProgVehDelete', 0)->first();
				$progData = [
					'_token' => $request->input('_token'),
					'FK_ProgServi' => $SolicitudNew->ID_SolSer,
					'ProgVehFecha' => $request->input('progveh_fecha'),
					'ProgVehSalida' => $request->input('progveh_salida'),
					'from_repeat_redirect' => $SolicitudNew->SolSerSlug,
				];
				if ($request->filled('FK_ProgVehiculo') && $request->filled('FK_ProgConductor')) {
					$progData['typetransportador'] = 0;
					$progData['FK_ProgVehiculo'] = $request->input('FK_ProgVehiculo');
					$progData['FK_ProgConductor'] = $request->input('FK_ProgConductor');
					$progData['FK_ProgAyudante'] = $request->input('FK_ProgAyudante');
					$progData['ProgVehColor'] = ($progOld && $progOld->ProgVehColor) ? $progOld->ProgVehColor : '#66b032';
					$progData['ProgVehPrecintos'] = $progOld ? $progOld->ProgVehPrecintos : null;
				} elseif ($progOld && $progOld->ProgVehtipo == 1) {
					$progData['typetransportador'] = 0;
					$progData['FK_ProgVehiculo'] = $progOld->FK_ProgVehiculo;
					$progData['FK_ProgConductor'] = $progOld->FK_ProgConductor;
					$progData['FK_ProgAyudante'] = $progOld->FK_ProgAyudante;
					$progData['ProgVehColor'] = $progOld->ProgVehColor ?? '#66b032';
					$progData['ProgVehPrecintos'] = $progOld->ProgVehPrecintos;
				} elseif ($progOld && $progOld->ProgVehtipo == 2) {
					$progData['typetransportador'] = 1;
					$progData['vehicalqui'] = $progOld->FK_ProgVehiculo;
					$progData['FK_ProgAyudante'] = $progOld->FK_ProgAyudante;
					$progData['ProgVehDocConductorEXT'] = $progOld->ProgVehDocConductorEXT;
					$progData['ProgVehNameConductorEXT'] = $progOld->ProgVehNameConductorEXT;
					$progData['ProgVehDocAuxiliarEXT'] = $progOld->ProgVehDocAuxiliarEXT;
					$progData['ProgVehNameAuxiliarEXT'] = $progOld->ProgVehNameAuxiliarEXT;
					$progData['ProgVehPlacaEXT'] = $progOld->ProgVehPlacaEXT;
					$progData['ProgVehTipoEXT'] = $progOld->ProgVehTipoEXT;
					$vehic = DB::table('vehiculos')->where('ID_Vehic', $progOld->FK_ProgVehiculo)->first();
					if ($vehic && $vehic->FK_VehiSede) {
						$sede = DB::table('sedes')->where('ID_Sede', $vehic->FK_VehiSede)->first();
						if ($sede) {
							$cli = DB::table('clientes')->where('ID_Cli', $sede->FK_SedeCli)->first();
							$progData['transport'] = $cli->CliSlug ?? null;
						}
					}
				}
				$progRequest = Request::create('/programacion-express', 'POST', array_merge($request->all(), $progData));
				$progRequest->setLaravelSession($request->session());
				$progRequest->setUserResolver($request->getUserResolver());
				return app(ProgramacionExpressController::class)->store($progRequest);
			}
			return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $SolicitudNew->SolSerSlug]);
		}
		else{
			abort(404, 'la solicitud que esta tratando de repetir no se encuentra en la base de datos');
		}
	}
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
			$Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
			if (!$Solicitud) {
				abort(404);
			}
			if($Solicitud->SolSerStatus === 'Tratado' || $Solicitud->SolSerStatus === 'Certificacion' || $Solicitud->SolSerStatus === 'Completado'){
				abort(403);
			}
			if($Solicitud->SolSerCityTrans <> null){
				$Municipio = Municipio::select('FK_MunCity')->where('ID_Mun', $Solicitud->SolSerCityTrans)->first();
				$Departamento = Departamento::where('ID_Depart', $Municipio->FK_MunCity)->first();
				$Municipios = Municipio::where('FK_MunCity', $Departamento->ID_Depart)->get();
			}
			if($Solicitud->FK_SolSerCollectMun <> null){
				$Municipio2 = Municipio::select('FK_MunCity')->where('ID_Mun', $Solicitud->FK_SolSerCollectMun)->first();
				$Departamento2 = Departamento::where('ID_Depart', $Municipio2->FK_MunCity)->first();
				$Municipios2 = Municipio::where('FK_MunCity', $Departamento2->ID_Depart)->get();
			}
			$Departamentos = Departamento::all();
			$Cliente = Cliente::where('ID_Cli', $Solicitud->FK_SolSerCliente)->first();
            $Requerimientos = RequerimientosCliente::where('FK_RequeClient', $Cliente->ID_Cli)->get();
			$Sedes = Sede::select('SedeSlug','SedeName', 'ID_Sede')->where('FK_SedeCli', $Cliente->ID_Cli)->get();
			$SGeneradors = DB::table('gener_sedes')
				->join('generadors', 'gener_sedes.FK_GSede', '=', 'generadors.ID_Gener')
				->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('gener_sedes.GSedeSlug', 'gener_sedes.GSedeName', 'generadors.GenerName')
				->where('clientes.ID_Cli', userController::IDClienteSegunUsuario())
				->get();
			$Persona = Personal::where('ID_Pers', $Solicitud->FK_SolSerPersona)
				->select('PersSlug','PersFirstName','PersLastName')
				->first();
			$Personals = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('personals.PersSlug', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersEmail')
				->where('clientes.ID_Cli', userController::IDClienteSegunUsuario())
				->where('personals.PersDelete', 0)
				->get();
			$KGenviados = DB::table('solicitud_residuos')
				->select('SolResKgEnviado')
				->where('FK_SolResSolSer', $Solicitud->ID_SolSer)
				->get();
			$totalenviado = 0;
			foreach ($KGenviados as $KGenviado) {
				$totalenviado = $totalenviado + $KGenviado->SolResKgEnviado;
			}
			return view('serviciosexpress.edit', compact('Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios', 'Departamento2','Municipios2', 'Sedes', 'totalenviado', 'Requerimientos'));
		}
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
		// return $request;
		$SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();
		if (!$SolicitudServicio) {
			abort(404);
		}
		$SolicitudServicio->SolServMailCopia = json_encode($request->input('SolServMailCopia'));

		if($SolicitudServicio->SolSerStatus === 'Programado'){
			if($request->input('SolSerTransportador') <> null){
				if($request->input('SolSerTransportador') <> 98){
					$cliente = DB::table('clientes')
						->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
						->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
						->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.ID_Mun')
						->where('ID_Cli', userController::IDClienteSegunUsuario())
						->first();
					$transportadorname = $cliente->CliName;
					$transportadornit = $cliente->CliNit;
					$transportadoradress = $cliente->SedeAddress;
					$transportadorcity = $cliente->ID_Mun;
				}
				else{
					$transportadorname = $request->input('SolSerNameTrans');
					$transportadornit = $request->input('SolSerNitTrans');
					$transportadoradress = $request->input('SolSerAdressTrans');
					$transportadorcity = $request->input('SolSerCityTrans');
				}
				$SolicitudServicio->SolSerTipo = "Externo";
				$SolicitudServicio->SolSerNameTrans = $transportadorname;
				$SolicitudServicio->SolSerNitTrans = $transportadornit;
				$SolicitudServicio->SolSerAdressTrans = $transportadoradress;
				$SolicitudServicio->SolSerCityTrans = $transportadorcity;
				$SolicitudServicio->SolSerConductor =  $request->input('SolSerConductor');
				$SolicitudServicio->SolSerVehiculo = $request->input('SolSerVehiculo');
			}
			$SolicitudServicio->FK_SolSerPersona = Personal::select('ID_Pers')->where('PersSlug',$request->input('FK_SolSerPersona'))->first()->ID_Pers;
			$SolicitudServicio->save();

			$log = new audit();
			$log->AuditTabla="solicitud_servicios";
			$log->AuditType="Modificado";
			$log->AuditRegistro=$SolicitudServicio->ID_SolSer;
			$log->AuditUser=Auth::user()->email;
			$log->Auditlog=json_encode($request->all());
			$log->save();

			/*se guarda la observacion de la modificacion del servicio*/
			$Observacion = new Observacion();
			$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
			$Observacion->ObsMensaje = $SolicitudServicio->SolSerDescript;
			$Observacion->ObsTipo = 'cliente';
			$Observacion->ObsRepeat = 1;
			$Observacion->ObsDate = now();
			$Observacion->ObsUser = Auth::user()->email;
			$Observacion->ObsRol = Auth::user()->UsRol;
			$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
			$Observacion->save();

			return redirect()->route('serviciosexpress.show', ['id' => $SolicitudServicio->SolSerSlug]);
		}
		switch ($request->input('SolResAuditoriaTipo')) {
			case 99:
				$SolicitudServicio->SolSerAuditable = 1;
				$SolicitudServicio->SolResAuditoriaTipo = "Virtual";
				break;
			case 98:
				$SolicitudServicio->SolSerAuditable = 1;
				$SolicitudServicio->SolResAuditoriaTipo = "Presencial";
				break;
			case 97:
				$SolicitudServicio->SolSerAuditable = 0;
				$SolicitudServicio->SolResAuditoriaTipo = "No Auditable";
				break;
		}
		$collect = null;
		if($request->input('SolSerTipo') == 99){
			$cliente = DB::table('clientes')
				->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
				->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
				->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.ID_Mun')
				->where('ID_Cli', 1)
				->first();
			$tipo = "Interno";
			$transportadorname = $cliente->CliName;
			$transportadornit = $cliente->CliNit;
			$transportadoradress = $cliente->SedeAddress;
			$transportadorcity = $cliente->ID_Mun;
			$conductor = null;
			$vehiculo = null;
			$collect = $request->input('SolSerTypeCollect');
		}
		else{
			if($request->input('SolSerTransportador') <> 98){
				$cliente = DB::table('clientes')
					->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
					->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.ID_Mun')
					->where('ID_Cli', userController::IDClienteSegunUsuario())
					->first();
				$transportadorname = $cliente->CliName;
				$transportadornit = $cliente->CliNit;
				$transportadoradress = $cliente->SedeAddress;
				$transportadorcity = $cliente->ID_Mun;
			}
			else{
				$transportadorname = $request->input('SolSerNameTrans');
				$transportadornit = $request->input('SolSerNitTrans');
				$transportadoradress = $request->input('SolSerAdressTrans');
				$transportadorcity = $request->input('SolSerCityTrans');
			}
			$tipo = "Externo";
			$conductor = $request->input('SolSerConductor');
			$vehiculo = $request->input('SolSerVehiculo');
		}
		$direccioncollect = null;
		switch ($request->input('SolSerTypeCollect')) {
			case 99:
				$direccioncollect = "Recolección en la sede de cada generador";
				break;
			case 98:
				$sede = Sede::select('ID_Sede')->where('SedeSlug', $request->input('SedeCollect'))->first();
				$direccioncollect = $sede->ID_Sede;
				break;
			case 97:
				$direccioncollect = $request->input('AddressCollect');
				$SolicitudServicio->FK_SolSerCollectMun = $request->input('FK_SolSerCollectMun');
				break;
		}
		if(isset($request['SupportPay'])){
			if($SolicitudServicio->SolSerSupport <> null && file_exists(public_path().'/img/SupportPay/'.$SolicitudServicio->SolSerSupport)){
				unlink(public_path().'/img/SupportPay/'.$SolicitudServicio->SolSerSupport);
			}
			$fileSupport = $request['SupportPay'];
			$nameSupport = hash('sha256', rand().time().$fileSupport->getClientOriginalName()).'.pdf';
			$fileSupport->move(public_path().'\img\SupportPay/',$nameSupport);
			$SolicitudServicio->SolSerSupport = $nameSupport;
		}
		$SolicitudServicio->SolSerTipo = $tipo;
		$SolicitudServicio->SolSerNameTrans = $transportadorname;
		$SolicitudServicio->SolSerNitTrans = $transportadornit;
		$SolicitudServicio->SolSerAdressTrans = $transportadoradress;
		$SolicitudServicio->SolSerCityTrans = $transportadorcity;
		$SolicitudServicio->SolSerConductor = $conductor;
		$SolicitudServicio->SolSerVehiculo = $vehiculo;
		$SolicitudServicio->SolSerTypeCollect = $collect;
		$SolicitudServicio->SolSerCollectAddress = $direccioncollect;
		if($request->input('SolSerBascula')){
			$SolicitudServicio->SolSerBascula = 1;
		}
		else{
			$SolicitudServicio->SolSerBascula = null;
		}
		if($request->input('SolSerCapacitacion')){
			$SolicitudServicio->SolSerCapacitacion = 1;
		}
		else{
			$SolicitudServicio->SolSerCapacitacion = null;
		}
		if($request->input('SolSerMasPerson')){
			$SolicitudServicio->SolSerMasPerson = 1;
		}
		else{
			$SolicitudServicio->SolSerMasPerson = null;
		}
		if($request->input('SolSerVehicExclusive')){
			$SolicitudServicio->SolSerVehicExclusive = 1;
		}
		else{
			$SolicitudServicio->SolSerVehicExclusive = null;
		}
		if($request->input('SolSerPlatform')){
			$SolicitudServicio->SolSerPlatform = 1;
		}
		else{
			$SolicitudServicio->SolSerPlatform = null;
		}
		if($request->input('SolSerDevolucion')){
			$SolicitudServicio->SolSerDevolucion = 1;
			$SolicitudServicio->SolSerDevolucionTipo = $request->input('SolSerDevolucionTipo');
		}
		else{
			$SolicitudServicio->SolSerDevolucion = null;
			$SolicitudServicio->SolSerDevolucionTipo = null;
		}
		$SolicitudServicio->FK_SolSerPersona = Personal::select('ID_Pers')->where('PersSlug',$request->input('FK_SolSerPersona'))->first()->ID_Pers;
		$SolicitudServicio->FK_SolSerCliente = userController::IDClienteSegunUsuario();
		$SolicitudServicio->SolSerDescript = $request->input('SolSerDescript');
		$SolicitudServicio->save();

		if(!is_null($request->input('SGenerador'))){
			$this->createSolRes($request, $SolicitudServicio->ID_SolSer);
		}

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="Modificado";
		$log->AuditRegistro=$SolicitudServicio->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

		/*se guarda la observacion de la modificacion del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
		$Observacion->ObsMensaje = $SolicitudServicio->SolSerDescript;
		$Observacion->ObsTipo = 'cliente';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
		$Observacion->save();

		return redirect()->to('/serviciosexpress/'.$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();

		if (!$SolicitudServicio) {
			abort(404, 'no se pudo eliminar la solicitud de servicio ya que no se encuentra en la base da datos');
		}

		switch ($SolicitudServicio->SolSerStatus) {
			case 'Pendiente':
			case 'Aceptado':
			case 'Programado':
			case 'Notificado':
			case 'Aprobado':

				$documentos = Documento::where('FK_CertSolser', $SolicitudServicio->ID_SolSer)->get();

				foreach ($documentos as $key => $documento) {
					$docdato = DocDato::where('FK_DatoDoc', $documento->ID_Doc)->get();

					foreach ($docdato as $key => $dato) {
							DocDato::destroy($dato->ID_Dato);
					}
					Documento::destroy($documento->ID_Doc);
				}

				SolicitudServicio::destroy($SolicitudServicio->ID_SolSer);

				break;

			default:
				abort(503, 'el servicio no puede ser eliminado si ya fue recibido en Planta');
				break;
		}


		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="Eliminado";
		$log->AuditRegistro=$SolicitudServicio->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$SolicitudServicio->SolSerDelete;
		$log->save();
		$SolicitudServicio->save();

		return redirect()->route('serviciosexpress.index');
	}

	/*
	*
	* Create from solicitud de residuo
	*
	*/
	public function createSolRes($request, $ID_SolSer)
	{
		foreach ($request->input('SGenerador') as $Generador => $value) {
			for ($y=0; $y < count($request['FK_SolResRg'][$Generador]); $y++) {
				$SolicitudResiduo = new SolicitudResiduo();
				$SolicitudResiduo->SolResKgEnviado = $request['SolResKgEnviado'][$Generador][$y];
				if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR)||in_array(Auth::user()->UsRol, Permisos::CONDUCTOR)){
					$SolicitudResiduo->SolResKgRecibido = $request['SolResKgEnviado'][$Generador][$y];
				} else {
					$SolicitudResiduo->SolResKgRecibido = 0;
				}
				$SolicitudResiduo->SolResKgConciliado = 0;
				$SolicitudResiduo->SolResKgTratado = 0;
				$SolicitudResiduo->SolResDelete = 0;
				$SolicitudResiduo->SolResSlug = hash('sha256', rand().time().$SolicitudResiduo->SolResKgEnviado);
				$SolicitudResiduo->FK_SolResSolSer = $ID_SolSer;
				if ((isset($request['SolResTypeUnidad'][$Generador][$y]))){
					if($request['SolResTypeUnidad'][$Generador][$y] == 99){
						$SolicitudResiduo->SolResTypeUnidad = "Unidad";
					}
					else if($request['SolResTypeUnidad'][$Generador][$y] == 98){
						$SolicitudResiduo->SolResTypeUnidad = "Litros";
					}
					if (isset($request['SolResCantiUnidad'][$Generador][$y])&&$request['SolResCantiUnidad'][$Generador][$y] != null) {
						$SolicitudResiduo->SolResCantiUnidad = $request['SolResCantiUnidad'][$Generador][$y];
						$SolicitudResiduo->SolResCantiUnidadConciliada = 0;
						$SolicitudResiduo->SolResCantiUnidadRecibida = 0;
					}else {
						$SolicitudResiduo->SolResCantiUnidad = 0;
						$SolicitudResiduo->SolResCantiUnidadConciliada = 0;
						$SolicitudResiduo->SolResCantiUnidadRecibida = 0;
					}
				}

				switch ($request['SolResEmbalaje'][$Generador][$y]) {
					case 99:
						$SolicitudResiduo->SolResEmbalaje = "Sacos/Bolsas";
						break;
					case 98:
						$SolicitudResiduo->SolResEmbalaje = "Bidones Pequeños";
						break;
					case 97:
						$SolicitudResiduo->SolResEmbalaje = "Bidones Grandes";
						break;
					case 96:
						$SolicitudResiduo->SolResEmbalaje = "Estibas";
						break;
					case 95:
						$SolicitudResiduo->SolResEmbalaje = "Garrafones/Jerricanes";
						break;
					case 94:
						$SolicitudResiduo->SolResEmbalaje = "Cajas";
						break;
					case 93:
						$SolicitudResiduo->SolResEmbalaje = "Cuñetes";
						break;
					case 92:
						$SolicitudResiduo->SolResEmbalaje = "Big Bags";
						break;
					case 91:
						$SolicitudResiduo->SolResEmbalaje = "Isotanques";
						break;
					case 90:
						$SolicitudResiduo->SolResEmbalaje = "Tachos";
						break;
					case 89:
						$SolicitudResiduo->SolResEmbalaje = "Embalajes Compuestos";
						break;
					case 88:
						$SolicitudResiduo->SolResEmbalaje = "Granel";
						break;
					case 87:
						$SolicitudResiduo->SolResEmbalaje = "Canecas 55 gal.";
						break;
					case 86:
						$SolicitudResiduo->SolResEmbalaje = "Canecas 05 gal.";
						break;
				}

				$SolicitudResiduo->FK_SolResRg = ResiduosGener::select('ID_SGenerRes')->where('SlugSGenerRes',$request['FK_SolResRg'][$Generador][$y])->first()->ID_SGenerRes;
				/*validar el residuo para saber el tratamiento*/
				$respelref = ResiduosGener::select('FK_Respel')->where('SlugSGenerRes',$request['FK_SolResRg'][$Generador][$y])->first()->FK_Respel;

				// Primero intentamos encontrar un requerimiento con todas las condiciones
				$requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
					->where('FK_ReqRespel', $respelref)
					->where('ofertado', 1)
					->where('forevaluation', 1)
					->first();

				// Si no encontramos uno con todas las condiciones, buscamos cualquier requerimiento para ese respel
				if (!$requerimientoparacopiar) {
					$requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
						->where('FK_ReqRespel', $respelref)
						->first();
				}

				// Si aún no encontramos un requerimiento, creamos uno nuevo
				if (!$requerimientoparacopiar) {
					$nuevorequerimiento = new Requerimiento();
					$nuevorequerimiento->FK_ReqRespel = $respelref;
					$nuevorequerimiento->ReqSlug = hash('md5', rand().time().$respelref);
					$nuevorequerimiento->forevaluation = 0;
					$nuevorequerimiento->ofertado = 0;
					$nuevorequerimiento->save();
				} else {
					$nuevorequerimiento = $requerimientoparacopiar->replicate();
					$nuevorequerimiento->ReqSlug = hash('md5', rand().time().$respelref);
					$nuevorequerimiento->forevaluation = 0;
					$nuevorequerimiento->ofertado = 0;
					$nuevorequerimiento->save();

					if ($requerimientoparacopiar->pretratamientosSelected) {
						$nuevorequerimiento->pretratamientosSelected()->attach($requerimientoparacopiar->pretratamientosSelected);
					}
				}

				// Intentamos copiar la tarifa si existe
				$tarifaparacopiar = null;
				if ($requerimientoparacopiar) {
					$tarifaparacopiar = Tarifa::with(['rangos'])
						->where('FK_TarifaReq', $requerimientoparacopiar->ID_Req)
						->first();
				}

				if ($tarifaparacopiar) {
					$nuevatarifa = $tarifaparacopiar->replicate();
					$nuevatarifa->FK_TarifaReq = $nuevorequerimiento->ID_Req;
					$nuevatarifa->save();

					foreach ($tarifaparacopiar->rangos as $rango) {
						$rangoparacopiar = Rango::find($rango->ID_Rango);
						$nuevarango = $rangoparacopiar->replicate();
						$nuevarango->FK_RangoTarifa = $nuevatarifa->ID_Tarifa;
						$nuevarango->save();
					}
				}

				$SolicitudResiduo->FK_SolResRequerimiento = $nuevorequerimiento->ID_Req;
				$SolicitudResiduo->save();
			}
		}
	}

	/**
	 * list the related documents for specific solserv.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function solservdocindex($id)
	{
		if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			$SolicitudServicio = DB::table('solicitud_servicios')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->select('solicitud_servicios.*','personals.PersFirstName','personals.PersLastName','personals.PersEmail')
			->where('solicitud_servicios.SolSerSlug', $id)
			->where('solicitud_servicios.SolSerStatus', 'Certificacion')
			->first();
			if (!$SolicitudServicio) {
				abort(403,'Sus residuos aun no han sido certificados');
			}
			$certificados = Certificado::where(function($query) use ($SolicitudServicio){
			    $UserSedeID = DB::table('personals')
			    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
			    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
			    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
			    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
			    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
			    ->value('clientes.ID_Cli');

			    $query->where('FK_CertCliente', $UserSedeID);
			    $query->where('CertAuthJo', '!=', 0);
			    $query->where('CertAuthJl', '!=', 0);
			    $query->where('CertAuthDp', '!=', 0);
			    $query->where('FK_CertSolser', $SolicitudServicio->ID_SolSer);

			})
			->with(['tratamiento'])
			->get();
		}else{
			$SolicitudServicio = DB::table('solicitud_servicios')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->select('solicitud_servicios.*','personals.PersFirstName','personals.PersLastName','personals.PersEmail')
			->where('solicitud_servicios.SolSerSlug', $id)
			->first();
			if (!$SolicitudServicio) {
				abort(404);
			}


			$SolicitudServicio->cliente = Cliente::where('ID_CLi', $SolicitudServicio->FK_SolSerCliente)->first(['CliName', 'CliSlug']);

			if ($SolicitudServicio->cliente->CliCategoria == 'ClientePrepago') {
				$certificados = CertificadoExpress::with(['certdato.solres'])
				->where('FK_CertSolser', $SolicitudServicio->ID_SolSer)
				->get();

			} else {
				$certificados = Certificado::with(['certdato.solres'])
				->where('FK_CertSolser', $SolicitudServicio->ID_SolSer)
				->get();
			}
		}
		/* validacion para encontrar la fecha de recepción en planta del servicio */
		$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)->programacionesrecibidas()->first();
		if($fechaRecepcion){
			$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
		}else{
			$SolicitudServicio->recepcion = null;
		}

		// return $certificados;
		return view('solicitud-serv.documentos', compact('SolicitudServicio', 'certificados'));
	}

	public function sendtobilling($id)
	{
		$Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
		if (!$Solicitud) {
			abort(404);
		}

		$Solicitud->SolServCertStatus=1;
		$Solicitud->save();

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="enviado a facturacion";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$Solicitud->SolServCertStatus;
		$log->save();

		return redirect()->to('/serviciosexpress/'.$id);
	}

	public function updateRms(Request $request, $id)
	{
		$Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
		if (!$Solicitud) {
			abort(404);
		}

		$Solicitud->SolSerRMs=$request->input('SolServRM');
		$Solicitud->save();

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="actualizados los RMs";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$request;
		$log->save();

		return redirect()->to('/serviciosexpress/'.$id);
	}

	public function solservdocstore($id)
	{

		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $id)->first();
		$serviciovalidado = $id;
		/*cuenta los diferentes generadores*/
		$generadoresdelasolicitud = GenerSede::whereHas('resgener.solres', function ($query) use ($serviciovalidado) {
		    $query->where('solicitud_residuos.FK_SolResSolSer', $serviciovalidado);
		})
		->with(['resgener' => function ($query) use ($serviciovalidado){
		    $query->with(['solres' => function ($query) use ($serviciovalidado){
		    	$query->where('FK_SolResSolSer', $serviciovalidado);
		    	$query->with(['requerimiento.tratamiento.gestor', 'requerimiento:ID_Req,FK_ReqTrata']);
		    }]);
		    $query->whereHas('solres', function ($query) use ($serviciovalidado){
		    	$query->where('FK_SolResSolSer', $serviciovalidado);
		    });
		}])
		->get();
		// return $generadoresdelasolicitud;
		/*consulta para el cliente de esta solicitud*/
		$cliente = Cliente::whereHas('sedes.generador', function ($query) use ($generadoresdelasolicitud) {
		    $query->where('generadors.ID_Gener', $generadoresdelasolicitud[0]->FK_GSede);
		})->first();
		foreach ($generadoresdelasolicitud as $genersede) {
			foreach ($genersede->resgener as $resgener) {
				foreach ($resgener->solres as $key) {
					if ($key->SolResKgConciliado > 0) {
						switch ($key->requerimiento->tratamiento->TratTipo) {
							case '0':
								// "tratamiento tipo: interno; Certificado";

								$certificadoprevio = Certificado::where('FK_CertTrat', $key->requerimiento->tratamiento->ID_Trat)
								->where('FK_CertSolser', $id)
								->where('FK_CertGenerSede', $genersede->ID_GSede)
								->first();

								$gestor = Sede::where('ID_Sede', $key->requerimiento->tratamiento->FK_TratProv)
								->first();

								if ((isset($certificadoprevio))&&($certificadoprevio->FK_CertTrat == $key->requerimiento->tratamiento->ID_Trat)&&($certificadoprevio->FK_CertGenerSede == $genersede->ID_GSede)) {

									$dato = new Certdato;
									$dato->FK_DatoCert = $certificadoprevio->ID_Cert;
									$dato->FK_DatoCertSolRes = $key->ID_SolRes;
									$dato->save();

								}else{

									$certificado = new Certificado;
									if ($key->requerimiento->tratamiento->TratName == 'TermoDestrucción') {
										$certificado->CertType = 0;
										$certificado->CertObservacion = "certificado con observacion generica";
									}else{
										$certificado->CertType = 1;
										$certificado->CertObservacion = "manifiesto con observacion generica";
									}
									$certificado->CertNumero = "";
									$certificado->CertManifNumero = "";
									$certificado->CertManifPrepend = "";
									$certificado->CertiEspName = "";
									$certificado->CertiEspValue = "";
									$certificado->CertSlug = hash('sha256', rand().time());
									$certificado->CertSrc = 'CertificadoDefault.pdf';
									// $certificado->CertNumRm = "C-130";
									$certificado->CertAuthHseq = 0;
									$certificado->CertAuthJl = 0;
									$certificado->CertAuthDp = 0;
									$certificado->CertAuthJo = 0;
									$certificado->CertAnexo = "anexo de certificado ".$key->requerimiento->tratamiento->TratName.$key->requerimiento->tratamiento->FK_TratProv;
									$certificado->FK_CertSolser = $id;
									$certificado->FK_CertCliente = $cliente->ID_Cli;
									$certificado->FK_CertGenerSede = $genersede->ID_GSede;
									$certificado->FK_CertGestor = $key->requerimiento->tratamiento->gestor->FK_SedeCli;
									$certificado->FK_CertTrat = $key->requerimiento->tratamiento->ID_Trat;
									if ($SolicitudServicio->SolSerTipo == 'Externo') {
										$certificado->FK_CertTransp = $cliente->ID_Cli;
									}else{
										$certificado->FK_CertTransp = 1;
									}

									$certificado->SolicitudServicio->SolicitudResiduo = $certificado->SolicitudServicio->SolicitudResiduo->map(function ($item) {
										$rm = SolicitudResiduo::where('SolResSlug', $item->SolResSlug)->first('SolResRM');
										$item->SolResRM2 = $rm->SolResRM;
										return $item;
									});
									$certificado->save();

									$dato = new Certdato;
									$dato->FK_DatoCert = $certificado->ID_Cert;
									$dato->FK_DatoCertSolRes = $key->ID_SolRes;
									$dato->save();

								}

								break;

							case '1':
								// "tratamiento tipo: externo ; manifiesto";
								/*se verifica si ya existe un documento con ese tratamiento para esa solicitud de servicio*/
								$manifiestoprevio = Manifiesto::where('FK_ManifTrat', $key->requerimiento->tratamiento->ID_Trat)
								->where('FK_ManifSolser', $id)
								->first();

								if ((isset($manifiestoprevio))&&($manifiestoprevio->FK_ManifTrat == $key->requerimiento->tratamiento->ID_Trat)) {

									$dato = new Manifdato;
									$dato->FK_DatoManif = $manifiestoprevio->ID_Manif;
									$dato->FK_DatoManifSolRes = $key->ID_SolRes;
									$dato->save();

								}else{

									$manifiesto = new Manifiesto;
									$manifiesto->ManifNumero = "";
									$manifiesto->ManifiEspName = "";
									$manifiesto->ManifiEspValue = "";
									$manifiesto->ManifObservacion = "manifiesto con observacion generica";
									$manifiesto->ManifSlug = hash('sha256', rand().time());
									$manifiesto->ManifSrc = 'ManifiestoDefault.pdf';
									$manifiesto->ManifNumRm = "M-16";
									$manifiesto->ManifAuthHseq = 0;
									$manifiesto->ManifAuthJl = 0;
									$manifiesto->ManifAuthDp = 0;
									$manifiesto->ManifAuthJo = 0;
									$manifiesto->ManifAnexo = "anexo de manifiesto ".$key->requerimiento->tratamiento->TratName.$key->requerimiento->tratamiento->FK_TratProv;
									$manifiesto->FK_ManifSolser = $id;
									$manifiesto->FK_ManifCliente = $cliente->ID_Cli;
									$manifiesto->FK_ManifGenerSede = $genersede->ID_GSede;
									$manifiesto->FK_ManifGestor = $key->requerimiento->tratamiento->gestor->FK_SedeCli;
									$manifiesto->FK_ManifTrat = $key->requerimiento->tratamiento->ID_Trat;
									if ($SolicitudServicio->SolSerTipo == 'Externo') {
										$manifiesto->FK_ManifTransp = $cliente->ID_Cli;
									}else{
										$manifiesto->FK_ManifTransp = 1;
									}
									$manifiesto->save();

									$dato = new Manifdato;
									$dato->FK_DatoManif = $manifiesto->ID_Manif;
									$dato->FK_DatoManifSolRes = $key->ID_SolRes;
									$dato->save();
								}

								break;

							default:
								return back()->withErrors(['msg' => ['alguno de los residuos no posee tratamiento asignado favor verifica que su asesor comercial la evaluacion de los residuos.']]);
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * muestra el formulario para añadir residuos adicionales al servicio en status Residuo Faltante.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function addRespel($id)
	{
		if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOREXPRESS) || in_array(Auth::user()->UsRol, Permisos::CONDUCTOREXPRESS)){
			$Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
			if (!$Solicitud) {
				abort(404);
			}
			// if($Solicitud->SolSerStatus !== 'Residuo Faltante'){
			// 	abort(403, 'el servicio no se encuentra en el status correcto para añadir residuos');
			// }

			$Cliente = Cliente::where('ID_Cli', $Solicitud->FK_SolSerCliente)->first();

            if(!$Cliente){
                $Cliente = DB::table('clientes_express')
                    ->where('id', $Solicitud->FK_Cliente_Express)
                    ->select('clientes_express.*', 'id as CliSlug', 'nombreEmpresa as CliName', 'nit as CliNit', 'direccion as CliAdress')
                    ->first();

                $Sede = DB::table('sedes_express')
                    ->where('idClienteExpress', $Cliente->id)
                    ->select('sedes_express.*', 'id as SedeSlug', 'nombreSede as SedeName', 'direccion as SedeAdress', 'localidad as FK_SedeMun')
                    ->first();
            } else {
                $Sede = Sede::where('FK_SedeCli', $Cliente->ID_Cli)->first();
            }

			$Persona = Personal::where('ID_Pers', $Solicitud->FK_SolSerPersona)
				->select('PersSlug','PersFirstName','PersLastName')
				->first();

			return view('serviciosexpress.addrespel', compact('Solicitud','Cliente','Sede','Persona'));
		}
		else{
			abort(403);
		}
	}

	/**
	 * ingresa los residuos adicionales a la base de datos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function updateRespel(Request $request, $id)
	{
		// return $request;
		$SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();
		if (!$SolicitudServicio) {
			abort(404, 'solicitud de servicio no encontrada');
		}

		if(!is_null($request->input('SGenerador'))){
			$this->createSolRes($request, $SolicitudServicio->ID_SolSer);
		}
		$SolicitudServicio->SolSerDescript = $request->input('SolSerDescript');
		$SolicitudServicio->save();


		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="residuos adicionales";
		$log->AuditRegistro=$SolicitudServicio->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

		/*se guarda la observacion inicial de la creación del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = $SolicitudServicio->SolSerStatus;
		if ($SolicitudServicio->SolSerDescript = "") {
			$Observacion->ObsMensaje = 'Residuos faltantes ya incluidos por el cliente';
		}else{
			$Observacion->ObsMensaje = $SolicitudServicio->SolSerDescript;
		}
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
		$Observacion->save();

		$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();

		if ($SolicitudServicio['cliente']->CliComercial <> null) {
			$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
		} else {
			$comercial = "";
		}

		$SolicitudServicio['comercial'] = $comercial;
		$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();

		// Solo interno: Programaciones (mismo criterio que servicio regular). El cliente no recibe este aviso (recibo RM y certificados sí van al cliente)
		Mail::to(self::MAIL_PROGRAMACIONES_INTERNO)->send(new SolSerLeftRespel($SolicitudServicio));

		return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $id]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function serviciosCompletados()
	{
		if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
			abort(401, 'no tiene autorización para acceder a esta página');
		}

		$Servicios = DB::table('solicitud_servicios')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('personals as Comercial', 'Comercial.ID_Pers', '=', 'clientes.CliComercial')
			->select('solicitud_servicios.ID_SolSer',
			'solicitud_servicios.SolSerStatus',
			'solicitud_servicios.SolSerTipo',
			'solicitud_servicios.SolSerAuditable',
			'solicitud_servicios.SolSerConductor',
			'solicitud_servicios.SolSerVehiculo',
			'solicitud_servicios.SolSerSlug',
			'solicitud_servicios.created_at',
			'solicitud_servicios.updated_at',
			'solicitud_servicios.SolSerDelete',
			'solicitud_servicios.SolResAuditoriaTipo',
			'solicitud_servicios.SolSerNameTrans',
			'solicitud_servicios.SolSerNitTrans',
			'solicitud_servicios.SolSerAdressTrans',
			'solicitud_servicios.SolSerTypeCollect',
			'solicitud_servicios.SolSerCollectAddress',
			'solicitud_servicios.SolServCertStatus',
			'clientes.CliName',
			'clientes.CliSlug',
			'clientes.CliStatus',
			'clientes.TipoFacturacion',
			'clientes.CliCategoria',
			'personals.PersFirstName',
			'personals.PersLastName',
			'personals.PersSlug',
			'personals.PersEmail',
			'personals.PersCellphone',
			'Comercial.PersFirstName as ComercialPersFirstName',
			'Comercial.PersLastName as ComercialPersLastName',
			'Comercial.PersSlug as ComercialPersSlug',
			'Comercial.PersEmail as ComercialPersEmail',
			'Comercial.PersCellphone as ComercialPersCellphone')
			->where('solicitud_servicios.SolSerStatus', 'Recepcionado')
			->where('clientes.CliCategoria', 'Cliente')
			->orderBy('created_at', 'desc')
			->get();

		$Cliente = Cliente::select('CliName','ID_Cli', 'CliStatus')->where('ID_Cli',userController::IDClienteSegunUsuario())->first();
		foreach ($Servicios as $servicio) {
			/* validacion para encontrar la fecha de recepción en planta del servicio */
			$fechaRecepcion = SolicitudServicio::find($servicio->ID_SolSer)->programacionesrecibidas()->first();
			if($fechaRecepcion){
				$servicio->recepcion = $fechaRecepcion->ProgVehSalida;
			}else{
				$servicio->recepcion = null;
			}
			$servicio->ultimoRecordatorio = SolicitudServicio::find($servicio->ID_SolSer)->ultimorecordatorio();
			$servicio->fechaRecepcionado = SolicitudServicio::find($servicio->ID_SolSer)->fecharecepcionado();
		}

		// return $Servicios;

		return view('solicitud-serv.indexrecordatorios', compact('Servicios', 'Residuos', 'Cliente'));
	}

	public function reversarStatus(Request $request)
	{
		$Solicitud = SolicitudServicio::where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}
		if ($Solicitud->SolSerStatus == 'Certificacion') {
			if (!in_array(Auth::user()->UsRol, Permisos::REVERSARADMIN) && !in_array(Auth::user()->UsRol2, Permisos::REVERSARADMIN)) {
				abort(403, 'el servicio ya ha sido certificado y no admite cambios de status');
			}
		}
		switch ($request->input('solserstatus')) {
			case 'Notificado':
			case 'Completado':
			case 'Residuo Faltante':
			case 'Corregido':
			case 'Programado':
			case 'No Conciliado':
			case 'Residuo Faltante':
				if ($Solicitud->SolSerStatus == 'Conciliado'||$Solicitud->SolSerStatus == 'Tratado'||$Solicitud->SolSerStatus == 'Certificacion') {
					// Certificado (0) y Manifiesto (1): borrar documento y certdato para que se recreen con las modificaciones.
					// Certificado de terceros (2): NO tocar, preservar documento y certdato.
					$certificadosDelete = Certificado::with('certdato')->where('FK_CertSolser', $Solicitud->ID_SolSer)->get();
					foreach ($certificadosDelete as $key => $value) {
						if ((int) $value->CertType === 2) {
							continue; // No borrar certdato de certificados de terceros
						}
						foreach ($value->certdato as $key2 => $value2) {
							$value2->delete();
						}
						$value->delete();
					}
				}
				break;
		}

		$Solicitud->SolSerStatus = $request->input('solserstatus');
		$Solicitud->SolSerDescript = $request->input('solserdescript');
		$Solicitud->save();

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="Reversado Status";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=[$Solicitud->SolSerStatus, $Solicitud->SolSerDescript];
		$log->save();


		/*se guarda la observacion de la modificacion del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = 'Devuelto a status: '.$Solicitud->SolSerStatus;
		$Observacion->ObsMensaje = $Solicitud->SolSerDescript;
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $Solicitud->ID_SolSer;
		$Observacion->save();

		return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $Solicitud->SolSerSlug]);

	}

	public function CancelarServicio(Request $request)
	{
		// return $request;
		$Solicitud = SolicitudServicio::where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}

		$statusAllowingCancel = ['Pendiente',
								'Cancelado',
								'Aceptado',
								'Aprobado',
								'Programado',
								'Notificado'];

		if (!in_array($Solicitud->SolSerStatus, $statusAllowingCancel)) {
			abort(403, 'el servicio #'.$Solicitud->ID_SolSer.' no debe ser cancelado, ya que se encuentra en status '.$Solicitud->SolSerStatus);
		}

		// eliminar las programaciones relacionadas con el servicio
		$programacionesDelete = ProgramacionVehiculo::where('FK_ProgServi', $Solicitud->ID_SolSer)
		->where('ProgVehDelete', 0)
		->get();

		foreach ($programacionesDelete as $key => $value) {
			$value->ProgVehDelete = 1;
			$value->save();
		}

		// cabiar el status del servicio
		switch ($request->input('solserstatus')) {
			case 'Aprobado':
				$Solicitud->SolSerStatus = 'Aprobado';
				break;
			case 'Cancelado':
				$Solicitud->SolSerStatus = 'Cancelado';
				break;
		}
		$Solicitud->SolSerDescript = $request->input('solserdescript');
		$Solicitud->save();

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="Servicio cancelado";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=[$Solicitud->SolSerStatus, $Solicitud->SolSerDescript];
		$log->save();


		/*se guarda la observacion de la modificacion del servicio*/
		$Observacion = new Observacion();
		// cabiar el status de la observación
		switch ($request->input('solserstatus')) {
			case 'Aprobado':
				$Observacion->ObsStatus = 'Reactivado';
				break;
			case 'Cancelado':
				$Observacion->ObsStatus = 'Cancelado';
				break;
		}
		$Observacion->ObsMensaje = $Solicitud->SolSerDescript;
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $Solicitud->ID_SolSer;
		$Observacion->save();
		return redirect()->route('serviciosexpress.index');
	}

	    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rutadeldia()
    {
        $hoy = \Illuminate\Support\Carbon::today()->toDateString();
        // Solo servicios con programación recibida HOY - evita N+1 y carga masiva
        $ServiciosDelDia = DB::table('solicitud_servicios')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('personals as Comercial', 'Comercial.ID_Pers', '=', 'clientes.CliComercial')
			->join('progvehiculos as prog_recepcion', function ($join) use ($hoy) {
				$join->on('prog_recepcion.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
					->whereNotNull('prog_recepcion.ProgVehEntrada')
					->where('prog_recepcion.ProgVehDelete', 0)
					->whereRaw('DATE(prog_recepcion.ProgVehSalida) = ?', [$hoy]);
			})
			->leftJoin('sedes', function ($join) {
				$join->on('sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
					->whereRaw('sedes.ID_Sede = (SELECT MIN(s2.ID_Sede) FROM sedes s2 WHERE s2.FK_SedeCli = clientes.ID_Cli)');
			})
			->select(
				'solicitud_servicios.ID_SolSer',
				'solicitud_servicios.SolSerStatus',
				'solicitud_servicios.SolSerTipo',
				'solicitud_servicios.SolSerAuditable',
				'solicitud_servicios.SolSerConductor',
				'solicitud_servicios.SolSerVehiculo',
				'solicitud_servicios.SolSerSlug',
				'solicitud_servicios.created_at',
				'solicitud_servicios.updated_at',
				'solicitud_servicios.SolSerDelete',
				'solicitud_servicios.SolResAuditoriaTipo',
				'solicitud_servicios.SolSerNameTrans',
				'solicitud_servicios.SolSerNitTrans',
				'solicitud_servicios.SolSerAdressTrans',
				'solicitud_servicios.SolSerTypeCollect',
				'solicitud_servicios.SolSerCollectAddress',
				'solicitud_servicios.SolServCertStatus',
				'clientes.ID_Cli',
				'clientes.CliName',
				'clientes.CliSlug',
				'clientes.CliStatus',
				'clientes.TipoFacturacion',
				'clientes.CliCategoria',
				'personals.PersFirstName',
				'personals.PersLastName',
				'personals.PersSlug',
				'personals.PersEmail',
				'personals.PersCellphone',
				'Comercial.ID_Pers as ComercialID_Pers',
				'Comercial.PersFirstName as ComercialPersFirstName',
				'Comercial.PersLastName as ComercialPersLastName',
				'Comercial.PersSlug as ComercialPersSlug',
				'Comercial.PersEmail as ComercialPersEmail',
				'Comercial.PersCellphone as ComercialPersCellphone',
				'prog_recepcion.ProgVehSalida as recepcion',
				'sedes.SedeAddress as SolSerCollectAddress',
				'sedes.FK_SedeMun',
				'sedes.SedeMapLocalidad as SedeMapLocalidad',
				'sedes.SedeMapLat as SedeMapLat',
				'sedes.SedeMapLong as SedeMapLong'
			)
			->where(function($query){
				if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES)){
					if(!in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
						$query->where('Comercial.ID_Pers', Auth::user()->FK_UserPers);
					}
				}
			})
			->where('CliCategoria', 'ClientePrepago')
			->orderBy('prog_recepcion.ProgVehSalida', 'asc')
			->get();
		return view('serviciosexpress.indexprosarc', ['Servicios' => $ServiciosDelDia]);
    }

	/**
	 * Obtiene la firma del cliente Express para incluirla en los PDFs.
	 *
	 * @return array{0: ?string, 1: ?string} [rutaFirmaCliente, firmaClienteBase64]
	 */
	private function obtenerFirmaClienteExpress(SolicitudServicio $Solicitud): array
	{
		$firmaCliente = DB::table('firmas_servicio')
			->where('FK_SolSer', $Solicitud->ID_SolSer)
			->where('FK_Gener', 0)
			->where('FK_SGener', 0)
			->whereNotNull('FirmaCliente')
			->where('FirmaCliente', '!=', '0')
			->first();

		if (!$firmaCliente) {
			$firmaCliente = DB::table('firmas_servicio')
				->where('FK_SolSer', $Solicitud->ID_SolSer)
				->whereNotNull('FirmaCliente')
				->where('FirmaCliente', '!=', '0')
				->first();
		}

		$rutaFirmaCliente = null;
		$firmaClienteBase64 = null;
		if ($firmaCliente && !empty($firmaCliente->FirmaCliente)) {
			$rutaFirmaCompleta = storage_path('app/public/FirmasClientesExpress/' . $firmaCliente->FirmaCliente . '.png');
			if (file_exists($rutaFirmaCompleta)) {
				$rutaFirmaCliente = 'storage/FirmasClientesExpress/' . $firmaCliente->FirmaCliente . '.png';
				$firmaClienteBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($rutaFirmaCompleta));
			}
		}

		return [$rutaFirmaCliente, $firmaClienteBase64];
	}

	/**
	 * Genera los PDFs de certificados/manifiestos Express.
	 * Usa la misma lógica y plantillas que certificarExpress.
	 * Invocable desde el comando certificados-express:generar.
	 *
	 * @param SolicitudServicio $Solicitud
	 * @param string|null $solserRecepcionDate Fecha de recepción (opcional)
	 * @param bool $enviarEmail Si enviar correos de notificación
	 */
	public function generarPdfsCertificadosExpress($Solicitud, $solserRecepcionDate = null, $enviarEmail = true)
	{
		[$rutaFirmaCliente, $firmaClienteBase64] = $this->obtenerFirmaClienteExpress($Solicitud);

		$certificados = CertificadoExpress::with(['certdato.solres', 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento', 'SolicitudServicio' => function ($query) {
			$query->with(['SolicitudResiduo' => function ($query) {
				$query->where('SolResKgConciliado', '>', 0);
				$query->orWhere('SolResCantiUnidadConciliada', '>', 0);
				$query->with('generespel.respels');
				$query->with('requerimiento');
			}]);
		}])
			->where('FK_CertSolser', $Solicitud->ID_SolSer)
			->get();

		$fechaRecepcionFormulario = null;
		if (!empty($solserRecepcionDate)) {
			try {
				$fechaRecepcionFormulario = Carbon::parse($solserRecepcionDate);
			} catch (\Throwable $e) {
				$fechaRecepcionFormulario = null;
			}
		}

		$logoQrPath = public_path('img/LogoQR.png');
		$publicDisk = Storage::disk('public');

		foreach ($certificados as $certificado) {
			set_time_limit(300);

			$Solicitud->rutaFirmaCliente = $rutaFirmaCliente;
			$Solicitud->firmaClienteBase64 = $firmaClienteBase64;

			$qrCode = new QrCode(route('certificadosexpress.show', ['certificadosexpress' => $certificado->CertSlug]));
			if (file_exists($logoQrPath)) {
				$qrCode->setLogoPath($logoQrPath);
			}
			$qrCode->setLogoSize(60, 60);
			$qrCode->setSize(300);
			$qrCode->setMargin(0);
			$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);

			$pdf = null;

			switch ($certificado->tratamiento->TratName) {
				case 'TermoDestrucción':
					if (empty($certificado->CertNumero) || $certificado->CertNumero == 0) {
						$ultimoCertificado = CertificadoExpress::where('CertType', 0)->orderBy('CertNumero', 'desc')->first();
						$certificado->CertNumero = ($ultimoCertificado && $ultimoCertificado->CertNumero)
							? $ultimoCertificado->CertNumero + 1
							: 1;
						$certificado->save();
					}

					$fechaEmision = Carbon::now();
					if ($fechaRecepcionFormulario instanceof Carbon) {
						$fechaRecepcion = $fechaRecepcionFormulario->copy();
					} else {
						$primeraRecepcion = $Solicitud->programacionesrecibidas()->orderBy('ProgVehEntrada', 'asc')->first();
						$fechaRecepcion = ($primeraRecepcion && $primeraRecepcion->ProgVehEntrada)
							? Carbon::parse($primeraRecepcion->ProgVehEntrada)
							: $fechaEmision;
					}

					$pdf = PDF::setPaper('letter', 'portrait')->loadView('certificadosExpress.topdf', compact(['certificado', 'Solicitud', 'qrCode', 'fechaEmision', 'fechaRecepcion']));
					$nombreArchivo = 'E-' . sprintf('%07s', $certificado->CertNumero) . '.pdf';
					$path = 'certificadoExpress/' . $nombreArchivo;
					$publicDisk->makeDirectory('certificadoExpress');
					if (!$publicDisk->put($path, $pdf->output())) {
						Log::error('No se pudo guardar el certificado express en disco', ['ID_Cert' => $certificado->ID_Cert, 'path' => $path]);
						continue 2;
					}

					$certificado->update([
						'CertSrc' => $nombreArchivo,
						'solserRecepcionDate' => $fechaRecepcion->format('Y-m-d'),
					]);
					break;

				default:
					if (empty($certificado->CertManifNumero) || $certificado->CertManifNumero == 0) {
						$ultimoManifiesto = CertificadoExpress::where('CertType', 1)->orderBy('CertManifNumero', 'desc')->first();
						$certificado->CertManifNumero = ($ultimoManifiesto && $ultimoManifiesto->CertManifNumero)
							? $ultimoManifiesto->CertManifNumero + 1
							: 1;
						$certificado->save();
					}

					$fechaEmision = Carbon::now();
					if ($fechaRecepcionFormulario instanceof Carbon) {
						$fechaRecepcion = $fechaRecepcionFormulario->copy();
					} else {
						$primeraRecepcion = $Solicitud->programacionesrecibidas()->orderBy('ProgVehEntrada', 'asc')->first();
						$fechaRecepcion = ($primeraRecepcion && $primeraRecepcion->ProgVehEntrada)
							? Carbon::parse($primeraRecepcion->ProgVehEntrada)
							: $fechaEmision;
					}

					$pdf = PDF::setPaper('letter', 'portrait')->loadView('certificadosExpress.topdfmanifesto', compact(['certificado', 'Solicitud', 'qrCode', 'fechaEmision', 'fechaRecepcion']));
					$nombreArchivo = 'ME-' . sprintf('%07s', $certificado->CertManifNumero) . '.pdf';
					$path = 'manifiestosExpress/' . $nombreArchivo;
					$publicDisk->makeDirectory('manifiestosExpress');
					if (!$publicDisk->put($path, $pdf->output())) {
						Log::error('No se pudo guardar el manifiesto express en disco', ['ID_Cert' => $certificado->ID_Cert, 'path' => $path]);
						continue 2;
					}

					$certificado->update([
						'CertSrcManif' => $nombreArchivo,
						'solserRecepcionDate' => $fechaRecepcion->format('Y-m-d'),
					]);
					break;
			}

			if (!$enviarEmail) {
				continue;
			}

			$email = DB::table('solicitud_servicios')
				->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
				->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
				->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
				->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
				->where('solicitud_servicios.SolSerSlug', '=', $Solicitud->SolSerSlug)
				->where('progvehiculos.FK_ProgServi', '=', $Solicitud->ID_SolSer)
				->where('progvehiculos.ProgVehDelete', 0)
				->first();

			$clienteEmail = null;
			if (!empty($Solicitud->FK_SolSerSede)) {
				$sede = Sede::where('ID_Sede', $Solicitud->FK_SolSerSede)->first();
				if ($sede) {
					if (!empty($sede->GSedeEmail)) {
						$clienteEmail = $sede->GSedeEmail;
					} elseif (!empty($sede->SedeEmail)) {
						$clienteEmail = $sede->SedeEmail;
					}
				}
			}
			if (!$clienteEmail && !empty($email->PersEmail)) {
				$clienteEmail = $email->PersEmail;
			}

			if ($email && $pdf && $certificado && isset($certificado->CertType)) {
				try {
					if (!empty($clienteEmail)) {
						Mail::to($clienteEmail)
							->cc(self::MAIL_EXPRESS_INTERNO)
							->send(new SolSerExpressEmail($email, $pdf, $certificado));
					} else {
						Mail::to(self::MAIL_EXPRESS_INTERNO)
							->send(new SolSerExpressEmail($email, $pdf, $certificado));
					}
				} catch (\Exception $e) {
					Log::error('Error al enviar correo de certificación Express: ' . $e->getMessage(), [
						'SolSerSlug' => $Solicitud->SolSerSlug,
						'certificado_id' => $certificado->ID_Cert ?? null,
					]);
				}
			} else {
				Log::warning('No se pudo enviar correo de certificación Express - Parámetros faltantes', [
					'SolSerSlug' => $Solicitud->SolSerSlug,
					'email' => $email ? 'OK' : 'NULL',
					'pdf' => $pdf ? 'OK' : 'NULL',
					'certificado' => $certificado ? 'OK' : 'NULL',
					'CertType' => isset($certificado->CertType) ? $certificado->CertType : 'NO DEFINIDO',
				]);
			}
		}
	}

	public function certificarExpress(Request $request)
	{
		// Solicitudes con muchos generadores generan muchos certificados/PDFs; evitar timeout
		set_time_limit(600);
		@ini_set('memory_limit', '512M');

		$request->validate([
			'solserRecepcionDate' => 'nullable|date',
		]);

		$Solicitud = SolicitudServicio::where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}

		$totalrerspel = $Solicitud->SolicitudResiduo()->get('SolResKgConciliado')->sum('SolResKgConciliado');


		if ($totalrerspel <= 0) {
			abort(403, 'debe indicar las cantidades de los residuos antes de poder continuar');
		}

		if ($Solicitud->SolSerStatus == 'Certificacion') {
			$certificadosDelete = CertificadoExpress::with('certdato')->where('FK_CertSolser', $Solicitud->ID_SolSer)->get();
			foreach ($certificadosDelete as $key => $value) {
				foreach ($value->certdato as $key2 => $value2) {
					$value2->delete();
				}
				$value->delete();
			}
			// Eliminar Manifiestos legacy (tratamientos externos que antes se guardaban en tabla Manifiesto)
			$manifiestosDelete = Manifiesto::with('manifdato')->where('FK_ManifSolser', $Solicitud->ID_SolSer)->get();
			foreach ($manifiestosDelete as $manif) {
				foreach ($manif->manifdato as $md) {
					$md->delete();
				}
				$manif->delete();
			}
		}

		/* se generan los registros para certificados y manifiestos */
		$this->solservdocstoreExpress($Solicitud->ID_SolSer);

		/**se cambia el status del servicio a certificado */
		$Solicitud->SolSerStatus = 'Certificacion';
		$Solicitud->SolServCertStatus = 2;
		$Solicitud->SolSerDescript = $request->input('solserdescript');
		$Solicitud->save();
		/** se guarda log en la tabla de auditoria */

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="certificar Express";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=[$Solicitud->SolSerStatus, $Solicitud->SolSerDescript];
		$log->save();

		/*se guarda la observacion de la modificacion del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = $Solicitud->SolSerStatus;
		$Observacion->ObsMensaje = $Solicitud->SolSerDescript;
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $Solicitud->ID_SolSer;
		$Observacion->save();

		$this->generarPdfsCertificadosExpress($Solicitud, $request->input('solserRecepcionDate'), true);

		return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $Solicitud->SolSerSlug]);

	}

    public function conciliarExpress(Request $request)
	{
		// return $request;

		$Solicitud = SolicitudServicio::where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}

		$totalrerspel = $Solicitud->SolicitudResiduo()->get('SolResKgConciliado')->sum('SolResKgConciliado');

		if ($totalrerspel <= 0) {
			abort(403, 'debe indicar las cantidades de los residuos antes de poder continuar');
		}

		/* Obtener la firma del cliente desde firmas_servicio (guardada al firmar el RM) */
		// Para servicios Express, buscar primero el registro principal (FK_Gener = 0, FK_SGener = 0)
		$firmaClienteRM = DB::table('firmas_servicio')
			->where('FK_SolSer', $Solicitud->ID_SolSer)
			->where('FK_Gener', 0)
			->where('FK_SGener', 0)
			->whereNotNull('FirmaCliente')
			->where('FirmaCliente', '!=', '0')
			->first();

		// Si no se encuentra con FK_Gener = 0, buscar cualquier registro del servicio (fallback)
		if (!$firmaClienteRM) {
			$firmaClienteRM = DB::table('firmas_servicio')
				->where('FK_SolSer', $Solicitud->ID_SolSer)
				->whereNotNull('FirmaCliente')
				->where('FirmaCliente', '!=', '0')
				->first();
		}

		/* Si existe firma del RM, usarla. Si no existe y se envía una nueva firma, guardarla */
		$solserFirma = $request->input('solserFirma');
		if (!$firmaClienteRM && !empty($solserFirma) && strpos($solserFirma, 'data:image') !== false) {
			/* se guarda la firma del cliente solo si no existe una del RM */
			$data_uri = $request->input('solserFirma');
			$encoded_image = explode(",", $data_uri)[1];
			$decoded_image = base64_decode($encoded_image);
			$nombreDeFirma = hash('md5', rand() . time());
			Storage::disk('public')->put('FirmasClientesExpress/' . $nombreDeFirma . '.png', $decoded_image);

			/* Actualizar o crear registro en firmas_servicio */
			$firmaExistente = DB::table('firmas_servicio')
				->where('FK_SolSer', $Solicitud->ID_SolSer)
				->first();

			if ($firmaExistente) {
				DB::table('firmas_servicio')
					->where('FK_SolSer', $Solicitud->ID_SolSer)
					->update(['FirmaCliente' => $nombreDeFirma, 'updated_at' => now()]);
			} else {
				DB::table('firmas_servicio')->insert([
					'FK_SolSer' => $Solicitud->ID_SolSer,
					'FK_Gener' => 0,
					'FK_SGener' => 0,
					'FirmaCliente' => $nombreDeFirma,
					'FirmaConductor' => '0',
					'FirmaPDA' => '0',
					'SlugFirmas' => \Illuminate\Support\Str::uuid()->toString(),
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
		/* Si ya existe firma del RM, no hacer nada - se usará automáticamente */

		/**se cambia el status del servicio a conciliado */
		$Solicitud->SolSerStatus = 'Conciliado';
		$Solicitud->SolServCertStatus = 1;
		$Solicitud->SolSerDescript = $request->input('solserdescript');
		$Solicitud->save();

		/** se guarda log en la tabla de auditoria */
		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="conciliado Express";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=[$Solicitud->SolSerStatus, $Solicitud->SolSerDescript];
		$log->save();

		/*se guarda la observacion de la modificacion del servicio*/
		$Observacion = new Observacion();
		$Observacion->ObsStatus = $Solicitud->SolSerStatus;
		$Observacion->ObsMensaje = $Solicitud->SolSerDescript;
		$Observacion->ObsTipo = 'prosarc';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $Solicitud->ID_SolSer;
		$Observacion->save();

		/**se envia notificacion con los archivos en formato pdf de los certificados */
		$emailData = DB::table('solicitud_servicios')
			->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
			->where('solicitud_servicios.SolSerSlug', '=', $Solicitud->SolSerSlug)
			->where('progvehiculos.FK_ProgServi', '=', $Solicitud->ID_SolSer)
			->where('progvehiculos.ProgVehDelete', 0)
			->first();

		// Solo interno Express: conciliación no se envía al cliente (recibo RM y certificados sí van al cliente)
		Mail::to(self::MAIL_EXPRESS_INTERNO)->send(new SolSerExpressConciliado($emailData));

		return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $Solicitud->SolSerSlug]);

	}

	public function solservdocstoreExpress($id)
	{

		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $id)->first();
		$serviciovalidado = $id;
		/*cuenta los diferentes generadores*/
		$generadoresdelasolicitud = GenerSede::whereHas('resgener.solres', function ($query) use ($serviciovalidado) {
			$query->where('solicitud_residuos.FK_SolResSolSer', $serviciovalidado);
		})
		->with(['resgener' => function ($query) use ($serviciovalidado){
			$query->with(['solres' => function ($query) use ($serviciovalidado){
				$query->where('FK_SolResSolSer', $serviciovalidado);
				$query->with(['requerimiento.tratamiento.gestor', 'requerimiento:ID_Req,FK_ReqTrata']);
			}]);
			$query->whereHas('solres', function ($query) use ($serviciovalidado){
				$query->where('FK_SolResSolSer', $serviciovalidado);
			});
		}])
		->get();
		// return $generadoresdelasolicitud;
		/*consulta para el cliente de esta solicitud*/
		$cliente = Cliente::whereHas('sedes.generador', function ($query) use ($generadoresdelasolicitud) {
			$query->where('generadors.ID_Gener', $generadoresdelasolicitud[0]->FK_GSede);
		})->first();
		foreach ($generadoresdelasolicitud as $genersede) {
			foreach ($genersede->resgener as $resgener) {
				foreach ($resgener->solres as $key) {
					if ($key->SolResKgConciliado > 0) {
						switch ($key->requerimiento->tratamiento->TratTipo) {
							case '0':
								// "tratamiento tipo: interno; Certificado";

                                //check CertManifNumero previous counter
                                $manifiestoprevio = CertificadoExpress::where('CertType', 1)->orderBy('ID_Cert','desc')->first();

                                //check CertNumero previous counter for certificates
                                $certificadoprevioNumero = CertificadoExpress::where('CertType', 0)->orderBy('ID_Cert','desc')->first();

								$certificadoprevio = CertificadoExpress::where('FK_CertTrat', $key->requerimiento->tratamiento->ID_Trat)
								->where('FK_CertSolser', $id)
								->where('FK_CertGenerSede', $genersede->ID_GSede)
								->first();

								$gestor = Sede::where('ID_Sede', $key->requerimiento->tratamiento->FK_TratProv)->first();

								if ((isset($certificadoprevio))&&($certificadoprevio->FK_CertTrat == $key->requerimiento->tratamiento->ID_Trat)&&($certificadoprevio->FK_CertGenerSede == $genersede->ID_GSede)) {

									$dato = new CertExpressdato;
									$dato->FK_DatoCert = $certificadoprevio->ID_Cert;
									$dato->FK_DatoCertSolRes = $key->ID_SolRes;
									$dato->save();

								}else{

									$certificado = new CertificadoExpress;
									if ($key->requerimiento->tratamiento->TratName == 'TermoDestrucción') {
										$certificado->CertType = 0;
										$certificado->CertObservacion = "certificado Express con observacion generica";
										$certificado->CertAnexo = "anexo de certificado ".$key->requerimiento->tratamiento->TratName.$key->requerimiento->tratamiento->FK_TratProv;
										$certificado->CertManifPrepend = "";
										$certificado->CertManifNumero = 0;

										// Asignar número consecutivo para certificados
										if ($certificadoprevioNumero && $certificadoprevioNumero->CertNumero) {
											$certificado->CertNumero = $certificadoprevioNumero->CertNumero + 1;
										} else {
											$certificado->CertNumero = 1;
										}
									}else{
										$certificado->CertType = 1;
										$certificado->CertObservacion = "manifiesto Express con observacion generica";
										$certificado->CertAnexo = "anexo de manifiesto ".$key->requerimiento->tratamiento->TratName.$key->requerimiento->tratamiento->FK_TratProv;
										$certificado->CertManifPrepend = "ME-";
                                        if ($manifiestoprevio) {
                                            $certificado->CertManifNumero = $manifiestoprevio->CertManifNumero + 1;
                                        }else{
                                            $certificado->CertManifNumero = 1;
                                        }
									}
									$certificado->CertiEspName = "";
									$certificado->CertiEspValue = "";
									$certificado->CertSlug = hash('sha256', rand().time());
									$certificado->CertSrc = 'CertificadoDefault.pdf';
									// $certificado->CertNumRm = "C-130";
									$certificado->CertAuthHseq = 0;
									$certificado->CertAuthDp = 1;
									$certificado->CertAuthJl = 2;
									$certificado->CertAuthJo = 3;
									$certificado->FK_CertSolser = $id;
									$certificado->FK_CertCliente = $cliente->ID_Cli;
									$certificado->FK_CertGenerSede = $genersede->ID_GSede;
									$certificado->FK_CertGestor = $key->requerimiento->tratamiento->gestor->FK_SedeCli;
									$certificado->FK_CertTrat = $key->requerimiento->tratamiento->ID_Trat;
									if ($SolicitudServicio->SolSerTipo == 'Externo') {
										$certificado->FK_CertTransp = $cliente->ID_Cli;
									}else{
										$certificado->FK_CertTransp = 1;
									}

									$certificado->SolicitudServicio->SolicitudResiduo = $certificado->SolicitudServicio->SolicitudResiduo->map(function ($item) {
										$rm = SolicitudResiduo::where('SolResSlug', $item->SolResSlug)->first('SolResRM');
										$item->SolResRM2 = $rm->SolResRM;
										return $item;
									});
									$certificado->save();

									$dato = new CertExpressdato;
									$dato->FK_DatoCert = $certificado->ID_Cert;
									$dato->FK_DatoCertSolRes = $key->ID_SolRes;
									$dato->save();

								}

								break;

							case '1':
								// "tratamiento tipo: externo ; manifiesto Express" - Crear CertificadoExpress (CertType=1) para que se genere el PDF
								$tratamiento = $key->requerimiento->tratamiento;
								if (!$tratamiento->gestor || !$tratamiento->gestor->FK_SedeCli) {
									Log::warning('solservdocstoreExpress: tratamiento externo sin gestor (FK_TratProv o Sede->FK_SedeCli)', [
										'ID_Trat' => $tratamiento->ID_Trat ?? null,
										'TratName' => $tratamiento->TratName ?? null,
										'ID_SolSer' => $id,
										'ID_SolRes' => $key->ID_SolRes
									]);
									break;
								}

								// Verificar si ya existe CertificadoExpress con ese tratamiento para esta solicitud
								$certManifiestoPrevio = CertificadoExpress::where('FK_CertTrat', $tratamiento->ID_Trat)
									->where('FK_CertSolser', $id)
									->where('FK_CertGenerSede', $genersede->ID_GSede)
									->first();

								if ($certManifiestoPrevio) {
									$dato = new CertExpressdato;
									$dato->FK_DatoCert = $certManifiestoPrevio->ID_Cert;
									$dato->FK_DatoCertSolRes = $key->ID_SolRes;
									$dato->save();
								} else {
									$manifiestoprevioNum = CertificadoExpress::where('CertType', 1)->orderBy('ID_Cert', 'desc')->first();
									$certificado = new CertificadoExpress;
									$certificado->CertType = 1;
									$certificado->CertObservacion = "manifiesto Express con observacion generica (tratamiento externo)";
									$certificado->CertAnexo = "anexo de manifiesto ".$tratamiento->TratName.$tratamiento->FK_TratProv;
									$certificado->CertManifPrepend = "ME-";
									$certificado->CertManifNumero = $manifiestoprevioNum ? ($manifiestoprevioNum->CertManifNumero + 1) : 1;
									$certificado->CertNumero = 0;
									$certificado->CertiEspName = "";
									$certificado->CertiEspValue = "";
									$certificado->CertSlug = hash('sha256', rand().time());
									$certificado->CertSrc = 'CertificadoDefault.pdf';
									$certificado->CertAuthHseq = 0;
									$certificado->CertAuthDp = 1;
									$certificado->CertAuthJl = 2;
									$certificado->CertAuthJo = 3;
									$certificado->FK_CertSolser = $id;
									$certificado->FK_CertCliente = $cliente->ID_Cli;
									$certificado->FK_CertGenerSede = $genersede->ID_GSede;
									$certificado->FK_CertGestor = $tratamiento->gestor->FK_SedeCli;
									$certificado->FK_CertTrat = $tratamiento->ID_Trat;
									$certificado->FK_CertTransp = ($SolicitudServicio->SolSerTipo == 'Externo') ? $cliente->ID_Cli : 1;
									$certificado->save();

									$dato = new CertExpressdato;
									$dato->FK_DatoCert = $certificado->ID_Cert;
									$dato->FK_DatoCertSolRes = $key->ID_SolRes;
									$dato->save();
								}

								break;

							default:
								return back()->withErrors(['msg' => ['alguno de los residuos no posee tratamiento asignado favor verifica que su asesor comercial la evaluacion de los residuos.']]);
								break;
						}
					}
				}
			}
		}
	}


	public function pdftest()
	{

		// return view('certificadosExpress.topdf', compact('certificado'));
		// return $certificado;
		$fechaEmision = Carbon::now();
		$fechaRecepcion = $fechaEmision;
		$pdf = PDF::setPaper('letter', 'portrait')->loadView('certificadosExpress.topdf', compact('certificado', 'fechaEmision', 'fechaRecepcion'));

		return $pdf->stream();
	}

    	/*
	*
	* Add all respels into express service
	*
	*/
	public function addAllRespels(SolicitudServicio $SolicitudServicio)
	{
            $Cliente = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();
			$Sede = Sede::where('FK_SedeCli', $Cliente->ID_Cli)->first();
			$generador = Generador::where('FK_GenerCli', $Sede->ID_Sede)->first();
			$sGener = GenerSede::with(['resgener.respels'])->where('FK_GSede', $generador->ID_Gener)->first();

			$Respels = DB::table('residuos_geners')
				->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('requerimientos', 'requerimientos.FK_ReqRespel', '=', 'respels.ID_Respel')
				->select('gener_sedes.*', 'residuos_geners.SlugSGenerRes', 'respels.RespelName', 'respels.RespelSlug', 'respels.ID_Respel', 'requerimientos.FK_ReqTrata', 'requerimientos.forevaluation', 'requerimientos.ofertado')
				->whereIn('respels.RespelStatus', ['Aprobado', 'Revisado', 'Falta TDE', 'TDE actualizada', 'Vencido'])
				->where('respels.RespelDelete', 0)
				->where('gener_sedes.GSedeSlug', $sGener->GSedeSlug)
				->where('residuos_geners.DeleteSGenerRes', '=', 0)
				->where('requerimientos.forevaluation', 1)
				->where('requerimientos.ofertado', 1)
				->get();

		foreach ($Respels as $Respel) {
				$SolicitudResiduo = new SolicitudResiduo();
				$SolicitudResiduo->SolResKgEnviado = 1;
				$SolicitudResiduo->SolResKgRecibido = 0;
				$SolicitudResiduo->SolResKgConciliado = 0;
				$SolicitudResiduo->SolResKgTratado = 0;
				$SolicitudResiduo->SolResDelete = 0;
				$SolicitudResiduo->SolResSlug = hash('sha256', rand().time().$Respel->RespelSlug);
				$SolicitudResiduo->FK_SolResSolSer =  $SolicitudServicio->ID_SolSer;
                $SolicitudResiduo->SolResEmbalaje = "Sacos/Bolsas";
				$SolicitudResiduo->FK_SolResRg = ResiduosGener::select('ID_SGenerRes')->where('SlugSGenerRes', $Respel->SlugSGenerRes)->first()->ID_SGenerRes;
				/*validar el residuo para saber el tratamiento*/
				$respelref = ResiduosGener::select('FK_Respel')->where('SlugSGenerRes', $Respel->SlugSGenerRes)->first()->FK_Respel;

				$requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
				->where('FK_ReqRespel', $respelref)
				->where('ofertado', 1)
				->where('forevaluation', 1)
				->first();
				$nuevorequerimiento = $requerimientoparacopiar->replicate();
                $nuevorequerimiento->ReqSlug= hash('md5', rand().time().$respelref);
                $nuevorequerimiento->forevaluation=0;
                $nuevorequerimiento->ofertado=0;
                $nuevorequerimiento->save();
                $nuevorequerimiento->pretratamientosSelected()->attach($requerimientoparacopiar['pretratamientosSelected']);

                $tarifaparacopiar = Tarifa::with(['rangos'])
                ->where('FK_TarifaReq', $requerimientoparacopiar->ID_Req)->first();
                $nuevatarifa = $tarifaparacopiar->replicate();
                $nuevatarifa->FK_TarifaReq=$nuevorequerimiento->ID_Req;
                $nuevatarifa->save();

                foreach ($tarifaparacopiar->rangos as $rango) {
                	$rangoparacopiar = Rango::find($rango->ID_Rango);
                	$nuevarango = $rangoparacopiar->replicate();
                	$nuevarango->FK_RangoTarifa = $nuevatarifa->ID_Tarifa;
                	$nuevarango->save();
                }

                $SolicitudResiduo->FK_SolResRequerimiento = $nuevorequerimiento->ID_Req;
                $SolicitudResiduo->save();
		}
	}

    public function recibotest()
	{
        $recibo = ReciboDePago::find(7);
        $pdf = '';
        $asesor = Personal::find(1);
        $cliente = Cliente::find(276);
        $sede = Sede::find(470);

        return new SolSerExpressRecibo($pdf, $recibo, $asesor, $cliente, $sede);
	}

	public function getResiduosComunes()
{
    try {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Usuario no autenticado',
                'message' => 'Debe iniciar sesión para acceder a esta función'
            ], 401);
        }

        // Verificar permisos - el usuario debe tener alguno de estos roles
        $allowedRoles = array_merge(
            Permisos::COMERCIALEXPRESS,
            Permisos::TODOPROSARC,
            ['Cliente']
        );

        if (!in_array(Auth::user()->UsRol, $allowedRoles)) {
            return response()->json([
                'error' => 'Acceso no autorizado',
                'message' => 'No tiene los permisos necesarios para acceder a esta función'
            ], 403);
        }

        // Obtener el ID del generador actual
        $generador = null;
        if (isset($_GET['generador'])) {
            $generador = GenerSede::where('GSedeSlug', $_GET['generador'])->first();
        }

        // Residuos express (tabla respels) donde RespelPublic = 1
        $expressResidues = DB::table('respels')
            ->leftJoin('requerimientos', function($join) {
                $join->on('requerimientos.FK_ReqRespel', '=', 'respels.ID_Respel')
                    ->where('requerimientos.ofertado', 1)
                    ->where('requerimientos.forevaluation', 1);
            })
            ->leftJoin('tratamientos', 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
            ->select(
                'respels.ID_Respel',
                'respels.RespelName',
                'respels.RespelSlug',
                DB::raw('COALESCE(tratamientos.TratName, "Sin Tratamiento") as TratName'),
                'requerimientos.ID_Req as RequerimientoID'
            )
            ->where('respels.RespelPublic', 1)
            ->where('respels.RespelDelete', 0)
            ->whereIn('respels.RespelStatus', ['Aprobado', 'Revisado', 'Falta TDE', 'TDE actualizada', 'Vencido'])
            ->distinct()
            ->get();

        // Si hay un generador seleccionado, crear o actualizar los registros en residuos_geners
        if ($generador) {
            foreach ($expressResidues as $residue) {
                // Verificar si ya existe el registro
                $existingRecord = ResiduosGener::where('FK_SGener', $generador->ID_GSede)
                    ->where('FK_Respel', $residue->ID_Respel)
                    ->where('DeleteSGenerRes', 0)
                    ->first();

                if (!$existingRecord) {
                    // Crear nuevo registro
                    $ResiduoSedeGener = new ResiduosGener();
                    $ResiduoSedeGener->FK_SGener = $generador->ID_GSede;
                    $ResiduoSedeGener->FK_Respel = $residue->ID_Respel;
                    $ResiduoSedeGener->DeleteSGenerRes = 0;
                    $ResiduoSedeGener->SlugSGenerRes = hash('sha256', rand().time().$ResiduoSedeGener->FK_Respel);
                    $ResiduoSedeGener->save();

                    // Asignar el SlugSGenerRes al objeto de residuo
                    $residue->SlugSGenerRes = $ResiduoSedeGener->SlugSGenerRes;

                    // Si el residuo tiene un requerimiento, copiarlo
                    if ($residue->RequerimientoID) {
                        $requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
                            ->where('ID_Req', $residue->RequerimientoID)
                            ->first();

                        if ($requerimientoparacopiar) {
                            $nuevorequerimiento = $requerimientoparacopiar->replicate();
                            $nuevorequerimiento->ReqSlug = hash('md5', rand().time().$residue->ID_Respel);
                            $nuevorequerimiento->forevaluation = 1;
                            $nuevorequerimiento->ofertado = 1;
                            $nuevorequerimiento->save();
                            $nuevorequerimiento->pretratamientosSelected()->attach($requerimientoparacopiar['pretratamientosSelected']);

                            // Copiar la tarifa si existe
                            $tarifaparacopiar = Tarifa::with(['rangos'])
                                ->where('FK_TarifaReq', $requerimientoparacopiar->ID_Req)
                                ->first();

                            if ($tarifaparacopiar) {
                                $nuevatarifa = $tarifaparacopiar->replicate();
                                $nuevatarifa->FK_TarifaReq = $nuevorequerimiento->ID_Req;
                                $nuevatarifa->save();

                                foreach ($tarifaparacopiar->rangos as $rango) {
                                    $rangoparacopiar = Rango::find($rango->ID_Rango);
                                    $nuevarango = $rangoparacopiar->replicate();
                                    $nuevarango->FK_RangoTarifa = $nuevatarifa->ID_Tarifa;
                                    $nuevarango->save();
                                }
                            }
                        }
                    }
                } else {
                    // Usar el registro existente
                    $residue->SlugSGenerRes = $existingRecord->SlugSGenerRes;
                }
            }
        } else {
            // Si no hay generador, usar un slug temporal
            foreach ($expressResidues as $residue) {
                $residue->SlugSGenerRes = DB::raw('CONCAT("ComRes-", '.$residue->ID_Respel.')');
            }
        }

        return response()->json($expressResidues);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al obtener los residuos',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
	 * ingresa el numero de factura a la base de datos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function recibomaterialExpress($id)
	{
		$users = Auth::user();
        log::info('Id recibido: ' , [$id]);
		// ===================== CARGA INICIAL DE LA SOLICITUD =====================
		/* $SolicitudServicio = DB::table('solicitud_servicios')
			->leftjoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->leftjoin('cargos', 'personals.FK_PersCargo', '=', 'ID_Carg')
            ->leftjoin('clientes_express', 'clientes_express.id', '=', 'solicitud_servicios.FK_Cliente_Express')
			->select(
				'solicitud_servicios.*',
				'personals.PersFirstName', 'personals.PersLastName',
				'personals.PersEmail', 'personals.PersCellphone',
				'cargos.CargName'
			)
			->where('solicitud_servicios.SolSerSlug', $id)
			->first(); */

        $SolicitudServicio = DB::table('solicitud_servicios')
            ->leftJoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
            ->leftJoin('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
            ->leftJoin('clientes_express', 'clientes_express.id', '=', 'solicitud_servicios.FK_Cliente_Express')
            ->select(
                'solicitud_servicios.*',
                DB::raw('COALESCE(clientes_express.encargado, personals.PersFirstName) as PersFirstName'),
                DB::raw("CASE WHEN solicitud_servicios.FK_Cliente_Express IS NOT NULL THEN '' ELSE personals.PersLastName END as PersLastName"),
                DB::raw('COALESCE(clientes_express.correoEmpresa, personals.PersEmail) as PersEmail'),
                DB::raw('COALESCE(clientes_express.numero_contacto, personals.PersCellphone) as PersCellphone'),
                DB::raw("CASE WHEN solicitud_servicios.FK_Cliente_Express IS NOT NULL THEN '' ELSE cargos.CargName END as PersLastName")
            )
            ->where('solicitud_servicios.SolSerSlug', $id)
            ->first();
        log::info('SolicitudServicio LINA: ' , [$SolicitudServicio]);

		if (!$SolicitudServicio) {
			abort(404);
		}

		// ===================== NUEVO: FIRMAS POR TIPO (NULL/97/98/99) =====================
		// Reglas:
		// - Recepción (NULL): una fila con FK_Gener = ID_Gener, FK_SGener = 0
		// - Recolección (97/98/99): filas por cada (Generador, Sede del Generador) presente en la solicitud
		$tipo = $SolicitudServicio->SolSerTypeCollect; // NULL, 97, 98, 99

		if (is_null($tipo)) {
		   $pares = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors',  'generadors.ID_Gener',   '=', 'gener_sedes.FK_GSede')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->distinct()
				->get([
					'generadors.ID_Gener  as id_gener',
				]);

			foreach ($pares as $p) {
				$keys = [
					'FK_SolSer' => $SolicitudServicio->ID_SolSer,
					'FK_Gener'  => $p->id_gener,
					'FK_SGener' => 0,
				];
				$exists = DB::table('firmas_servicio')->where($keys)->exists();
				if (!$exists) {
					DB::table('firmas_servicio')->insert($keys + [
						'SlugFirmas' => Str::uuid()->toString(),
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
			}
		} else {
			// Recolección (97/98/99) -> pares (Generador, Sede del Generador)
			$pares = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors',  'generadors.ID_Gener',   '=', 'gener_sedes.FK_GSede')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->distinct()
				->get([
					'generadors.ID_Gener  as id_gener',
					'gener_sedes.ID_GSede as id_sgener',
				]);

			foreach ($pares as $p) {
				$keys = [
					'FK_SolSer' => $SolicitudServicio->ID_SolSer,
					'FK_Gener'  => $p->id_gener,
					'FK_SGener' => $p->id_sgener,
				];
				$exists = DB::table('firmas_servicio')->where($keys)->exists();
				if (!$exists) {
					DB::table('firmas_servicio')->insert($keys + [
						'SlugFirmas' => Str::uuid()->toString(),
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
			}
		}
		// ===================== FIN BLOQUE NUEVO =====================

		if ($SolicitudServicio->SolSerTypeCollect === null) {

			$SolSerConductor = $SolicitudServicio->SolSerConductor;

			if ($SolicitudServicio->SolSerTipo == 'Interno') {
				$SolSerConductor = Personal::where('ID_Pers', $SolicitudServicio->SolSerConductor)->first();
			}
			if ($SolicitudServicio->SolSerTypeCollect == 98) {
				$Address = Sede::select(['SedeAddress', 'SedeName'])
					->where('ID_Sede', $SolicitudServicio->SolSerCollectAddress)
					->first();
				$SolSerCollectAddress = $Address->SedeName . ' - ' . $Address->SedeAddress;
			}

			$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehDelete', 0)
				->get();

			$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get());

			$Cliente = DB::table('clientes')
				->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
				->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
				->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
				->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
				->first();

			$GenerResiduos = DB::table('solicitud_residuos')
				->distinct()
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
				->join('firmas_servicio', function($join) use ($SolicitudServicio) {
					$join->on('firmas_servicio.FK_Gener', '=', 'generadors.ID_Gener')
						 ->on('firmas_servicio.FK_SolSer', '=', 'solicitud_residuos.FK_SolResSolSer');
					if (is_null($SolicitudServicio->SolSerTypeCollect)) {
						$join->where('firmas_servicio.FK_SGener', '=', 0);
					} else {
						$join->on('firmas_servicio.FK_SGener', '=', 'gener_sedes.ID_GSede');
					}
				})
				->join('municipios', 'municipios.ID_Mun', '=', 'gener_sedes.FK_GSedeMun')
				->select(
					'gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.ID_Gener',
					'generadors.FK_GenerCli', 'generadors.GenerName', 'gener_sedes.GSedeSlug',
					'gener_sedes.GSedeAddress', 'gener_sedes.GSedeEmail', 'gener_sedes.GSedeCelular',
					'firmas_servicio.SlugFirmas', 'municipios.MunName'
				)
				->where('firmas_servicio.FK_SolSer', $SolicitudServicio->ID_SolSer)
				->get();

			$Residuosoriginal = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->join('requerimientos', 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
				->join('tratamientos', 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
				->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select(
					'solicitud_residuos.*', 'residuos_geners.FK_SGener', 'respels.*',
					'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName'
				)
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->get();

			$Residuos = $Residuosoriginal->map(function ($item) {
				$requerimientos = Requerimiento::with([
					'pretratamientosSelected',
					'tarifa.rangos' => function ($query) {
						$query->orderBy('TarifaDesde');
					}
				])
					->where('ID_Req', $item->FK_SolResRequerimiento)
					->first();

				$rm = SolicitudResiduo::with('SolicitudServicio')
					->where('SolResSlug', $item->SolResSlug)
					->first(['SolResRM', 'FK_SolResSolSer']);

				$item->pretratamientosSelected = $requerimientos->pretratamientosSelected;
				$item->tarifa = $requerimientos->tarifa;

				if ($requerimientos->tarifa->TarifaSpecial === 1) {
					switch ($item->SolResTypeUnidad) {
						case 'Unidad': $tarifatipo = 'Unid'; break;
						case 'Litros': $tarifatipo = 'Lt'; break;
						default:       $tarifatipo = 'Kg'; break;
					}

					$tarifaResiduo = CTarifa::with('rangos')
						->where('FK_Cliente', $rm->SolicitudServicio->FK_SolSerCliente)
						->where('FK_Tratamiento', $requerimientos->FK_ReqTrata)
						->where('Tarifatipo', $tarifatipo)
						->first();

					$item->ctarifa = $tarifaResiduo ?? null;
				} else {
					$item->ctarifa = null;
				}

				$item->SolResRM2 = $rm->SolResRM;
				return $item;
			});

			$SolicitudServicio->Repetible = 0;

			$rms = SolicitudServicio::where('SolSerSlug', $SolicitudServicio->SolSerSlug)->first('SolSerRMs');
			$SolicitudServicio->SolSerRMs = $rms->SolSerRMs;

			foreach ($Residuos as $residuo => $value) {
				$requerimientos = Requerimiento::with(['pretratamientosSelected'])
					->where('ID_Req', $value->FK_SolResRequerimiento)
					->first();

				$residuoSinTratamiento = Requerimiento::where('FK_ReqRespel', $requerimientos->FK_ReqRespel)
					->where('ofertado', 1)
					->where('forevaluation', 1)
					->first();

				if ($residuoSinTratamiento == null) {
					$SolicitudServicio->Repetible++;
				}
			}

			$SolicitudesServicioscount = SolicitudServicio::with(['Personal', 'cliente', 'municipio', 'SolicitudResiduo'])
				->where('ID_SolSer', $SolicitudServicio->ID_SolSer)
				->orderBy('created_at', 'desc')
				->get();

			$total['estimado'] = 0;
			$total['recibido'] = 0;
			$total['conciliado'] = 0;
			$total['tratado'] = 0;
			$cantidadesXtratamiento = [];

			foreach ($SolicitudesServicioscount as $servicio) {
				foreach ($servicio->SolicitudResiduo as $residuo) {
					$collection = collect($cantidadesXtratamiento);

					if ($collection->has($residuo->requerimiento->tratamiento->TratName)) {
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['estimado']   += $residuo->SolResKgEnviado;
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['recibido']   += $residuo->SolResKgRecibido;
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['conciliado'] += $residuo->SolResKgConciliado;
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['tratado']    += $residuo->SolResKgTratado;
					} else {
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName] = [
							'estimado'   => $residuo->SolResKgEnviado,
							'recibido'   => $residuo->SolResKgRecibido,
							'conciliado' => $residuo->SolResKgConciliado,
							'tratado'    => $residuo->SolResKgTratado,
						];
					}

					$total['estimado']   += $residuo->SolResKgEnviado;
					$total['recibido']   += $residuo->SolResKgRecibido ?? 0;
					$total['conciliado'] += $residuo->SolResKgConciliado ?? 0;
					$total['tratado']    += $residuo->SolResKgTratado ?? 0;
				}
			}

			if (in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol, Permisos::SolSer1)) {
				$tratamientos = Tratamiento::join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
					->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->select('*')
					->get();
			} else {
				$tratamientos = 'NoAutorizado';
			}

			// validación de fecha de recepción (usamos el ID actual de la solicitud)
			$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)
				->programacionesrecibidas()
				->first();

			if ($fechaRecepcion) {
				$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
			} else {
				$SolicitudServicio->recepcion = null;
			}

			$PublicRespels = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->select('respels.ID_Respel', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->distinct()
				->get();

			switch ($SolicitudServicio->SolSerStatus) {
				case 'Residuo Faltante':
				case 'Notificado':
					return view('solicitud-serv.rmplanta', compact(
						'SolicitudServicio', 'Residuos', 'GenerResiduos', 'Cliente',
						'SolSerConductor', 'Programaciones', 'ProgramacionesActivas',
						'total', 'cantidadesXtratamiento', 'tratamientos', 'PublicRespels'
					));
					break;
				case 'Programado':
					return view('solicitud-serv.rmplanta', compact(
						'SolicitudServicio', 'Residuos', 'GenerResiduos', 'Cliente',
						'SolSerConductor', 'Programaciones', 'ProgramacionesActivas',
						'total', 'cantidadesXtratamiento', 'tratamientos', 'PublicRespels'
					));
					break;
				case 'Corregido':
				case 'Completado':
				default:
					break;
			}
		} else {

			// ===================== RAMA ELSE (recolecciones) =====================
			/* $SolicitudServicio = DB::table('solicitud_servicios')
				->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
				->join('cargos', 'personals.FK_PersCargo', '=', 'ID_Carg')
				->select(
					'solicitud_servicios.*',
					'personals.PersFirstName', 'personals.PersLastName',
					'personals.PersEmail', 'personals.PersCellphone',
					'cargos.CargName'
				)
				->where('solicitud_servicios.SolSerSlug', $id)
				->first(); */
                $SolicitudServicio = DB::table('solicitud_servicios')
                    ->leftJoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                    ->leftJoin('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                    ->leftJoin('clientes_express', 'clientes_express.id', '=', 'solicitud_servicios.FK_Cliente_Express')
                    ->select(
                        'solicitud_servicios.*',
                        DB::raw('COALESCE(clientes_express.encargado, personals.PersFirstName) as PersFirstName'),
                        DB::raw("CASE WHEN solicitud_servicios.FK_Cliente_Express IS NOT NULL THEN '' ELSE personals.PersLastName END as PersLastName"),
                        DB::raw('COALESCE(clientes_express.correoEmpresa, personals.PersEmail) as PersEmail'),
                        DB::raw('COALESCE(clientes_express.numero_contacto, personals.PersCellphone) as PersCellphone'),
                        DB::raw("CASE WHEN solicitud_servicios.FK_Cliente_Express IS NOT NULL THEN 'Encargado' ELSE cargos.CargName END as CargName")
                    )
                    ->where('solicitud_servicios.SolSerSlug', $id)
                    ->first();
                log::info('SolicitudServicio LINA: ' , [$SolicitudServicio]);

			if (!$SolicitudServicio) {
				abort(404);
			}

			$SolSerConductor = $SolicitudServicio->SolSerConductor;

			if ($SolicitudServicio->SolSerTipo == 'Interno') {
				$SolSerConductor = Personal::where('ID_Pers', $SolicitudServicio->SolSerConductor)->first();
			}
			if ($SolicitudServicio->SolSerTypeCollect == 98) {
				$Address = Sede::select(['SedeAddress', 'SedeName'])
					->where('ID_Sede', $SolicitudServicio->SolSerCollectAddress)
					->first();
				$SolSerCollectAddress = $Address->SedeName . ' - ' . $Address->SedeAddress;
			}

			$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehDelete', 0)
				->get();

			$ProgramacionesActivas = count(ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get());

			$Cliente = DB::table('clientes')
				->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
				->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
				->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
				->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
				->first();

            if(!$Cliente) {
                $Cliente = DB::table('clientes_express')
                    ->select('clientes_express.nit as CliNit', 'clientes_express.nombreEmpresa as CliName', 'clientes_express.direccion as SedeAddress', 'clientes_express.ciudadEmpresa as MunName')
                    ->where('clientes_express.id', $SolicitudServicio->FK_Cliente_Express)
                    ->first();
            }

			$GenerResiduos = DB::table('solicitud_residuos')
				->distinct()
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
				->join('firmas_servicio', 'firmas_servicio.FK_Gener', '=', 'generadors.ID_Gener')
				->join('municipios', 'municipios.ID_Mun', '=', 'gener_sedes.FK_GSedeMun')
				->select(
					'gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.ID_Gener',
					'generadors.GenerName', 'gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress',
					'gener_sedes.GSedeEmail', 'gener_sedes.GSedeCelular',
					'municipios.MunName', 'firmas_servicio.SlugFirmas',
					'firmas_servicio.FK_SolSer', 'solicitud_residuos.SolResKgEnviado'
				)
				->where('firmas_servicio.FK_SolSer', $SolicitudServicio->ID_SolSer)
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->groupBy('gener_sedes.ID_GSede', 'residuos_geners.FK_SGener')
				->get();

			$Residuosoriginal = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->join('requerimientos', 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
				->join('tratamientos', 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
				->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select(
					'solicitud_residuos.*', 'residuos_geners.FK_SGener', 'respels.*',
					'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName'
				)
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->get();

			$Residuos = $Residuosoriginal->map(function ($item) {
				$requerimientos = Requerimiento::with([
					'pretratamientosSelected',
					'tarifa.rangos' => function ($query) {
						$query->orderBy('TarifaDesde');
					}
				])
					->where('ID_Req', $item->FK_SolResRequerimiento)
					->first();

				$rm = SolicitudResiduo::with('SolicitudServicio')
					->where('SolResSlug', $item->SolResSlug)
					->first(['SolResRM', 'FK_SolResSolSer']);

				$item->pretratamientosSelected = $requerimientos->pretratamientosSelected;
				$item->tarifa = $requerimientos->tarifa;

				if ($requerimientos->tarifa->TarifaSpecial === 1) {
					switch ($item->SolResTypeUnidad) {
						case 'Unidad': $tarifatipo = 'Unid'; break;
						case 'Litros': $tarifatipo = 'Lt'; break;
						default:       $tarifatipo = 'Kg'; break;
					}

					$tarifaResiduo = CTarifa::with('rangos')
						->where('FK_Cliente', $rm->SolicitudServicio->FK_SolSerCliente)
						->where('FK_Tratamiento', $requerimientos->FK_ReqTrata)
						->where('Tarifatipo', $tarifatipo)
						->first();

					$item->ctarifa = $tarifaResiduo ?? null;
				} else {
					$item->ctarifa = null;
				}

				$item->SolResRM2 = $rm->SolResRM;
				return $item;
			});

			$SolicitudServicio->Repetible = 0;

			$rms = SolicitudServicio::where('SolSerSlug', $SolicitudServicio->SolSerSlug)->first('SolSerRMs');
			$SolicitudServicio->SolSerRMs = $rms->SolSerRMs;

			foreach ($Residuos as $residuo => $value) {
				$requerimientos = Requerimiento::with(['pretratamientosSelected'])
					->where('ID_Req', $value->FK_SolResRequerimiento)
					->first();

				$residuoSinTratamiento = Requerimiento::where('FK_ReqRespel', $requerimientos->FK_ReqRespel)
					->where('ofertado', 1)
					->where('forevaluation', 1)
					->first();

				if ($residuoSinTratamiento == null) {
					$SolicitudServicio->Repetible++;
				}
			}

			$SolicitudesServicioscount = SolicitudServicio::with(['Personal', 'cliente', 'municipio', 'SolicitudResiduo'])
				->where('ID_SolSer', $SolicitudServicio->ID_SolSer)
				->orderBy('created_at', 'desc')
				->get();

			$total['estimado'] = 0;
			$total['recibido'] = 0;
			$total['conciliado'] = 0;
			$total['tratado'] = 0;
			$cantidadesXtratamiento = [];

			foreach ($SolicitudesServicioscount as $servicio) {
				foreach ($servicio->SolicitudResiduo as $residuo) {
					$collection = collect($cantidadesXtratamiento);

					if ($collection->has($residuo->requerimiento->tratamiento->TratName)) {
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['estimado']   += $residuo->SolResKgEnviado;
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['recibido']   += $residuo->SolResKgRecibido;
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['conciliado'] += $residuo->SolResKgConciliado;
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['tratado']    += $residuo->SolResKgTratado;
					} else {
						$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName] = [
							'estimado'   => $residuo->SolResKgEnviado,
							'recibido'   => $residuo->SolResKgRecibido,
							'conciliado' => $residuo->SolResKgConciliado,
							'tratado'    => $residuo->SolResKgTratado,
						];
					}

					$total['estimado']   += $residuo->SolResKgEnviado;
					$total['recibido']   += $residuo->SolResKgRecibido ?? 0;
					$total['conciliado'] += $residuo->SolResKgConciliado ?? 0;
					$total['tratado']    += $residuo->SolResKgTratado ?? 0;
				}
			}

			if (in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol, Permisos::SolSer1)) {
				$tratamientos = Tratamiento::join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
					->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->select('*')
					->get();
			} else {
				$tratamientos = 'NoAutorizado';
			}

			$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)
				->programacionesrecibidas()
				->first();

			if ($fechaRecepcion) {
				$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
			} else {
				$SolicitudServicio->recepcion = null;
			}

			$PublicRespels = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->select('respels.ID_Respel', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->distinct()
				->get();

			switch ($SolicitudServicio->SolSerStatus) {
				case 'Residuo Faltante':
				case 'Notificado':
					return view('serviciosexpress.rm', compact(
						'SolicitudServicio', 'Residuos', 'GenerResiduos', 'Cliente',
						'SolSerConductor', 'Programaciones', 'ProgramacionesActivas',
						'total', 'cantidadesXtratamiento', 'tratamientos', 'PublicRespels'
					));
					break;
				case 'Programado':
					return view('serviciosexpress.rm', compact(
						'SolicitudServicio', 'Residuos', 'GenerResiduos', 'Cliente',
						'SolSerConductor', 'Programaciones', 'ProgramacionesActivas',
						'total', 'cantidadesXtratamiento', 'tratamientos', 'PublicRespels'
					));
					break;
				case 'Corregido':
				case 'Completado':
				default:
					break;
			}
		}
	}

	/**
	 * Generar PDF del recibo de material Express
	 */
	public function generarPDFExpress($id)
	{
		$SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();

		if (!$SolicitudServicio) {
			abort(404);
		}

		// Express se identifica por cliente prepago (CliCategoria), no por SolSerTipo.
		$clienteExpress = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();
		if (!$clienteExpress || $clienteExpress->CliCategoria !== 'ClientePrepago') {
			abort(404, 'Este no es un servicio Express');
		}

		// Cargar los mismos datos que en recibomaterialExpress
		$users = Auth::user();

		$SolicitudServicio = DB::table('solicitud_servicios')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('cargos', 'personals.FK_PersCargo', '=', 'ID_Carg')
			->select(
				'solicitud_servicios.*',
				'personals.PersFirstName', 'personals.PersLastName',
				'personals.PersEmail', 'personals.PersCellphone',
				'cargos.CargName'
			)
			->where('solicitud_servicios.SolSerSlug', $id)
			->first();

		$SolSerConductor = $SolicitudServicio->SolSerConductor;

		if ($SolicitudServicio->SolSerTipo == 'Interno') {
			$SolSerConductor = Personal::where('ID_Pers', $SolicitudServicio->SolSerConductor)->first();
		}

		$Cliente = DB::table('clientes')
			->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
			->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
			->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
			->first();

		$Residuosoriginal = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->join('requerimientos', 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
			->join('tratamientos', 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
			->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
			->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
			->select(
				'solicitud_residuos.*', 'residuos_geners.FK_SGener', 'respels.*',
				'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName'
			)
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->get();

		$Residuos = $Residuosoriginal->map(function ($item) {
			$rm = SolicitudResiduo::with('SolicitudServicio')
				->where('SolResSlug', $item->SolResSlug)
				->first(['SolResRM', 'FK_SolResSolSer']);

			$item->SolResRM2 = $rm->SolResRM;
			return $item;
		});

		$SolicitudesServicioscount = SolicitudServicio::with(['Personal', 'cliente', 'municipio', 'SolicitudResiduo'])
			->where('ID_SolSer', $SolicitudServicio->ID_SolSer)
			->orderBy('created_at', 'desc')
			->get();

		$totales['estimado'] = 0;
		$totales['recibido'] = 0;
		$totales['conciliado'] = 0;
		$totales['tratado'] = 0;

		foreach ($SolicitudesServicioscount as $servicio) {
			foreach ($servicio->SolicitudResiduo as $residuo) {
				$totales['estimado']   += $residuo->SolResKgEnviado;
				$totales['recibido']   += $residuo->SolResKgRecibido ?? 0;
				$totales['conciliado'] += $residuo->SolResKgConciliado ?? 0;
				$totales['tratado']    += $residuo->SolResKgTratado ?? 0;
			}
		}

		// Usar la misma base del RM regular: recepción desde programación.
		$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)
			->programacionesrecibidas()
			->first();

		$SolicitudServicio->recepcion = $fechaRecepcion ? $fechaRecepcion->ProgVehSalida : null;

		// Si existe la columna en BD, priorizar la fecha certificada de Express.
		if (Schema::hasColumn('certificadosexpress', 'solserRecepcionDate')) {
			$certificadoConRecepcion = CertificadoExpress::where('FK_CertSolser', $SolicitudServicio->ID_SolSer)
				->whereNotNull('solserRecepcionDate')
				->orderBy('ID_Cert', 'desc')
				->first(['ID_Cert', 'solserRecepcionDate']);

			if ($certificadoConRecepcion && !empty($certificadoConRecepcion->solserRecepcionDate)) {
				$SolicitudServicio->recepcion = $certificadoConRecepcion->solserRecepcionDate;
			}
		}
		// crea la carpeta si no existe
        Storage::disk('public')->makeDirectory('RecibosMaterialExpress');

		$pdf = PDF::loadView('serviciosexpress.rmtemplade', compact(
			'SolicitudServicio', 'Residuos', 'Cliente', 'SolSerConductor', 'totales'
		));

		// Guardar el PDF en la carpeta específica de Express
		Storage::disk('public')->put('RecibosMaterialExpress/' . $SolicitudServicio->SlugFirmas . '.pdf', $pdf->output());

		$pdfPath = Storage::url('RecibosMaterialExpress/' . $SolicitudServicio->SlugFirmas . '.pdf');

		return $pdf->download('ReciboMaterialExpress' . $SolicitudServicio->ID_SolSer . '.pdf');
	}

	/**
	 * Generar PDF del Recibo Material Express (rmtemplate)
	 */
	public function rmtemplate($id, $slug)
	{
		// Obtener información de firmas
		/* $firmas = DB::table('firmas_servicio')
			->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('generadors', 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
			->where('firmas_servicio.FK_SGener', $id)
			->where('solicitud_servicios.SolSerSlug', $slug)
			->select('firmas_servicio.*', 'clientes.CliName', 'generadors.*')
			->first(); */

        $firmas = DB::table('firmas_servicio')
            ->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
            ->leftJoin('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
            ->leftJoin('clientes_express', 'clientes_express.id', '=', 'solicitud_servicios.FK_Cliente_Express')
            ->join('generadors', 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
            ->where('firmas_servicio.FK_SGener', $id)
            ->where('solicitud_servicios.SolSerSlug', $slug)
            ->select(
                'firmas_servicio.*',
                'generadors.*',
                // Toma el nombre de clientes_express si existe, o clientes.CliName en su lugar
                DB::raw('COALESCE(clientes_express.nombreEmpresa, clientes.CliName) as CliName')
            )
            ->first();

		if (!$firmas) {
			abort(404);
		}

		// Obtener información del servicio
		/* $SolicitudServicio = DB::table('solicitud_servicios')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('cargos', 'personals.FK_PersCargo', '=', 'ID_Carg')
			->select('solicitud_servicios.*', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersEmail', 'personals.PersCellphone', 'cargos.CargName')
			->where('solicitud_servicios.ID_SolSer', $firmas->FK_SolSer)
			->first();
 */
        $SolicitudServicio = DB::table('solicitud_servicios')
            ->leftJoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
            ->leftJoin('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
            ->leftJoin('clientes_express', 'clientes_express.id', '=', 'solicitud_servicios.FK_Cliente_Express')
            ->select(
                'solicitud_servicios.*',
                DB::raw('COALESCE(clientes_express.encargado, personals.PersFirstName) as PersFirstName'),
                DB::raw("CASE WHEN solicitud_servicios.FK_Cliente_Express IS NOT NULL THEN '' ELSE personals.PersLastName END as PersLastName"),
                DB::raw('COALESCE(clientes_express.correoEmpresa, personals.PersEmail) as PersEmail'),
                DB::raw('COALESCE(clientes_express.numero_contacto, personals.PersCellphone) as PersCellphone'),
                DB::raw("CASE WHEN solicitud_servicios.FK_Cliente_Express IS NOT NULL THEN 'Encargado' ELSE cargos.CargName END as CargName")
            )
            ->where('solicitud_servicios.ID_SolSer', $firmas->FK_SolSer)
            ->first();

		if (!$SolicitudServicio) {
			abort(404);
		}

		// Obtener información del cliente
		$Cliente = DB::table('clientes')
			->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
			->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
			->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
			->first();

		// Obtener información del conductor
		$SolSerConductor = $SolicitudServicio->SolSerConductor;
		if ($SolicitudServicio->SolSerTipo == 'Interno') {
			$SolSerConductor = Personal::where('ID_Pers', $SolicitudServicio->SolSerConductor)->first();
		}

		// Obtener residuos
		$Residuos = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->join('requerimientos', 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
			->join('tratamientos', 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
			->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
			->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
			->select(
				'solicitud_residuos.*', 'residuos_geners.FK_SGener', 'respels.*',
				'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName'
			)
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->where('residuos_geners.FK_SGener', $id)
			->get();

		// Obtener generadores
		   $GenerResiduos = DB::table('solicitud_residuos')
			   ->distinct()
			   ->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			   ->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			   ->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			   ->join('municipios', 'municipios.ID_Mun', '=', 'gener_sedes.FK_GSedeMun')
			   ->select(
				   'solicitud_residuos.FK_SolResSolSer as FK_SolSer',
				   'gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.ID_Gener',
				   'generadors.GenerName', 'gener_sedes.GSedeSlug',
				   'gener_sedes.GSedeAddress', 'gener_sedes.GSedeEmail', 'gener_sedes.GSedeCelular',
				   'municipios.MunName'
			   )
			   ->where('residuos_geners.FK_SGener', $id)
			   ->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			   ->get();

		// Obtener programaciones (primer registro)
		$Programaciones = ProgramacionVehiculo::with('Ayudante')
			->where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
			->where('ProgVehDelete', 0)
			->first();

		// Calcular totales
		$totales = [
			'estimado' => 0,
			'recibido' => 0,
			'conciliado' => 0,
			'tratado' => 0
		];

		foreach ($Residuos as $residuo) {
			$totales['estimado'] += $residuo->SolResKgEnviado;
			$totales['recibido'] += $residuo->SolResKgRecibido ?? 0;
			$totales['conciliado'] += $residuo->SolResKgConciliado ?? 0;
			$totales['tratado'] += $residuo->SolResKgTratado ?? 0;
		}

		// Obtener corrientes de residuos
		$PublicRespels = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->select('respels.ID_Respel', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->where('residuos_geners.FK_SGener', $id)
			->distinct()
			->get();

		// Usar la misma base del RM regular: recepción desde programación.
		$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)
			->programacionesrecibidas()
			->first();

		$SolicitudServicio->recepcion = $fechaRecepcion ? $fechaRecepcion->ProgVehSalida : null;

		// Si existe la columna en BD, priorizar la fecha certificada de Express.
		if (Schema::hasColumn('certificadosexpress', 'solserRecepcionDate')) {
			$certificadoConRecepcion = CertificadoExpress::where('FK_CertSolser', $SolicitudServicio->ID_SolSer)
				->whereNotNull('solserRecepcionDate')
				->orderBy('ID_Cert', 'desc')
				->first(['ID_Cert', 'solserRecepcionDate']);

			if ($certificadoConRecepcion && !empty($certificadoConRecepcion->solserRecepcionDate)) {
				$SolicitudServicio->recepcion = $certificadoConRecepcion->solserRecepcionDate;
			}
		}

		$user = Auth::user();

		// Generar string de precintos (vacío para Express)
		$precintosString = '';

		// Generación de PDF
		$pdf = PDF::setPaper('letter', 'portrait')->loadView('serviciosexpress.rmtemplade', compact(
			'SolicitudServicio', 'Residuos', 'GenerResiduos', 'Cliente', 'SolSerConductor',
			'Programaciones', 'totales', 'PublicRespels', 'firmas', 'user', 'precintosString'
		));

		// Crear carpeta si no existe
		Storage::disk('public')->makeDirectory('RecibosMaterialExpress');

		// Guardar PDF
		$nombre = $firmas->SlugFirmas . '.pdf';
		Storage::disk('public')->put('RecibosMaterialExpress/' . $nombre, $pdf->output());

		// Enviar por correo
		$destinatarios = [self::MAIL_EXPRESS_INTERNO];

		$pdfPath = storage_path('app/public/RecibosMaterialExpress/' . $firmas->SlugFirmas . '.pdf');

		$correoPrincipal = $SolicitudServicio->PersEmail;
		$destinatarios = array_values(array_unique(array_filter($destinatarios)));
		$hostSmtp = (string) env('MAIL_HOST');
		$usuarioSmtp = (string) env('MAIL_USERNAME');
		$relaySinAuth = empty($usuarioSmtp) && str_contains($hostSmtp, 'mail.protection.outlook.com');

		if ($relaySinAuth) {
			$listaEnvio = array_values(array_unique(array_filter(array_merge([$correoPrincipal], $destinatarios))));
			foreach ($listaEnvio as $correo) {
				try {
					Mail::to($correo)->send(new SolSerRME($correoPrincipal, $pdfPath, $firmas, $Cliente, $GenerResiduos->first()));
				} catch (\Throwable $e) {
					Log::warning('Fallo envio RM Express (relay sin auth) para destinatario', [
						'servicio_id' => $SolicitudServicio->ID_SolSer,
						'correo' => $correo,
						'error' => $e->getMessage(),
					]);
				}
			}
		} else {
			try {
				Mail::to($correoPrincipal)
					->cc($destinatarios)
					->send(new SolSerRME($correoPrincipal, $pdfPath, $firmas, $Cliente, $GenerResiduos->first()));
			} catch (\Throwable $e) {
				Log::error('Fallo envio RM Express por SMTP', [
					'servicio_id' => $SolicitudServicio->ID_SolSer,
					'correo_principal' => $correoPrincipal,
					'cc' => $destinatarios,
					'error' => $e->getMessage(),
				]);
			}
		}

		return response($pdf->output(), 200, [
			'Content-Type' => 'application/pdf',
			'Content-Disposition' => 'inline; filename="' . $nombre . '"'
		]);
	}

	/**
	 * Guarda la firma del cliente para servicios Express
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function firmacliente(Request $request, $id)
	{
		$idGener = $request->input('ID_Gener');

		$solser = DB::table('solicitud_servicios')
			->select('ID_SolSer')
			->where('SolSerSlug', $id)
			->first();

		if (!$solser) {
			abort(404, 'Servicio no encontrado');
		}

		// Crear carpeta si no existe
		Storage::disk('public')->makeDirectory('FirmasClientesExpress');

		// Guardar la firma del cliente en archivo
		$data_uri = $request->input('FirmaCliente');
		$encoded_image = explode(",", $data_uri)[1];
		$decoded_image = base64_decode($encoded_image);
		$nombreDeFirma = hash('md5', rand() . time());
		Storage::disk('public')->put('FirmasClientesExpress/' . $nombreDeFirma . '.png', $decoded_image);

		// Datos a guardar
		$datosActualizacion = [
			'FirmaCliente' => $nombreDeFirma,
			'NombreFuncionario' => $request->input('NombreFuncionario'),
			'Cedula' => $request->input('CedulaFuncionario'),
			'Observaciones' => $request->input('Observacion'),
			'updated_at' => now(),
		];

		// Verificar si existe el registro y actualizar o crear
		if(in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol, Permisos::SUPERVISOR)){
			$firmacliente = DB::table('firmas_servicio')
				->where('FK_SolSer', $solser->ID_SolSer)
				->first();

			if ($firmacliente) {
				// Actualizar registro existente
				DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->update($datosActualizacion);
			} else {
				// Crear nuevo registro
				DB::table('firmas_servicio')->insert(array_merge($datosActualizacion, [
					'FK_SolSer' => $solser->ID_SolSer,
					'FK_Gener' => 0,
					'FK_SGener' => 0,
					'SlugFirmas' => Str::uuid()->toString(),
					'FirmaConductor' => '0',
					'FirmaPDA' => '0',
					'created_at' => now(),
				]));
			}
		} else {
			if ($solser) {
				$firmacliente = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('FK_SGener', $idGener)
					->first();

				if ($firmacliente) {
					// Actualizar registro existente
					DB::table('firmas_servicio')
						->where('FK_SolSer', $solser->ID_SolSer)
						->where('FK_SGener', $idGener)
						->update($datosActualizacion);
				} else {
					// Crear nuevo registro
					DB::table('firmas_servicio')->insert(array_merge($datosActualizacion, [
						'FK_SolSer' => $solser->ID_SolSer,
						'FK_Gener' => 0,
						'FK_SGener' => $idGener,
						'SlugFirmas' => Str::uuid()->toString(),
						'FirmaConductor' => '0',
						'FirmaPDA' => '0',
						'created_at' => now(),
					]));
				}
			}
		}

		// Si es una petición AJAX (desde offline-sync), devolver JSON
		// Verificar múltiples formas de detectar peticiones AJAX/JSON
		$isAjax = $request->ajax() ||
				  $request->wantsJson() ||
				  $request->expectsJson() ||
				  $request->header('Content-Type') === 'application/json' ||
				  strpos($request->header('Accept', ''), 'application/json') !== false ||
				  $request->input('_offline_sync') === true;

		if ($isAjax) {
			return response()->json([
				'success' => true,
				'message' => 'Firma del cliente guardada exitosamente',
				'firma' => $nombreDeFirma
			]);
		}

		return redirect()->to("/serviciosexpress/{$id}/recibomaterial");
	}

	/**
	 * Guarda la firma del conductor para servicios Express
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function firmaconductor(Request $request, $id)
	{
		$idGener = $request->input('ID_Gener');

		$solser = DB::table('solicitud_servicios')
			->select('ID_SolSer')
			->where('SolSerSlug', $id)
			->first();

		if(in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol, Permisos::SUPERVISOR)){
			$firmaconductor = DB::table('firmas_servicio')
				->where('FK_SolSer', $solser->ID_SolSer)
				->first();
		}else{
			if ($solser) {
				$firmaconductor = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('FK_SGener', $idGener)
					->first();
			}
		}

		// Guardar la firma del conductor
		$data_uri = $request->input('FirmaConductor');
		$encoded_image = explode(",", $data_uri)[1];
		$decoded_image = base64_decode($encoded_image);
		$nombreDeFirma = hash('md5', rand() . time());
		Storage::put('public/FirmaConductorExpress/' . $nombreDeFirma . '.png', $decoded_image, 'public');

		// Guardar la firma en la base de datos
		if(in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol, Permisos::SUPERVISOR)){
			DB::table('firmas_servicio')
			->where('FK_SolSer', $solser->ID_SolSer)
			->update([
				'FirmaConductor' => $nombreDeFirma,
			]);
		}else{
			if ($firmaconductor) {
				DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('FK_SGener', $idGener)
					->update([
						'FirmaConductor' => $nombreDeFirma,
					]);
			}
		}
		return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $id]);
	}

	/**
	 * Guarda la firma PDA para servicios Express
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function firmapda(Request $request, $id)
	{
		$solser = DB::table('solicitud_servicios')
			->select('ID_SolSer')
			->where('SolSerSlug', $id)
			->first();

		if ($solser) {
			$firmaPDA = DB::table('firmas_servicio')
				->where('FK_SolSer', $solser->ID_SolSer)
				->get();
		}

		$generadores = $firmaPDA;
		$numeroDeGeneradores = count($generadores);

		// Guardar la firma PDA
		$data_uri = $request->input('FirmaPDA');
		$encoded_image = explode(",", $data_uri)[1];
		$decoded_image = base64_decode($encoded_image);
		$nombreDeFirma = hash('md5', rand() . time());
		Storage::put('public/FirmaPDAExpress/' . $nombreDeFirma . '.png', $decoded_image, 'public');

		// Guardar la firma en la base de datos
		for($y=0; $y < $numeroDeGeneradores ; $y++){
			if ($firmaPDA) {
				DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->update([
						'FirmaPDA' => $nombreDeFirma,
					]);
			}
		}
		return redirect()->route('serviciosexpress.show', ['id' => $id]);
	}

}

