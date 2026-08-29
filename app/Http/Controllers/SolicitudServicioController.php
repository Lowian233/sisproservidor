<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\SolServStoreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Http\Controllers\userController;
use App\Http\Controllers\SolicitudResiduoController;
use App\Mail\NewSolServEmail;
use App\Mail\SolSerLeftRespel;
use App\Mail\NewSolServProsarcEmail;
use App\Mail\ServicioReversado;
use App\Mail\CertUpdated;
use App\Mail\SolSerRM;
use App\Mail\SolserAuditar;
use App\Mail\AprobarAuditoria;
use App\Mail\SustanciaControladaProgramada;
use App\Mail\AceiteUsadoProgramado;
use App\Mail\SustanciaControladaCreada;
use App\Mail\AceiteUsadoCreado;
use Illuminate\Support\Str;
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
use App\Rango;
use App\Certificado;
use App\Certdato;
use App\CertificadoExpress;
use App\CertExpressdato;
use App\Manifiesto;
use App\Manifdato;
use App\Requerimiento;
use App\Documento;
use App\Docdato;
use App\ProgramacionVehiculo;
use App\RequerimientosCliente;
use App\Observacion;
use App\Jaulas;
use App\CTarifa;
use App\Prefactura;
use App\PrefacturaTratamiento;
use App\PrefacturaResiduo;
use App\FirmasServicios;
use App\Permisos;
use Barryvdh\DomPDF\Facade\Pdf;
use app\Calificacion;
use app\Mail\CalificacionNotificacion;
use app\Mail\CalificacionSolicitud;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;


class SolicitudServicioController extends Controller
{
	/** Buzón interno único para notificaciones de servicios regulares (lista de distribución). */
	private const MAIL_REGULARES_INTERNO = 'regulares@prosarc.com.co';
	/** Buzón interno para documentos operativos (certificados/manifiestos) de regulares. */
	private const MAIL_CERTIFICACIONES_INTERNO = 'certificaciones@prosarc.com.co';
	/** Buzón interno para recibos de material de servicios regulares. */
	private const MAIL_RECIBO_MATERIAL_INTERNO = 'recibomaterial@prosarc.com.co';
	/** Buzón interno para solicitudes donde cliente/generador entrega en planta. */
	private const MAIL_EXTERNO_INTERNO = 'externo@prosarc.com.co';
	/** Buzón interno único para notificaciones de auditorías de servicio. */
	private const MAIL_AUDITORIAS_INTERNO = 'auditorias@prosarc.com.co';
	private const MAIL_RESIDUOS_INTERNO = 'residuos@prosarc.com.co';
	/** Buzón de Programaciones: aviso cuando el cliente añade residuos faltantes (SolSerLeftRespel). Alineado con VehicProgController::MAIL_PROGRAMACIONES_INTERNO. */
	private const MAIL_PROGRAMACIONES_INTERNO = 'programaciones@prosarc.com.co';

	private function puedeRectificarCliente(): bool
	{
		$user = Auth::user();

		return in_array($user->UsRol, Permisos::COMERCIALES, true)
			|| in_array($user->UsRol2, Permisos::COMERCIALES, true)
			|| in_array($user->UsRol, Permisos::COMERCIALEINGRURNO, true)
			|| in_array($user->UsRol2, Permisos::COMERCIALEINGRURNO, true);
	}

	private function esJefeComercialPrincipal(): bool
	{
		return Auth::user()->UsRol === 'JefeComercial';
	}

	private function esComercialIndividual(): bool
	{
		$user = Auth::user();
		$rolesComercialIndividual = ['Comercial', 'Comercialap', 'Ejecutivo Comercial'];

		if ($user->UsRol === 'JefeComercial') {
			return false;
		}
		if (in_array($user->UsRol, $rolesComercialIndividual, true)) {
			return true;
		}
		if (in_array($user->UsRol, Permisos::JefeComercial, true)
			|| in_array($user->UsRol, Permisos::PROGRAMADOR, true)) {
			return false;
		}

		return in_array($user->UsRol2, $rolesComercialIndividual, true);
	}

	private function idsComercialesEquipo(): array
	{
		$comercialesIds = DB::table('users')
			->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
			->where(function ($q) {
				$q->whereIn('users.UsRol', ['Comercial', 'Comercialap', 'Ejecutivo Comercial'])
					->orWhereIn('users.UsRol2', ['Comercial', 'Comercialap', 'Ejecutivo Comercial']);
			})
			->where('personals.PersDelete', 0)
			->pluck('personals.ID_Pers')
			->unique()
			->toArray();

		$comercialesConClientes = Cliente::where('CliDelete', 0)
			->whereNotNull('CliComercial')
			->distinct()
			->pluck('CliComercial')
			->toArray();

		return array_values(array_unique(array_merge($comercialesIds, $comercialesConClientes)));
	}

	private function aplicarFiltroComercialEnQuery($query, string $columnaComercial = 'Comercial.ID_Pers'): void
	{
		if ($this->esJefeComercialPrincipal()) {
			$ids = $this->idsComercialesEquipo();
			if (!empty($ids)) {
				$query->whereIn($columnaComercial, $ids);
			}
			return;
		}
		if ($this->esComercialIndividual()) {
			$query->where($columnaComercial, Auth::user()->FK_UserPers);
		}
	}

	private function aplicarFiltroComercialEnClientes($query, string $columna = 'clientes.CliComercial'): void
	{
		if ($this->esJefeComercialPrincipal()) {
			$ids = $this->idsComercialesEquipo();
			if (!empty($ids)) {
				$query->whereIn($columna, $ids);
			}
			return;
		}
		if ($this->esComercialIndividual()) {
			$query->where($columna, Auth::user()->FK_UserPers);
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
public function index(Request $request)
	{if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC)){
			return view('solicitud-serv.año');
		}else{
		$Servicios = DB::table('solicitud_servicios')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('personals as Comercial', 'Comercial.ID_Pers', '=', 'clientes.CliComercial')
			->join('solicitud_residuos', 'solicitud_residuos.FK_SolResSolSer', '=', 'solicitud_servicios.ID_SolSer')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
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
			'solicitud_servicios.SolNumeroFactura',
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
			'gener_sedes.GSedeName')
			->where(function($query){
				if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
					$query->where('ID_Cli',userController::IDClienteSegunUsuario());
				}
				if(in_array(Auth::user()->UsRol, Permisos::SOLSERACEPTADO) || in_array(Auth::user()->UsRol2, Permisos::SOLSERACEPTADO)){
					if(!in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
						$query->where('solicitud_servicios.SolSerStatus', 'Pendiente');
						$query->orWhere('solicitud_servicios.SolServCertStatus', 1);
					}
				}
				$this->aplicarFiltroComercialEnQuery($query);
			})
			->where('CliCategoria', 'Cliente')
			//->whereBetween('solicitud_servicios.created_at',['2022-01-01 00:00:00','2022-12-31 23:59:00'])
			->orderBy('created_at', 'desc')
            ->groupBy('solicitud_servicios.ID_SolSer')
			//->distinct()
			->get();
		$Cliente = Cliente::select('CliName','ID_Cli', 'CliStatus')->where('ID_Cli',userController::IDClienteSegunUsuario())->first();
		foreach ($Servicios as $servicio) {
			if($servicio->SolSerTypeCollect == 98){
				$Address = Sede::select('SedeAddress')->where('ID_Sede',$servicio->SolSerCollectAddress)->first();
				$servicio->SolSerCollectAddress = $Address ? $Address->SedeAddress : 'Dirección no disponible';
			}

			/* validacion para encontrar la fecha de recepción en planta del servicio */
			$fechaRecepcion = SolicitudServicio::find($servicio->ID_SolSer)->programacionesrecibidas()->first();
			if($fechaRecepcion){
				$servicio->recepcion = $fechaRecepcion->ProgVehSalida;
			}else{
				$servicio->recepcion = null;
			}
		}
		if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
			return view('solicitud-serv.index', compact('Servicios', 'Cliente'));
		}else{
			return view('solicitud-serv.indexprosarc', compact('Servicios', 'Cliente'));
		}
	}

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
		$query = DB::table('solicitud_servicios')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->leftjoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->leftjoin('personals as Comercial', 'Comercial.ID_Pers', '=', 'clientes.CliComercial')
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
				'Comercial.PersCellphone as ComercialPersCellphone'
			)
			->where(function($q){
				if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
					$q->where('clientes.ID_Cli', userController::IDClienteSegunUsuario());
				}
				if(in_array(Auth::user()->UsRol, Permisos::SOLSERACEPTADO) || in_array(Auth::user()->UsRol2, Permisos::SOLSERACEPTADO)){
					if(!in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
						$q->where('solicitud_servicios.SolSerStatus', 'Pendiente');
						$q->orWhere('solicitud_servicios.SolServCertStatus', 1);
					}
				}
				$this->aplicarFiltroComercialEnQuery($q);
			})
			->where('CliCategoria', 'Cliente')
			->whereYear('solicitud_servicios.created_at', $anio)
			->orderBy('solicitud_servicios.created_at', 'desc');

		// Filtro por mes (solo mes 1-12, el año ya está fijado)
		if ($request->filled('mes')) {
			$mes = (int) $request->mes;
			if ($mes >= 1 && $mes <= 12) {
				$query->whereMonth('solicitud_servicios.created_at', $mes);
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
			if ($servicio->SolSerTypeCollect == 98) {
				$Address = Sede::select('SedeAddress')->where('ID_Sede', $servicio->SolSerCollectAddress)->first();
				$servicio->SolSerCollectAddress = $Address ? $Address->SedeAddress : 'Dirección no disponible';
			}
			$fechaRecepcion = SolicitudServicio::find($servicio->ID_SolSer)->programacionesrecibidas()->first();
			if ($fechaRecepcion) {
				$servicio->recepcion = $fechaRecepcion->ProgVehSalida;
			} else {
				$servicio->recepcion = null;
			}
		}

		// Clientes con solicitudes en este año
		$qClientes = DB::table('solicitud_servicios')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->where('clientes.CliCategoria', 'Cliente')
			->whereYear('solicitud_servicios.created_at', $anio)
			->select('clientes.ID_Cli', 'clientes.CliName')
			->distinct()
			->orderBy('clientes.CliName');

		$this->aplicarFiltroComercialEnClientes($qClientes);
		$clientesFiltro = $qClientes->get();

		// Meses del año seleccionado (Enero a Diciembre)
		$nombresMes = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
		$mesesFiltro = collect();
		for ($m = 1; $m <= 12; $m++) {
			$mesesFiltro->push((object)[
				'valor' => $m,
				'label' => $nombresMes[$m - 1],
			]);
		}

		return view('solicitud-serv.anioFiltrado', compact('Servicios', 'Cliente', 'clientesFiltro', 'mesesFiltro', 'anio'));
	}

	/**
	 * Display a listing of the resource.
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



	public function createit(Request $request)
    {
        if ($this->puedeRectificarCliente()) {
            $ID_Cli = Cliente::where('CliDelete', 0)->get();
            $Departamentos = Departamento::all();

            // Cliente preseleccionado si viene en la URL
            $clientePreseleccionado = $request->input('ID_Cli');

            return view('solicitud-serv.createit', compact('ID_Cli', 'clientePreseleccionado'));
        } else {
            abort(403);
        }
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	 /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // 🟢 Flujo para usuarios internos de planta → cliente Prosarc (ID 1)
        // Pueden acceder directamente sin pasar por createit
        if (in_array(Auth::user()->UsRol, Permisos::PUEDE_SOLICITAR_PLANTA) && !$this->puedeRectificarCliente()) {

            $Departamentos = Departamento::all();
            $Cliente = Cliente::select('ID_Cli', 'CliName', 'CliStatus', 'TipoFacturacion')
                ->where('ID_Cli', 1) // ← Fijo Prosarc
                ->first();

            $Sedes = Sede::select('SedeSlug', 'SedeName')
                ->where('FK_SedeCli', $Cliente->ID_Cli)
                ->where('SedeDelete', 0)
                ->get();

            $SGeneradors = DB::table('gener_sedes')
                ->join('generadors', 'gener_sedes.FK_GSede', '=', 'generadors.ID_Gener')
                ->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->select('gener_sedes.GSedeSlug', 'gener_sedes.GSedeName', 'generadors.GenerName')
                ->where('clientes.ID_Cli', 1) // ← Solo Prosarc
                ->where('generadors.GenerDelete', 0)
                ->where('gener_sedes.GSedeDelete', 0)
                ->get();

            $Personals = DB::table('personals')
                ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
                ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->select('personals.PersSlug', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersEmail')
                ->where('clientes.ID_Cli', 1)
                ->where('personals.PersDelete', 0)
                ->get();

            $Requerimientos = RequerimientosCliente::where('FK_RequeClient', $Cliente->ID_Cli)->get();
            $Clientes = collect(); // Usuarios de planta no seleccionan cliente (siempre Prosarc)
            $clienteSeleccionado = null; // No aplica para usuarios de planta

            return view('solicitud-serv.create', compact('Personals', 'Cliente', 'SGeneradors', 'Departamentos', 'Sedes', 'Requerimientos', 'Clientes', 'clienteSeleccionado'));
        }

        // 🔵 Flujo para clientes normales
        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
            $Departamentos = Departamento::all();
            $Cliente = Cliente::select('CliName', 'ID_Cli', 'CliStatus', 'TipoFacturacion')
                ->where('ID_Cli', userController::IDClienteSegunUsuario())
                ->first();

            $Sedes = Sede::select('SedeSlug', 'SedeName')
                ->where('FK_SedeCli', $Cliente->ID_Cli)
                ->where('SedeDelete', 0)
                ->get();

            $SGeneradors = DB::table('gener_sedes')
                ->join('generadors', 'gener_sedes.FK_GSede', '=', 'generadors.ID_Gener')
                ->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->select('gener_sedes.GSedeSlug', 'gener_sedes.GSedeName', 'generadors.GenerName')
                ->where('clientes.ID_Cli', userController::IDClienteSegunUsuario())
                ->where('generadors.GenerDelete', 0)
                ->where('gener_sedes.GSedeDelete', 0)
                ->get();

            $Personals = DB::table('personals')
                ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
                ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->select('personals.PersSlug', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersEmail')
                ->where('clientes.ID_Cli', userController::IDClienteSegunUsuario())
                ->where('personals.PersDelete', 0)
                ->get();

            $Requerimientos = RequerimientosCliente::where('FK_RequeClient', $Cliente->ID_Cli)->get();
            $Clientes = collect(); // Clientes normales no seleccionan (son ellos mismos)
            $clienteSeleccionado = null; // No aplica para clientes normales

            return view('solicitud-serv.create', compact('Personals', 'Cliente', 'SGeneradors', 'Departamentos', 'Sedes', 'Requerimientos', 'Clientes', 'clienteSeleccionado'));
        }

        // 🔶 Flujo para otros roles internos (comerciales, programador, etc.)
        $clienteId = intval($request->input('ID_Cli'));
        $Cliente = Cliente::select('*')->where('ID_Cli', $clienteId)->first();
        if (!$Cliente) {
            abort(403, 'Cliente no encontrado.');
        }

        $Sedes = Sede::select('SedeSlug', 'SedeName')
            ->where('FK_SedeCli', $Cliente->ID_Cli)
            ->where('SedeDelete', 0)
            ->get();

        $Departamentos = Departamento::all();
        $ID_Cli = Cliente::where('CliDelete', 0)->get();

        $SGeneradors = DB::table('gener_sedes')
            ->join('generadors', 'gener_sedes.FK_GSede', '=', 'generadors.ID_Gener')
            ->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
            ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
            ->select('gener_sedes.GSedeSlug', 'gener_sedes.GSedeName', 'generadors.GenerName')
            ->where('clientes.ID_Cli', $Cliente->ID_Cli)
            ->where('generadors.GenerDelete', 0)
            ->where('gener_sedes.GSedeDelete', 0)
            ->get();

        $Personals = DB::table('personals')
            ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
            ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
            ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
            ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
            ->select('personals.PersSlug', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersEmail')
            ->where('clientes.ID_Cli', $Cliente->ID_Cli)
            ->where('personals.PersDelete', 0)
            ->get();

        $Clientes = DB::table('clientes')
            ->select('ID_Cli', 'CliName', 'TipoFacturacion')
            ->where('CliDelete', 0)
            ->get();

        $Requerimientos = RequerimientosCliente::where('FK_RequeClient', $Cliente->ID_Cli)->get();

        // Pasar el ID del cliente seleccionado para preseleccionarlo en el formulario
        $clienteSeleccionado = $clienteId;

        return view('solicitud-serv.create', compact('Personals', 'Cliente', 'SGeneradors', 'Departamentos', 'Sedes', 'Requerimientos', 'Clientes', 'clienteSeleccionado'));
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(SolServStoreRequest $request)
	{
		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="request store PRE-saved";
		$log->AuditRegistro="";
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

        if($request->input('SolResAuditoriaTipo') != 97){
            $status = 'Pendiente';
        } else {
            $status = 'Aprobado';
        }

		// return $request;
		$SolicitudServicio = new SolicitudServicio();
		$SolicitudServicio->SolSerStatus = $status;
		$SolicitudServicio->SolServMailCopia = json_encode($request->input('SolServMailCopia'));
		switch ($request->input('SolResAuditoriaTipo')) {
			case 99:
				$SolicitudServicio->SolSerAuditable = 2;
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
		$direccioncollect = 'No aplica';

		switch ($request->input('SolSerTipo')) {
			case '96':
				$transportadorname = $request->input('SolSerNameTrans');
				$transportadornit = $request->input('SolSerNitTrans');
				$transportadoradress = $request->input('SolSerAdressTrans');
				$transportadorcity = $request->input('SolSerCityTrans');
				$tipo = "Externo";
				$conductor = $request->input('SolSerConductor');
				$vehiculo = $request->input('SolSerVehiculo');
				$FechaLlegada = $request->input('SolSerFecha');
				break;

			case '97':
				$generador = DB::table('generadors')
					->join('gener_sedes', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
					->join('municipios', 'gener_sedes.FK_GSedeMun', '=', 'municipios.ID_Mun')
					->select('generadors.ID_Gener', 'generadors.GenerNit', 'generadors.GenerName', 'gener_sedes.GSedeAddress', 'municipios.ID_Mun')
					->where('GSedeSlug', $request->input('SolSerTransportador'))
					->first();
				$transportadorname = $generador->GenerName;
				$transportadornit = $generador->GenerNit;
				$transportadoradress = $generador->GSedeAddress;
				$transportadorcity = $generador->ID_Mun;
				$tipo = "Generador";
				$conductor = $request->input('SolSerConductor');
				$vehiculo = $request->input('SolSerVehiculo');
				break;

			case '98':
				$cliente = DB::table('clientes')
					->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
					->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
					->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'sedes.SedeSlug', 'municipios.ID_Mun')
					->where('SedeSlug', $request->input('SolSerTransportador'))
					->first();
				$transportadorname = $cliente->CliName;
				$transportadornit = $cliente->CliNit;
				$transportadoradress = $cliente->SedeAddress;
				$transportadorcity = $cliente->ID_Mun;
				$tipo = "Cliente";
				$conductor = $request->input('SolSerConductor');
				$vehiculo = $request->input('SolSerVehiculo');
				$FechaLlegada = $request->input('SolSerFecha');
				break;

			case '99':
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
				switch ($request->input('SolSerTypeCollect')) {
					case 99:
						$direccioncollect = "Recolección en la sede de cada generador";
						break;
					case 98:
						$sede = Sede::select(['ID_Sede','FK_SedeMun'])->where('SedeSlug', $request->input('SedeCollect'))->first();
						$direccioncollect = $sede->ID_Sede;
						$SolicitudServicio->FK_SolSerCollectMun = $sede->FK_SedeMun;
						break;
					case 97:
						$direccioncollect = $request->input('AddressCollect');
						$SolicitudServicio->FK_SolSerCollectMun = $request->input('FK_SolSerCollectMun');
						break;
					case null:
						$FechaLlegada = $request->input('SolSerFecha');
						break;
				}
				break;

			default:
				# code...
				break;
		}

		if(isset($request['SupportPay'])){
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
		$SolicitudServicio->SolSerFecha = $request->input('SolSerFecha');
		$SolicitudServicio->SolSerDescript = $request->input('SolSerDescript');
		$SolicitudServicio->SolSerTypeCollect = $request->input('SolSerTypeCollect');
		$SolicitudServicio->SolSerCollectAddress = $direccioncollect;
		if($request->input('SolSerBascula')){
			$SolicitudServicio->SolSerBascula = 1;
		}
		if($request->input('SolSerCapacitacion')){
			$SolicitudServicio->SolSerCapacitacion = 1;
		}
		if($request->input('SolSerMasPerson')){
			$SolicitudServicio->SolSerMasPerson = 1;
		}
		if($request->input('SolSerVehicExclusive')){
			$SolicitudServicio->SolSerVehicExclusive = 1;
		}
		if($request->input('SolSerPlatform')){
			$SolicitudServicio->SolSerPlatform = 1;
		}
		if($request->input('SolSerDevolucion')){
			$SolicitudServicio->SolSerDevolucion = 1;
			$SolicitudServicio->SolSerDevolucionTipo = $request->input('SolSerDevolucionTipo');
		}
		$SolicitudServicio->SolSerSlug = hash('sha256', rand().time().$SolicitudServicio->SolSerNameTrans);
		$SolicitudServicio->SolSerDelete = 0;
	$personal = Personal::select('ID_Pers')->where('PersSlug', $request->input('FK_SolSerPersona'))->first();
	$SolicitudServicio->FK_SolSerPersona = $personal ? $personal->ID_Pers : null;

	// Usuarios que crean solicitudes para clientes externos (comerciales)
	if ($this->puedeRectificarCliente()) {
		$SolicitudServicio->FK_SolSerCliente = $request->input('FK_SolSerCliente');
	}
	// Usuarios de planta crean solicitudes para Prosarc (ID_Cli = 1) - disposición interna
	else if (in_array(Auth::user()->UsRol, Permisos::PUEDE_SOLICITAR_PLANTA)){
		$SolicitudServicio->FK_SolSerCliente = 1; // Prosarc
	}
	// Otros usuarios (clientes normales)
	else{
		$clienteId = userController::IDClienteSegunUsuario();
		$SolicitudServicio->FK_SolSerCliente = $clienteId ? $clienteId : 1; // Fallback a Prosarc si no tiene cliente
	}

	// Garantizar que FK_SolSerCliente nunca sea null
	if (empty($SolicitudServicio->FK_SolSerCliente)) {
		$SolicitudServicio->FK_SolSerCliente = 1;
	}

	$SolicitudServicio->save();
	$this->createSolRes($request, $SolicitudServicio->ID_SolSer);		$log = new audit();
        $log->AuditTabla="Solicitud_servicios";
        $log->AuditType="Nuevo servicio";
        $log->AuditRegistro=$SolicitudServicio->ID_SolSer;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($request->all());
        $log->save();


		/*se guarda la observacion inicial de la creación del servicio*/
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

		// se verifica si el cliente tiene comercial asignado
		$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();
		if ($SolicitudServicio['cliente']->CliComercial <> null) {
			$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
		} else {
			$comercial = "";
		}
		$destinatarios = [self::MAIL_REGULARES_INTERNO];
		$destinatarios1 = [self::MAIL_AUDITORIAS_INTERNO];
		$destinatariorecepciom = [self::MAIL_REGULARES_INTERNO];
		if (in_array($SolicitudServicio->SolSerTipo, ['Generador', 'Cliente'], true)) {
			$destinatarios[] = self::MAIL_EXTERNO_INTERNO;
			$destinatariorecepciom[] = self::MAIL_EXTERNO_INTERNO;
		}
		$destinatarios = array_values(array_unique(array_filter($destinatarios)));
		$destinatariorecepciom = array_values(array_unique(array_filter($destinatariorecepciom)));

		$SolicitudServicio['comercial'] = $comercial;
		$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();
		// se envia un correo por cada residuo registrado
		Mail::to($destinatarios)->send(new NewSolServEmail($SolicitudServicio));

		if ($SolicitudServicio->SolSerAuditable == 2 || $SolicitudServicio->SolSerAuditable == 1) {
			Mail::to($destinatarios1)->send(new SolserAuditar($SolicitudServicio));
		}
		if ($SolicitudServicio->SolSerTipo == 'Cliente' || $SolicitudServicio->SolSerTipo == 'Externo') {
			Mail::to($destinatariorecepciom)->send(new NewSolServEmail($SolicitudServicio));
		}
		// Verificar si hay sustancias controladas o aceites usados en la solicitud
		$SolicitudServicio = SolicitudServicio::with(['SolicitudResiduo.requerimiento.respel'])
			->where('ID_SolSer', $SolicitudServicio->ID_SolSer)->first();

		$cantidadDeResiduosControlados = 0;
		$cantidadDeAceitesUsados = 0;

		foreach ($SolicitudServicio->SolicitudResiduo as $residuo) {
			$respel = $residuo->requerimiento->respel;
			if ($respel->SustanciaControlada == 1) {
				$cantidadDeResiduosControlados++;
			}
			if ($respel->AceiteUsado == 1) {
				$cantidadDeAceitesUsados++;
			}
		}

		// Enviar notificaciones específicas si hay sustancias controladas o aceites usados
		if ($cantidadDeResiduosControlados > 0) {
			// Preparar datos para el email (similar a como se hace en VehicProgController)
			$email = DB::table('solicitud_servicios')
				->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
				->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
				->select('personals.*', 'solicitud_servicios.*', 'clientes.CliName', 'clientes.CliComercial')
				->where('solicitud_servicios.SolSerSlug', '=', $SolicitudServicio->SolSerSlug)
				->first();

			// Enviar notificación de sustancia controlada creada
			Mail::to('sustancias@prosarc.com.co')->send(new SustanciaControladaCreada($email, $SolicitudServicio));
		}

		if ($cantidadDeAceitesUsados > 0) {
			// Preparar datos para el email si no se hizo antes
			if (!isset($email)) {
				$email = DB::table('solicitud_servicios')
					->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
					->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
					->select('personals.*', 'solicitud_servicios.*', 'clientes.CliName', 'clientes.CliComercial')
					->where('solicitud_servicios.SolSerSlug', '=', $SolicitudServicio->SolSerSlug)
					->first();
			}

			// Enviar notificación de aceite usado creado
			Mail::to('sustancias@prosarc.com.co')->send(new AceiteUsadoCreado($email, $SolicitudServicio));
		}
		//return redirect()->route('solicitud-servicio.show', ['id' => $SolicitudServicio->SolSerSlug]);
		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $SolicitudServicio->SolSerSlug]);


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
				if(in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL)){
				$SolicitudResiduo->SolResKgEnviado = 0;
				$SolicitudResiduo->SolResKgRecibido = $request['SolResKgEnviado'][$Generador][$y];
				}else {
				$SolicitudResiduo->SolResKgEnviado = $request['SolResKgEnviado'][$Generador][$y];
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
		 		$SolicitudResiduo->SolResAlto = $request['SolResAlto'][$Generador][$y];
				$SolicitudResiduo->SolResAncho = $request['SolResAncho'][$Generador][$y];
				$SolicitudResiduo->SolResProfundo = $request['SolResProfundo'][$Generador][$y];

				// Asignar la cantidad de embalaje si viene en el request (evita que quede null en creación)
				if (isset($request['SolResCantEmbalaje'][$Generador][$y]) && $request['SolResCantEmbalaje'][$Generador][$y] !== null) {
					$SolicitudResiduo->SolResCantEmbalaje = $request['SolResCantEmbalaje'][$Generador][$y];
				} else {
					$SolicitudResiduo->SolResCantEmbalaje = 0;
				}
				$SolicitudResiduo->SolResFotoDescargue_Pesaje = $request['SolResFotoDescargue_Pesaje'][$Generador][$y];
				$SolicitudResiduo->SolResFotoTratamiento = $request['SolResFotoTratamiento'][$Generador][$y];
				$SolicitudResiduo->SolResVideoDescargue_Pesaje = $request['SolResVideoDescargue_Pesaje'][$Generador][$y];
				$SolicitudResiduo->SolResVideoTratamiento = $request['SolResVideoTratamiento'][$Generador][$y];
				$SolicitudResiduo->SolResAuditoria = $request['SolResAuditoria'][$Generador][$y];
				// $SolicitudResiduo->SolResAuditoriaTipo = $request['SolResAuditoriaTipo'][$Generador][$y];
				$SolicitudResiduo->SolResDevolucion = $request['SolResDevolucion'][$Generador][$y];
				if ($SolicitudResiduo->SolResDevolucion == 0 || $SolicitudResiduo->SolResDevolucion == null) {
					$SolicitudResiduo->SolResDevolCantidad = 0;
				}else{
					$SolicitudResiduo->SolResDevolCantidad = $request['SolResDevolCantidad'][$Generador][$y];
				}
				$residuoGener = ResiduosGener::select('ID_SGenerRes', 'FK_Respel')->where('SlugSGenerRes',$request['FK_SolResRg'][$Generador][$y])->first();
				if (!$residuoGener) {
					Log::error('Residuo generador no encontrado en createSolRes', [
						'slug' => $request['FK_SolResRg'][$Generador][$y],
						'generador' => $Generador,
						'y' => $y
					]);
					continue; // Saltar este residuo si no se encuentra
				}

				$SolicitudResiduo->FK_SolResRg = $residuoGener->ID_SGenerRes;
				/*validar el residuo para saber el tratamiento*/
				$respelref = $residuoGener->FK_Respel;

				/*asignar el requerimiento segun el tratamiento ofertado actualmente*/
				$requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
				->where('FK_ReqRespel', $respelref)
				->where('ofertado', 1)
				->where('forevaluation', 1)
				->first();

				if (!$requerimientoparacopiar) {
					// Para Prosarc, puede que algunos residuos no tengan requerimiento ofertado
					// Intentar buscar sin el filtro de ofertado/forevaluation como fallback
					$requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
						->where('FK_ReqRespel', $respelref)
						->orderBy('ofertado', 'desc')
						->orderBy('forevaluation', 'desc')
						->first();

					if (!$requerimientoparacopiar) {
						Log::error('Requerimiento no encontrado en createSolRes (incluso sin filtros)', [
							'respelref' => $respelref,
							'SlugSGenerRes' => $request['FK_SolResRg'][$Generador][$y],
							'generador' => $Generador,
							'y' => $y,
							'ID_SolSer' => $ID_SolSer
						]);
						continue; // Saltar este residuo si no se encuentra el requerimiento
					} else {
						Log::warning('Requerimiento encontrado sin filtro ofertado/forevaluation en createSolRes', [
							'respelref' => $respelref,
							'requerimiento_id' => $requerimientoparacopiar->ID_Req,
							'ofertado' => $requerimientoparacopiar->ofertado,
							'forevaluation' => $requerimientoparacopiar->forevaluation
						]);
					}
				}

				$nuevorequerimiento = $requerimientoparacopiar->replicate();
				$nuevorequerimiento->ReqSlug= hash('md5', rand().time().$respelref);
				$nuevorequerimiento->forevaluation=0;
				$nuevorequerimiento->ofertado=0;
				$nuevorequerimiento->save();
				$nuevorequerimiento->pretratamientosSelected()->attach($requerimientoparacopiar['pretratamientosSelected']);

				$tarifaparacopiar = Tarifa::with(['rangos'])
				->where('FK_TarifaReq', $requerimientoparacopiar->ID_Req)->first();

				if (!$tarifaparacopiar) {
					Log::error('Tarifa no encontrada en createSolRes', [
						'requerimiento_id' => $requerimientoparacopiar->ID_Req,
						'generador' => $Generador,
						'y' => $y
					]);
					continue; // Saltar este residuo si no se encuentra la tarifa
				}

				$nuevatarifa = $tarifaparacopiar->replicate();
				$nuevatarifa->FK_TarifaReq=$nuevorequerimiento->ID_Req;
				$nuevatarifa->save();

				foreach ($tarifaparacopiar->rangos as $rango) {
					$rangoparacopiar = Rango::find($rango->ID_Rango);
					if ($rangoparacopiar) {
						$nuevarango = $rangoparacopiar->replicate();
						$nuevarango->FK_RangoTarifa = $nuevatarifa->ID_Tarifa;
						$nuevarango->save();
					}
				}

					// Obtener la instancia completa del modelo Respel
				$sustancia = Respel::where('ID_Respel', $respelref)->first();

						if (!$sustancia) {
							return response()->json(['error' => 'Sustancia not found'], 404);
						}

						$originalAttributes = $sustancia->getOriginal();

						/* Verificar si se cargó un documento en este campo */
						if ($request->hasFile('SustanciaControlada')) {
							$files = $request->file('SustanciaControlada');

							// Verificar si $files es un array o un solo archivo
							if (!is_array($files)) {
								$files = [$files]; // Asegurarse de que siempre es un array, incluso si es un solo archivo
							}

							// Borrar el documento actual si existe
							if ($sustancia->SustanciaControladaDocumento !== null && file_exists(public_path().'/img/SustanciaControlDoc/'.$sustancia->SustanciaControladaDocumento)) {
								unlink(public_path().'/img/SustanciaControlDoc/'.$sustancia->SustanciaControladaDocumento);
							}

							foreach ($files as $file4) {
								if ($file4 instanceof \Illuminate\Http\UploadedFile) {
									$ctrlDoc = hash('sha256', rand().time().$file4->getClientOriginalName()).'.pdf';
									$file4->move(public_path().'/img/SustanciaControlDoc/', $ctrlDoc);
									// Guardar el último documento subido, podrías cambiar esto si necesitas guardar varios documentos
									$sustancia->SustanciaControladaDocumento = $ctrlDoc;
								} else {
									// Manejar el caso en que $file4 no sea una instancia de UploadedFile
									// Esto podría ser un error inesperado
									return response()->json(['error' => 'File is not an instance of UploadedFile'], 400);
								}
							}

							// Guardar los cambios en la base de datos
							$sustancia->save();
						} else {
							$ctrlDoc = $sustancia->SustanciaControladaDocumento;
						}

						$SolicitudResiduo->FK_SolResRequerimiento = $nuevorequerimiento->ID_Req;
						$SolicitudResiduo->save();
					}
			}

		if(in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL)){

			} else {

			if($request->input('SolSerTipo') === '99'){

				$generadores = $request->input('SGenerador');
				$numeroDeGeneradores = count($generadores);

				//dd($numeroDeGeneradores);

				for($x=0; $x < $numeroDeGeneradores ; $x++){

					$slug = $generadores[$x];
					//dd($slug);

					$generadorRespel = DB::table('generadors')
					->join('gener_sedes', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
					->join('municipios', 'gener_sedes.FK_GSedeMun', '=', 'municipios.ID_Mun')
					->select('generadors.ID_Gener', 'generadors.GenerNit', 'generadors.GenerName', 'gener_sedes.GSedeAddress', 'municipios.ID_Mun','gener_sedes.ID_GSede')
					->where('GSedeSlug', $slug)
					->first();

					//dd($generadorRespel);

					$firmas = new FirmasServicios();
					$firmas->FK_SolSer = $ID_SolSer;
					$firmas->FK_Gener = $generadorRespel->ID_Gener;
					$firmas->FirmaCliente = '0';
					$firmas->FirmaConductor = '0';
					$firmas->FirmaPDA = '0';
					$firmas->SlugFirmas = hash('md5', rand() . time());
					$firmas->NombreFuncionario ='';
					$firmas->Cedula = '0';
					$firmas->Observaciones ='';
					$firmas->FK_SGener = $generadorRespel->ID_GSede;
					$firmas->save();
				}

			} else {
				// Para solicitudes tipo NULL (recepción en planta), buscar el generador del cliente
				$Cliente = DB::table('solicitud_servicios')
					->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
					->where('solicitud_servicios.ID_SolSer', $ID_SolSer)
					->select('clientes.ID_Cli', 'clientes.CliNit')
					->first();

				if (!$Cliente) {
					Log::error('Cliente no encontrado al crear firmas en createSolRes', [
						'ID_SolSer' => $ID_SolSer
					]);
					return; // Salir de la creación de firmas si no se encuentra el cliente
				}

				// Buscar el generador asociado al cliente por NIT
				$Generador = DB::table('generadors')
					->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
					->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
					->where('clientes.ID_Cli', $Cliente->ID_Cli)
					->where('generadors.GenerNit', $Cliente->CliNit)
					->where('generadors.GenerDelete', 0)
					->select('generadors.ID_Gener')
					->first();

				// Si no existe generador para este cliente, buscar por la primera sede del cliente
				if (!$Generador) {
					$Sede = DB::table('sedes')
						->where('FK_SedeCli', $Cliente->ID_Cli)
						->where('SedeDelete', 0)
						->first();

					if ($Sede) {
						$Generador = DB::table('generadors')
							->where('FK_GenerCli', $Sede->ID_Sede)
							->where('GenerDelete', 0)
							->select('ID_Gener')
							->first();
					}
				}

				$firmas = new FirmasServicios();
				$firmas->FK_SolSer = $ID_SolSer;
				$firmas->FK_Gener = $Generador ? $Generador->ID_Gener : null;
				$firmas->FK_SGener = 0; // Para recepción en planta, FK_SGener es 0
				$firmas->FirmaCliente = '0';
				$firmas->FirmaConductor = '0';
				$firmas->FirmaPDA = '0';
				$firmas->SlugFirmas = hash('md5', rand() . time());
				$firmas->NombreFuncionario ='';
				$firmas->Cedula = '0';
				$firmas->Observaciones ='';
				$firmas->save();

			}
		}
	}


/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$SolicitudServicio = DB::table('solicitud_servicios')
			->leftjoin('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->select('solicitud_servicios.*','personals.PersFirstName','personals.PersLastName', 'personals.PersEmail')
			->where('solicitud_servicios.SolSerSlug', $id)
			->first();
		if (!$SolicitudServicio) {
			abort(404);
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
					$ultimoRecordatorio = new \stdClass();
					$ultimoRecordatorio->ObsDate = $SolicitudServicio->updated_at;
					$ultimoRecordatorio->ObsRepeat = 0;
				}
				$ultimoRecordatorio->ObsRepeat = 0;
			}
		}


		$SolSerCollectAddress = $SolicitudServicio->SolSerCollectAddress;
		$SolSerConductor = $SolicitudServicio->SolSerConductor;
		if($SolicitudServicio->SolSerTipo == 'Interno'){
			$SolSerConductor = Personal::where('ID_Pers', $SolicitudServicio->SolSerConductor)->first();
		}
		if($SolicitudServicio->SolSerTypeCollect == 98){
			$Address = Sede::select(['SedeAddress', 'SedeName'])->where('ID_Sede',$SolicitudServicio->SolSerCollectAddress)->first();
			$SolSerCollectAddress = $Address ? $Address->SedeName.' - '.$Address->SedeAddress : 'Dirección no disponible';
		}
		$Municipio = null;
		if($SolicitudServicio->SolSerCityTrans <> null){
			$Municipio1 = DB::table('municipios')
				->select('MunName')
				->where('ID_Mun', $SolicitudServicio->SolSerCityTrans)
				->first();
			$Municipio = $Municipio1 ? $Municipio1->MunName : null;
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

			default:
				$Programaciones = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				// ->where('ProgVehEntrada', null)
				->where('ProgVehDelete', 0)
				->get();
				break;
		}
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
			->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			->select('gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.GenerName','gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
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
			->select('solicitud_residuos.*','residuos_geners.FK_SGener', 'respels.*', 'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			// ->where('requerimientos.ofertado', 1)
	        // ->where('forevaluation', 0)
			->get();

		$Residuos = $Residuosoriginal->map(function ($item) {
			$requerimientos = Requerimiento::with(['pretratamientosSelected', 'tarifa.rangos' => function($query){
				$query->orderBy('TarifaDesde');
			}])
			->where('ID_Req', $item->FK_SolResRequerimiento)
			// ->where('forevaluation', 0)
			->first();

			$rm = SolicitudResiduo::with('SolicitudServicio')->where('SolResSlug', $item->SolResSlug)->first(['SolResRM', 'FK_SolResSolSer']);

	        $item->pretratamientosSelected = $requerimientos->pretratamientosSelected;
	        $item->tarifa = $requerimientos->tarifa;
			if ($requerimientos->tarifa->TarifaSpecial === 1) {
				switch ($item->SolResTypeUnidad) {
					case 'Unidad':
						$tarifatipo = 'Unid';
						break;

					case 'Litros':
						$tarifatipo = 'Lt';
						break;

					default:
						$tarifatipo = 'Kg';
						break;
				}

				$tarifaResiduo = CTarifa::with('rangos')
					->where('FK_Cliente', $rm->SolicitudServicio->FK_SolSerCliente)
					->where('FK_Tratamiento', $requerimientos->FK_ReqTrata)
					->where('Tarifatipo', $tarifatipo)
					->first();

				if ($tarifaResiduo === null) {
					$item->ctarifa = null;
				}else{
					$item->ctarifa = $tarifaResiduo;
				}
			}else{
				$item->ctarifa = null;
			}
	        $item->SolResRM2 = $rm->SolResRM;
		  	return $item;
		});

		$SolicitudServicio->Repetible = 0;

		/* se convierte el tipo de dato a aray mediante la consulta en el modelo de la columna SolSerRMs usando eloquent*/
		$rms = SolicitudServicio::where('SolSerSlug', $SolicitudServicio->SolSerSlug)->first('SolSerRMs');
		$SolicitudServicio->SolSerRMs = $rms->SolSerRMs;

		//return $Residuos;

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

		/*se inicializan las variables para el calculo de totales */
		$total['estimado'] = 0;
		$total['recibido'] = 0;
		$total['conciliado'] = 0;
		$total['tratado'] = 0;
		$cantidadesXtratamiento = [];


		/* se itera sobre todos los residuos de las solicitudes de servicio */
		foreach ($SolicitudesServicioscount as $servicio) {
			foreach ($servicio->SolicitudResiduo as $residuo) {
				$collection = collect($cantidadesXtratamiento);

				/* si el tratamiento existe en la lista se suman las cantidadesxtratamiento y los totales correspondientes */
				if ($collection->has($residuo->requerimiento->tratamiento->TratName)) {
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['estimado'] = $cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['estimado'] + $residuo->SolResKgEnviado;
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['recibido'] = $cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['recibido'] + $residuo->SolResKgRecibido;
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['conciliado'] = $cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['conciliado'] + $residuo->SolResKgConciliado;
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['tratado'] = $cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['tratado'] + $residuo->SolResKgTratado;
					$total['estimado'] = $total['estimado'] + $residuo->SolResKgEnviado;
					$total['recibido'] = $total['recibido'] + $residuo->SolResKgRecibido;
					$total['conciliado'] = $total['conciliado'] + $residuo->SolResKgConciliado;
					$total['tratado'] = $total['tratado'] + $residuo->SolResKgTratado;
				}else{
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['estimado'] = $residuo->SolResKgEnviado;
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['recibido'] = $residuo->SolResKgRecibido;
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['conciliado'] = $residuo->SolResKgConciliado;
					$cantidadesXtratamiento[$residuo->requerimiento->tratamiento->TratName]['tratado'] = $residuo->SolResKgTratado;
					$total['estimado'] = $total['estimado'] + $residuo->SolResKgEnviado;
					$total['recibido'] = $total['recibido'] + $residuo->SolResKgRecibido;
					$total['conciliado'] = $total['conciliado'] + $residuo->SolResKgConciliado;
					$total['tratado'] = $total['tratado'] + $residuo->SolResKgTratado;
				}
			}
		}
		if (in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol, Permisos::SolSer1)) {
			$tratamientos = Tratamiento::join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
			->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->select('*')
			->where('TratDelete', 0)
			->get();
		}else{
			$tratamientos = 'NoAutorizado';
		}

		/* validacion para encontrar la fecha de recepción en planta del servicio */
		$fechaRecepcion = SolicitudServicio::find($servicio->ID_SolSer)->programacionesrecibidas()->first();
		if($fechaRecepcion){
			$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
		}else{
			$SolicitudServicio->recepcion = null;
		}

		//Buscar corrientes del residuo

			$PublicRespels = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->select('respels.ID_Respel', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->distinct()
			->get();

        // Buscar fotos del servicios
         // Obtener la solicitud
        $solicitud = DB::table('solicitud_servicios as ss')
            ->join('solicitud_residuos as sr', 'sr.FK_SolResSolSer', '=', 'ss.ID_SolSer')
            ->where('ss.SolSerSlug', $SolicitudServicio->SolSerSlug)
            ->select('ss.ID_SolSer', 'sr.ID_SolRes', 'ss.SolSerStatus')
            ->first();

        if (!$solicitud) {
            abort(404, 'Solicitud no encontrada');
        }

        // Obtener fotos de la solicitud
        $foto = DB::table('recursos as r')
            ->where('r.FK_RecSolRes', $solicitud->ID_SolRes)
            ->where('r.RecCarte', 'Foto')
            ->where('r.RecTipo', 'Pesaje-Descargue')
            ->orderBy('r.created_at', 'desc')
            ->exists();


        //return $fotos;

        // adjuntar variables segun status del servicio
        switch ($SolicitudServicio->SolSerStatus) {
            case 'Residuo Faltante':
            case 'Notificado':
            case 'Programado':
				//return $Residuos;
		       return view('solicitud-serv.show', compact('SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente', 'SolSerCollectAddress', 'SolSerConductor', 'TextProgramacion', 'Municipio', 'Programaciones', 'ProgramacionesActivas', 'total', 'cantidadesXtratamiento', 'tratamientos', 'Observaciones', 'PublicRespels', 'foto'));
                break;

            case 'Corregido':
            case 'Completado':
			//	return $tratamientos;
		       return view('solicitud-serv.show', compact('SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente', 'SolSerCollectAddress', 'SolSerConductor', 'TextProgramacion', 'Municipio', 'Programaciones', 'total', 'cantidadesXtratamiento', 'tratamientos', 'Observaciones', 'ultimoRecordatorio', 'PublicRespels', 'foto'));
                break;

            default:
			//return $tratamientos;
       		return view('solicitud-serv.show', compact('SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente', 'SolSerCollectAddress', 'SolSerConductor', 'TextProgramacion', 'Municipio', 'Programaciones', 'total', 'cantidadesXtratamiento', 'tratamientos', 'Observaciones', 'PublicRespels', 'foto'));
                break;
        }
	}


public function changestatus(Request $request)
	{
		$Solicitud = SolicitudServicio::where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}
		if ($Solicitud->SolSerStatus <> 'Certificacion') {
			if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA)){
				if ($Solicitud->SolSerStatus == 'Recepcionado' || $Solicitud->SolSerStatus == 'Corregido') {
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

			if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC)){
				switch ($request->input('solserstatus')) {
					case 'Aprobada':
						if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2 ) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2 )){
							$Solicitud->SolSerStatus = 'Aprobado';
						}
						break;
					case 'Aceptada':
						if(in_array(Auth::user()->UsRol, Permisos::APROBARAUDITORIA) || in_array(Auth::user()->UsRol2, Permisos::APROBARAUDITORIA)){
							$Solicitud->SolSerStatus = 'Aceptado';
						}
						break;
					case 'Recibida':
						if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::RECIBOMATERIAL)){
							$Solicitud->SolSerStatus = 'Completado';
							$Solicitud->save();

							// Solo enviar calificación si es recolección (NO recepción en planta)
							// Recepción en planta = SolSerTypeCollect es NULL
							// Recolección = SolSerTypeCollect tiene valor (97, 98, 99)
							if (!is_null($Solicitud->SolSerTypeCollect)) {
								// Buscar la firma asociada al servicio
								$firma = FirmasServicios::where('FK_SolSer', $Solicitud->ID_SolSer)->first();

								if ($firma) {
									// Crear calificación
									$calificacion = \App\Http\Controllers\CalificacionController::crearCalificacionDesdeRM(
										$firma->ID_Firmas,
										$Solicitud->ID_SolSer
									);

									// Enviar correo de notificación de calificación al cliente
									if ($calificacion) {
										\App\Http\Controllers\CalificacionController::notificarCliente($calificacion, $Solicitud, $firma);
									}
								}
							}
						}
						break;
					case 'Recepcionado':
						if(in_array(Auth::user()->UsRol, Permisos::RECEPCIONPDA) || in_array(Auth::user()->UsRol2, Permisos::RECEPCIONPDA)){
							$Solicitud->SolSerStatus = 'Recepcionado';
						}
						break;
					case 'Fallido':
						if(in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol2, Permisos::RECIBOMATERIAL)){
							$Solicitud->SolSerStatus = 'Fallido';
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
						if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::SolSer1)){
							$Solicitud->SolSerStatus = 'Conciliado';
						}
						break;
					case 'No Deacuerdo':
						if (in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2)){
							$Solicitud->SolSerStatus = 'No Conciliado';
						}
						break;
					case 'Certificada':
						if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi)){
							$Solicitud->SolSerStatus = 'Certificacion';
							$Solicitud->SolServCertStatus = 2;
							$Solicitud->SolSerDescript = $request->input('solserdescript');
							$Solicitud->save();

							$log = new audit();
							$log->AuditTabla="solicitud_servicios";
							$log->AuditType="Modificado Status";
							$log->AuditRegistro=$Solicitud->ID_SolSer;
							$log->AuditUser=Auth::user()->email;
							$log->Auditlog=$Solicitud->SolSerStatus;
							$log->save();

							// return redirect()->route('solicitud-servicio.index');
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

        if($Solicitud->SolSerStatus == 'Aprobado'){
            $destinatarios = [self::MAIL_AUDITORIAS_INTERNO];
            $observacion = Observacion::where('FK_ObsSolSer', $Solicitud->ID_SolSer)
                ->where('ObsStatus', 'Aprobado')
                ->orderBy('ObsDate', 'desc')
                ->first();
            Mail::to($destinatarios)->send(new AprobarAuditoria($Solicitud, $observacion));
        }

		if ($Solicitud->SolSerStatus == 'Conciliado') {
			$this->solservdocstore($Solicitud->ID_SolSer);

			$Solicitud->SolSerStatus = 'Conciliado';
			$Solicitud->SolServCertStatus = 2;
			$Solicitud->SolSerDescript = $request->input('solserdescript');
			$Solicitud->save();
			/** se guarda log en la tabla de auditoria */

			$log = new audit();
			$log->AuditTabla="solicitud_servicios";
			$log->AuditType="certificar";
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

			// Generar PDFs aquí (síncrono). Para solicitudes muy grandes, usar: php artisan certificados:generar {ID_SolSer}
			set_time_limit(600);
			@ini_set('memory_limit', '512M');
			$this->generarPdfsCertificadosRegulares($Solicitud, $request->input('solserRecepcionDate'), true);
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
				if (in_array(Auth::user()->UsRol, Permisos::ADMINPLANTA) || in_array(Auth::user()->UsRol2, Permisos::ADMINPLANTA)) {
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
			case 'Tratado':
			case 'Facturado':
				return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $Solicitud->SolSerSlug]);
				break;
			case 'Aceptado':
				return redirect()->route('solicitud-servicio.index');
				break;
			case 'Conciliado':
				return redirect()->route('solicitud-servicio.index');
			case 'Completado':
				return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $Solicitud->SolSerSlug]);
			case 'Recepcionado':
				return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $Solicitud->SolSerSlug]);
			default:
			    $slug = $Solicitud->SolSerSlug;
				return redirect()->route('email-solser', compact('slug'));
		}
	    return redirect()->route('solicitud-servicio.index');
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
						$SolResNew->SolResAuditoria = $SolResOld->SolResAuditoria;
					}
					$SolResNew->SolResVideoTratamiento = $SolResOld->SolResVideoTratamiento;
					$SolResNew->SolResDevolucion = $SolResOld->SolResVideoTratamiento;
					$SolResNew->SolResDevolCantidad = $SolResOld->SolResVideoTratamiento;
					$SolResNew->SolResAuditoria = $SolResOld->SolResVideoTratamiento;
					$SolResNew->SolResAuditoriaTipo = $SolResOld->SolResVideoTratamiento;
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
			$SolicitudServicio['comercial'] = $comercial;
			$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudNew->FK_SolSerPersona)->first();

			$destinatariosInterno = [self::MAIL_REGULARES_INTERNO];
			if (in_array($SolicitudServicio->SolSerTipo, ['Generador', 'Cliente'], true)) {
				$destinatariosInterno[] = self::MAIL_EXTERNO_INTERNO;
			}
			$destinatariosInterno = array_values(array_unique(array_filter($destinatariosInterno)));

			if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
				Mail::to($destinatariosInterno)->send(new NewSolServEmail($SolicitudServicio));
			} else {
				Mail::to($destinatariosInterno)->send(new NewSolServProsarcEmail($SolicitudServicio));
			}

			$log = new audit();
			$log->AuditTabla="Solicitud_servicios";
			$log->AuditType="servicio Repetido";
			$log->AuditRegistro=$SolicitudOld->ID_SolSer;
			$log->AuditUser=Auth::user()->email;
			$log->Auditlog=json_encode($SolicitudNew->ID_SolSer);
			$log->save();

			if($request->input('SolSerTipo') === '99'){

				$generadores = $request->input('SGenerador');
				$numeroDeGeneradores = count($generadores);

				//dd($numeroDeGeneradores);

				for($x=0; $x < $numeroDeGeneradores ; $x++){

					$slug = $generadores[$x];
					//dd($slug);

					$generadorRespel = DB::table('generadors')
					->join('gener_sedes', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
					->join('municipios', 'gener_sedes.FK_GSedeMun', '=', 'municipios.ID_Mun')
					->select('generadors.ID_Gener', 'generadors.GenerNit', 'generadors.GenerName', 'gener_sedes.GSedeAddress', 'municipios.ID_Mun','gener_sedes.ID_GSede')
					->where('GSedeSlug', $slug)
					->first();

					//dd($generadorRespel);

					$firmas = new FirmasServicios();
					$firmas->FK_SolSer = $SolicitudNew->ID_SolSer;
					$firmas->FK_Gener = $generadorRespel->ID_Gener;
					$firmas->FirmaCliente = '0';
					$firmas->FirmaConductor = '0';
					$firmas->FirmaPDA = '0';
					$firmas->SlugFirmas = hash('md5', rand() . time());
					$firmas->NombreFuncionario ='';
					$firmas->Cedula = '0';
					$firmas->Observaciones ='';
					$firmas->FK_SGener = $generadorRespel->ID_GSede;
					$firmas->save();
				}

			} else {
				// Para solicitudes tipo NULL (recepción en planta), buscar el generador del cliente
				$Cliente = DB::table('solicitud_servicios')
					->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
					->where('solicitud_servicios.ID_SolSer', $SolicitudNew->ID_SolSer)
					->select('clientes.ID_Cli', 'clientes.CliNit')
					->first();

				// Buscar el generador asociado al cliente por NIT
				$Generador = DB::table('generadors')
					->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
					->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
					->where('clientes.ID_Cli', $Cliente->ID_Cli)
					->where('generadors.GenerNit', $Cliente->CliNit)
					->where('generadors.GenerDelete', 0)
					->select('generadors.ID_Gener')
					->first();

				// Si no existe generador para este cliente, buscar por la primera sede del cliente
				if (!$Generador) {
					$Sede = DB::table('sedes')
						->where('FK_SedeCli', $Cliente->ID_Cli)
						->where('SedeDelete', 0)
						->first();

					if ($Sede) {
						$Generador = DB::table('generadors')
							->where('FK_GenerCli', $Sede->ID_Sede)
							->where('GenerDelete', 0)
							->select('ID_Gener')
							->first();
					}
				}

				$firmas = new FirmasServicios();
				$firmas->FK_SolSer = $SolicitudNew->ID_SolSer;
				$firmas->FK_Gener = $Generador ? $Generador->ID_Gener : null;
				$firmas->FK_SGener = 0; // Para recepción en planta, FK_SGener es 0
				$firmas->FirmaCliente = '0';
				$firmas->FirmaConductor = '0';
				$firmas->FirmaPDA = '0';
				$firmas->SlugFirmas = hash('md5', rand() . time());
				$firmas->NombreFuncionario ='';
				$firmas->Cedula = '0';
				$firmas->Observaciones ='';
				$firmas->save();

			}


			return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $SolicitudNew->SolSerSlug]);
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
        if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
            $Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
            if (!$Solicitud) {
                abort(404);
            }
            if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) && $Solicitud->SolSerStatus === 'Tratado' || $Solicitud->SolSerStatus === 'Certificacion' || $Solicitud->SolSerStatus === 'Completado'){
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
                ->select('gener_sedes.GSedeSlug', 'gener_sedes.GSedeName', 'generadors.GenerName', 'generadors.GenerNit')
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
            return view('solicitud-serv.edit', compact('Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios',  'Sedes', 'totalenviado', 'Requerimientos'));

        } elseif(in_array(Auth::user()->UsRol2, Permisos::RECEPCIONPDA)){
            $Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
            if (!$Solicitud) {
                abort(404);
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
                ->select('gener_sedes.GSedeSlug', 'gener_sedes.GSedeName', 'generadors.GenerName', 'generadors.GenerNit')
                ->where('clientes.ID_Cli', $Solicitud->FK_SolSerCliente )
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
                ->where('clientes.ID_Cli', $Solicitud->FK_SolSerCliente)
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
            //return $Departamentos;
			if(empty($Departamentos2)){
				return view('solicitud-serv.edit', compact('Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios',  'Sedes', 'totalenviado', 'Requerimientos'));
			} else {
            return view('solicitud-serv.edit', compact('Departamento2', 'Municipios2', 'Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios',  'Sedes', 'totalenviado', 'Requerimientos'));
			}
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
		$SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();
		if (!$SolicitudServicio) {
			abort(404);
		}
		$SolicitudServicio->SolServMailCopia = json_encode($request->input('SolServMailCopia'));

	    if ($SolicitudServicio->SolSerStatus === "Aprobado"||(($SolicitudServicio->SolSerStatus === "Programado"||$SolicitudServicio->SolSerStatus === "Notificado"||$SolicitudServicio->SolSerStatus === "Tratado" ||$SolicitudServicio->SolSerStatus === "Certificacion"||  $SolicitudServicio->SolSerStatus === "Completado")&&$SolicitudServicio->SolSerTipo !== 'Interno')){
			switch ($request->input('SolResAuditoriaTipo')) {
				case 99:
					$SolicitudServicio->SolSerAuditable = 2;
					$SolicitudServicio->SolResAuditoriaTipo = 'Virtual';
					break;
				case 98:
					$SolicitudServicio->SolSerAuditable = 1;
					$SolicitudServicio->SolResAuditoriaTipo = 'Presencial';
					break;
				case 97:
					$SolicitudServicio->SolSerAuditable = 0;
					$SolicitudServicio->SolResAuditoriaTipo = 'No Auditable';
					break;
			}
			$collect = null;
			$SolicitudServicio->FK_SolSerCollectMun = null;
			$direccioncollect = 'No aplica';
			switch ($request->input('SolSerTipo')) {
				case '96':
					$transportadorname = $request->input('SolSerNameTrans');
					$transportadornit = $request->input('SolSerNitTrans');
					$transportadoradress = $request->input('SolSerAdressTrans');
					$transportadorcity = $request->input('SolSerCityTrans');
					$tipo = "Externo";
					$conductor = $request->input('SolSerConductor');
					$vehiculo = $request->input('SolSerVehiculo');
					$FechaLlegada = $request->input('SolSerFecha');
					break;

				case '97':
					$generador = DB::table('generadors')
						->join('gener_sedes', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
						->join('municipios', 'gener_sedes.FK_GSedeMun', '=', 'municipios.ID_Mun')
						->select('generadors.ID_Gener', 'generadors.GenerNit', 'generadors.GenerName', 'gener_sedes.GSedeAddress', 'municipios.ID_Mun')
						->where('GSedeSlug', $request->input('SolSerTransportador'))
						->first();
					$transportadorname = $generador->GenerName;
					$transportadornit = $generador->GenerNit;
					$transportadoradress = $generador->GSedeAddress;
					$transportadorcity = $generador->ID_Mun;
					$tipo = "Generador";
					$conductor = $request->input('SolSerConductor');
					$vehiculo = $request->input('SolSerVehiculo');
					break;

				case '98':
					$cliente = DB::table('clientes')
						->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
						->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
						->select('clientes.ID_Cli', 'clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'sedes.SedeSlug', 'municipios.ID_Mun')
						->where('SedeSlug', $request->input('SolSerTransportador'))
						->first();
					$transportadorname = $cliente->CliName;
					$transportadornit = $cliente->CliNit;
					$transportadoradress = $cliente->SedeAddress;
					$transportadorcity = $cliente->ID_Mun;
					$tipo = "Cliente";
					$conductor = $request->input('SolSerConductor');
					$vehiculo = $request->input('SolSerVehiculo');
					$FechaLlegada = $request->input('SolSerFecha');
					break;

				case '99':
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
					switch ($request->input('SolSerTypeCollect')) {
						case 99:
							$direccioncollect = "Recolección en la sede de cada generador";
							$SolicitudServicio->FK_SolSerCollectMun = null;
							break;
						case 98:
							$sede = Sede::select(['ID_Sede', 'FK_SedeMun'])->where('SedeSlug', $request->input('SedeCollect'))->first();
							$direccioncollect = $sede->ID_Sede;
							$SolicitudServicio->FK_SolSerCollectMun = $sede->FK_SedeMun;
							break;
						case 97:
							$direccioncollect = $request->input('AddressCollect');
							$SolicitudServicio->FK_SolSerCollectMun = $request->input('FK_SolSerCollectMun');
							break;
						case null:
								$FechaLlegada = $request->input('SolSerFecha');
								$SolicitudServicio->SolSerFecha = $FechaLlegada;
								break;
					}
					$collect = $request->input('SolSerTypeCollect');
					break;

				default:
					# code...
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

			if(!is_null($request->input('SGenerador'))){
				$this->createSolRes($request, $SolicitudServicio->ID_SolSer);
			}
		}

		$SolicitudServicio->FK_SolSerPersona = Personal::select('ID_Pers')->where('PersSlug',$request->input('FK_SolSerPersona'))->first()->ID_Pers;
		$SolicitudServicio->SolSerDescript = $request->input('SolSerDescript');
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

        $destinatarios1 = [self::MAIL_AUDITORIAS_INTERNO];
		if ($SolicitudServicio->SolSerAuditable == 2 || $SolicitudServicio->SolSerAuditable == 1) {
			Mail::to($destinatarios1)->send(new SolserAuditar($SolicitudServicio));
		}
		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
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

		return redirect()->route('solicitud-servicio.index');
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

		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
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

		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
	}

	/**
	 * Genera los PDFs de certificados/manifiestos para una solicitud conciliada.
	 * Usa exactamente la misma lógica y plantillas que changestatus (Conciliado).
	 * Invocable desde el comando certificados:generar.
	 *
	 * @param SolicitudServicio $Solicitud
	 * @param string|null $solserRecepcionDate Fecha de recepción (opcional)
	 * @param bool $enviarEmail Si enviar correos de notificación
	 */
	public function generarPdfsCertificadosRegulares($Solicitud, $solserRecepcionDate = null, $enviarEmail = true)
	{
		$certificados = Certificado::with(['certdato.solres', 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento', 'SolicitudServicio' => function ($query){
			$query->with(['SolicitudResiduo' => function ($query){
				$query->where('SolResKgConciliado', '>', 0);
				$query->orWhere('SolResCantiUnidadConciliada', '>', 0);
				$query->with('generespel.respels');
				$query->with('requerimiento');
			}]);
		}])
		->where('FK_CertSolser', $Solicitud->ID_SolSer)
		->get();

		foreach ($certificados as $certificado) {
			set_time_limit(300);

			$fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
			if ($fecharecepcionenplanta != null) {
				$fechaLlegadaPlanta = $fecharecepcionenplanta->ProgVehSalida;
				$certificado->recepcion = $fechaLlegadaPlanta;
			} else {
				$certificado->recepcion = "";
				$fechaLlegadaPlanta = $certificado->created_at ?? null;
			}

			$certificado->solserRecepcionDate = $solserRecepcionDate ?? $certificado->created_at;

			$qrCode = new QrCode(route('certificados.show', ['certificado' => $certificado->CertSlug]));
			$qrCode->setLogoSize(60, 60);
			$qrCode->setSize(300);
			$qrCode->setMargin(0);
			$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);
			$publicDisk = Storage::disk('public');

			switch (trim($certificado->tratamiento->TratName ?? '')) {
				case 'TermoDestrucción':
					$pdf = PDF::setPaper('letter', 'portrait')->loadView('certificados.topdf', compact(['certificado', 'Solicitud', 'qrCode', 'fechaLlegadaPlanta']));
					$nombre = $certificado->CertSlug . '.pdf';
					$path = 'certificadoRegular/' . sprintf("%0s", $nombre);
					$publicDisk->makeDirectory('certificadoRegular');
					if (!$publicDisk->put($path, $pdf->output())) {
						Log::error('No se pudo guardar el certificado regular en disco', ['ID_Cert' => $certificado->ID_Cert, 'path' => $path]);
						continue 2;
					}
					Certificado::where('ID_Cert', $certificado->ID_Cert)->update(['CertSrc' => $nombre]);

					$collection2 = collect([]);
					$uniquestring = 'Sin RM';
					foreach($certificado->SolicitudServicio->SolicitudResiduo as $Residuo){
						if($Residuo->requerimiento->FK_ReqTrata == $certificado->FK_CertTrat && $Residuo->generespel->gener_sedes->ID_GSede == $certificado->FK_CertGenerSede) {
							if($Residuo->SolResRM2 !== null && is_array($Residuo->SolResRM2)) {
								foreach ($Residuo->SolResRM2 as $value2) {
									$collection2 = $collection2->concat([$value2]);
								}
							} else {
								if (is_array($Residuo->SolResRM)) {
									foreach ($Residuo->SolResRM as $value) {
										$collection2 = $collection2->concat([$value]);
									}
								} else {
									$uniquestring = 'RM Invalido -> '.$Residuo->SolResRM;
								}
							}
						}
					}
					$uniqueKey = md5($certificado->ID_Cert . $certificado->FK_CertTrat . $certificado->FK_CertGenerSede);
					if (!Cache::has($uniqueKey)) {
						$uniquestring = $collection2->isNotEmpty() ? collect($collection2->unique())->values()->join(', ') : $uniquestring;
						Certificado::where('ID_Cert', $certificado->ID_Cert)->update(['CertNumRm' => $uniquestring]);
						if ($enviarEmail) {
							$servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
							$cliente = Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first();
							try {
								Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdated($certificado, $servicio, $cliente));
							} catch (\Throwable $e) {
								Log::warning('Fallo envio CertUpdated (regular)', [
									'certificado_id' => $certificado->ID_Cert,
									'servicio_id' => $servicio->ID_SolSer ?? null,
									'error' => $e->getMessage(),
								]);
							}
							Cache::put($uniqueKey, true, 1440);
						}
					}
					break;

				default:
					$pdf = PDF::setPaper('letter', 'portrait')->loadView('certificados.topdfmanifiesto', compact(['certificado', 'Solicitud', 'qrCode', 'fechaLlegadaPlanta']));
					$nombre = $certificado->CertSlug . '.pdf';
					$path = 'manifiestosRegular/' . sprintf("%0s", $nombre);
					$publicDisk->makeDirectory('manifiestosRegular');
					if (!$publicDisk->put($path, $pdf->output())) {
						Log::error('No se pudo guardar el manifiesto en disco', ['ID_Cert' => $certificado->ID_Cert, 'path' => $path]);
						continue 2;
					}
					Certificado::where('ID_Cert', $certificado->ID_Cert)->update(['CertSrc' => $nombre]);

					$collection2 = collect([]);
					$uniquestring = 'Sin RM';
					foreach($certificado->SolicitudServicio->SolicitudResiduo as $Residuo){
						if($Residuo->requerimiento->FK_ReqTrata == $certificado->FK_CertTrat && $Residuo->generespel->gener_sedes->ID_GSede == $certificado->FK_CertGenerSede) {
							if($Residuo->SolResRM2 !== null && is_array($Residuo->SolResRM2)) {
								foreach ($Residuo->SolResRM2 as $value2) {
									$collection2 = $collection2->concat([$value2]);
								}
							} else {
								if (is_array($Residuo->SolResRM)) {
									foreach ($Residuo->SolResRM as $value) {
										$collection2 = $collection2->concat([$value]);
									}
								} else {
									$uniquestring = 'RM Invalido -> '.$Residuo->SolResRM;
								}
							}
						}
					}
					$uniqueKey = md5($certificado->ID_Cert . $certificado->FK_CertTrat . $certificado->FK_CertGenerSede);
					if (!Cache::has($uniqueKey)) {
						$uniquestring = $collection2->isNotEmpty() ? collect($collection2->unique())->values()->join(', ') : 'Sin RM';
						Certificado::where('ID_Cert', $certificado->ID_Cert)->update(['CertNumRm' => $uniquestring]);
						if ($enviarEmail) {
							$servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
							$cliente = Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first();
							try {
								Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdated($certificado, $servicio, $cliente));
							} catch (\Throwable $e) {
								Log::warning('Fallo envio CertUpdated (manifiesto regular)', [
									'certificado_id' => $certificado->ID_Cert,
									'servicio_id' => $servicio->ID_SolSer ?? null,
									'error' => $e->getMessage(),
								]);
							}
							Cache::put($uniqueKey, true, 1440);
						}
					}
					break;
			}
		}
	}

	public function solservdocstore($id)
	{
		Log::info('solservdocstore: inicio', ['ID_SolSer' => $id]);

		$SolicitudServicio = SolicitudServicio::with(['SolicitudResiduo.requerimiento.tarifa.rangos' => function ($query){
			$query->orderBy('TarifaDesde', 'desc');
		}])->where('ID_SolSer', $id)->first();
		if (!$SolicitudServicio) {
			Log::warning('solservdocstore: solicitud no encontrada', ['ID_SolSer' => $id]);
			return;
		}
		$serviciovalidado = $id;
		/*cuenta los diferentes generadores*/
		$generadoresdelasolicitud = GenerSede::whereHas('resgener.solres', function ($query) use ($serviciovalidado) {
		    $query->where('solicitud_residuos.FK_SolResSolSer', $serviciovalidado);
		})
		->with(['resgener' => function ($query) use ($serviciovalidado){
		    $query->with(['solres' => function ($query) use ($serviciovalidado){
		    	$query->where('FK_SolResSolSer', $serviciovalidado);
		    	$query->with('requerimiento.tratamiento.gestor');
		    }]);
		    $query->whereHas('solres', function ($query) use ($serviciovalidado){
		    	$query->where('FK_SolResSolSer', $serviciovalidado);
		    });
		}])
		->get();

		if ($generadoresdelasolicitud->isEmpty()) {
			Log::warning('solservdocstore: ningún generador/sede con residuos vinculados a esta solicitud (revisar FK_SolResRg en solicitud_residuos)', ['ID_SolSer' => $id]);
			return;
		}
		Log::info('solservdocstore: generadores encontrados', ['ID_SolSer' => $id, 'cantidad_generadores' => $generadoresdelasolicitud->count()]);

		/*consulta para el cliente de esta solicitud*/
		$cliente = Cliente::whereHas('sedes.generador', function ($query) use ($generadoresdelasolicitud) {
		    $query->where('generadors.ID_Gener', $generadoresdelasolicitud[0]->FK_GSede);
		})->first();
		if (!$cliente) {
			Log::warning('solservdocstore: no se encontró cliente para el generador de la solicitud', ['ID_SolSer' => $id, 'FK_GSede' => $generadoresdelasolicitud[0]->FK_GSede ?? null]);
			return;
		}
		foreach ($generadoresdelasolicitud as $genersede) {
			foreach ($genersede->resgener as $resgener) {
				foreach ($resgener->solres as $key) {
					if ($key->SolResKgConciliado <= 0) {
						continue;
					}
					$requerimiento = $key->requerimiento;
					if (!$requerimiento || !$requerimiento->tratamiento) {
						Log::warning('solservdocstore: residuo sin requerimiento o tratamiento', ['ID_SolRes' => $key->ID_SolRes ?? null, 'ID_SolSer' => $id]);
						continue;
					}
					$tratamiento = $requerimiento->tratamiento;
					$tratTipo = $tratamiento->TratTipo;
					if ($tratTipo !== null && $tratTipo !== '') {
						$tratTipo = (int) $tratTipo;
					}
					Log::info('solservdocstore: procesando residuo', ['ID_SolRes' => $key->ID_SolRes, 'ID_SolSer' => $id, 'TratTipo' => $tratTipo, 'TratName' => $tratamiento->TratName ?? null, 'ID_Trat' => $tratamiento->ID_Trat ?? null]);
					switch ($tratTipo) {
						case 0:
							// "tratamiento tipo: interno; Certificado"
							$gestorSedeCli = null;
							if ($tratamiento->gestor) {
								$gestorSedeCli = $tratamiento->gestor->FK_SedeCli;
							}
							if ($gestorSedeCli === null) {
								Log::warning('solservdocstore: tratamiento sin gestor (FK_TratProv o Sede->FK_SedeCli)', ['ID_Trat' => $tratamiento->ID_Trat ?? null, 'ID_SolSer' => $id]);
								continue 2;
							}

							$certificadoprevio = Certificado::where('FK_CertTrat', $tratamiento->ID_Trat)
								->where('FK_CertSolser', $id)
								->where('FK_CertGenerSede', $genersede->ID_GSede)
								->first();

							if ($certificadoprevio && $certificadoprevio->FK_CertTrat == $tratamiento->ID_Trat && $certificadoprevio->FK_CertGenerSede == $genersede->ID_GSede) {
								$dato = new Certdato;
								$dato->FK_DatoCert = $certificadoprevio->ID_Cert;
								$dato->FK_DatoCertSolRes = $key->ID_SolRes;
								$dato->save();
								Log::info('solservdocstore: Certdato añadido a certificado existente', ['ID_SolSer' => $id, 'ID_Cert' => $certificadoprevio->ID_Cert, 'ID_SolRes' => $key->ID_SolRes]);
							} else {
								$certificado = new Certificado;
								$certificado->CertType = (trim($tratamiento->TratName ?? '') === 'TermoDestrucción') ? 0 : 1;
								$certificado->CertObservacion = $certificado->CertType === 0 ? 'certificado con observacion generica' : 'manifiesto con observacion generica';
								$certificado->CertNumero = '';
								$certificado->CertManifNumero = '';
								$certificado->CertManifPrepend = '';
								$certificado->CertiEspName = '';
								$certificado->CertiEspValue = '';
								$certificado->CertSlug = hash('sha256', rand().time());
								$certificado->CertSrc = 'CertificadoDefault.pdf';
								$certificado->CertAuthHseq = 0;
								$certificado->CertAuthJl = 0;
								$certificado->CertAuthDp = 0;
								$certificado->CertAuthJo = 0;
								$certificado->CertAnexo = 'anexo de certificado ' . ($tratamiento->TratName ?? '') . ($tratamiento->FK_TratProv ?? '');
								$certificado->FK_CertSolser = (int) $id;
								$certificado->FK_CertCliente = $cliente->ID_Cli;
								$certificado->FK_CertGenerSede = $genersede->ID_GSede;
								$certificado->FK_CertGestor = $gestorSedeCli;
								$certificado->FK_CertTrat = $tratamiento->ID_Trat;
								switch ($SolicitudServicio->SolSerTipo) {
									case 'Externo':
									case 'Cliente':
									case 'Generador':
										$certificado->FK_CertTransp = $cliente->ID_Cli;
										break;
									case 'Interno':
										$certificado->FK_CertTransp = 1;
										break;
									default:
										$certificado->FK_CertTransp = 1;
										break;
								}
								$certificado->save();

								$dato = new Certdato;
								$dato->FK_DatoCert = $certificado->ID_Cert;
								$dato->FK_DatoCertSolRes = $key->ID_SolRes;
								$dato->save();
								Log::info('solservdocstore: certificado creado', ['ID_SolSer' => $id, 'ID_Cert' => $certificado->ID_Cert, 'ID_SolRes' => $key->ID_SolRes]);
							}
							break;

						case 1:
							// "tratamiento tipo: externo ; manifiesto" — siempre se crea una línea adicional: nuevo Manifiesto + nuevo Certificado (CertType=2) por residuo, sin reutilizar
							$manifGestorSedeCli = $tratamiento->gestor ? $tratamiento->gestor->FK_SedeCli : null;
							if ($manifGestorSedeCli === null) {
								Log::warning('solservdocstore: MANIFIESTO NO CREADO - tratamiento tipo 1 sin gestor (FK_TratProv o Sede->FK_SedeCli)', ['ID_Trat' => $tratamiento->ID_Trat ?? null, 'TratName' => $tratamiento->TratName ?? null, 'ID_SolSer' => $id, 'ID_SolRes' => $key->ID_SolRes]);
								break;
							}

							// Siempre crear nuevo Manifiesto (una línea por residuo)
							$manifiesto = new Manifiesto;
							$manifiesto->ManifNumero = '';
							$manifiesto->ManifiEspName = '';
							$manifiesto->ManifiEspValue = '';
							$manifiesto->ManifObservacion = 'manifiesto con observacion generica';
							$manifiesto->ManifSlug = hash('sha256', rand().time());
							$manifiesto->ManifSrc = 'ManifiestoDefault.pdf';
							$manifiesto->ManifNumRm = 'M-16';
							$manifiesto->ManifAuthHseq = 0;
							$manifiesto->ManifAuthJl = 0;
							$manifiesto->ManifAuthDp = 0;
							$manifiesto->ManifAuthJo = 0;
							$manifiesto->ManifAnexo = 'anexo de manifiesto ' . ($tratamiento->TratName ?? '') . ($tratamiento->FK_TratProv ?? '');
							$manifiesto->FK_ManifSolser = (int) $id;
							$manifiesto->FK_ManifCliente = $cliente->ID_Cli;
							$manifiesto->FK_ManifGenerSede = $genersede->ID_GSede;
							$manifiesto->FK_ManifGestor = $manifGestorSedeCli;
							$manifiesto->FK_ManifTrat = $tratamiento->ID_Trat;
							// FK_ManifTransp -> clientes.ID_Cli (no confundir con ID_GSede del generador)
							switch ($SolicitudServicio->SolSerTipo) {
								case 'Externo':
								case 'Cliente':
								case 'Generador':
									$manifiesto->FK_ManifTransp = $cliente->ID_Cli;
									break;
								case 'Interno':
								default:
									$manifiesto->FK_ManifTransp = 1;
									break;
							}
							$manifiesto->save();
							$dato = new Manifdato;
							$dato->FK_DatoManif = $manifiesto->ID_Manif;
							$dato->FK_DatoManifSolRes = $key->ID_SolRes;
							$dato->save();
							Log::info('solservdocstore: manifiesto creado (línea adicional)', ['ID_SolSer' => $id, 'ID_Manif' => $manifiesto->ID_Manif, 'ID_SolRes' => $key->ID_SolRes]);

							// Línea en certificados con CertType=1 (manifiesto) para que aparezca en el index; luego en edit se puede agregar cert. de terceros como línea adicional (tipo 2)
							$certificadoManif = new Certificado;
							$certificadoManif->CertType = 1;
							$certificadoManif->CertObservacion = 'manifiesto con observacion generica';
							$certificadoManif->CertNumero = '';
							$certificadoManif->CertManifNumero = '';
							$certificadoManif->CertManifPrepend = '';
							$certificadoManif->CertiEspName = '';
							$certificadoManif->CertiEspValue = '';
							$certificadoManif->CertSlug = hash('sha256', rand().time());
							$certificadoManif->CertSrc = 'ManifiestoDefault.pdf';
							$certificadoManif->CertAuthHseq = 0;
							$certificadoManif->CertAuthJl = 0;
							$certificadoManif->CertAuthDp = 0;
							$certificadoManif->CertAuthJo = 0;
							$certificadoManif->CertAnexo = 'anexo de manifiesto ' . ($tratamiento->TratName ?? '') . ($tratamiento->FK_TratProv ?? '');
							$certificadoManif->FK_CertSolser = (int) $id;
							$certificadoManif->FK_CertCliente = $cliente->ID_Cli;
							$certificadoManif->FK_CertGenerSede = $genersede->ID_GSede;
							$certificadoManif->FK_CertGestor = $manifGestorSedeCli;
							$certificadoManif->FK_CertTrat = $tratamiento->ID_Trat;
							switch ($SolicitudServicio->SolSerTipo) {
								case 'Externo':
								case 'Cliente':
								case 'Generador':
									$certificadoManif->FK_CertTransp = $cliente->ID_Cli;
									break;
								case 'Interno':
								default:
									$certificadoManif->FK_CertTransp = 1;
									break;
							}
							$certificadoManif->save();
							$datoCert = new Certdato;
							$datoCert->FK_DatoCert = $certificadoManif->ID_Cert;
							$datoCert->FK_DatoCertSolRes = $key->ID_SolRes;
							$datoCert->save();
							Log::info('solservdocstore: certificado tipo 1 (manifiesto) creado (línea en index)', ['ID_SolSer' => $id, 'ID_Cert' => $certificadoManif->ID_Cert, 'ID_SolRes' => $key->ID_SolRes]);
							break;

						default:
							Log::warning('solservdocstore: MANIFIESTO/CERT NO CREADO - TratTipo no es 0 ni 1 (solo 0=certificado, 1=manifiesto)', ['ID_SolRes' => $key->ID_SolRes ?? null, 'TratTipo' => $tratTipo, 'ID_Trat' => $tratamiento->ID_Trat ?? null, 'TratName' => $tratamiento->TratName ?? null, 'ID_SolSer' => $id]);
							break;
					}
				}
			}
		}

		Log::info('solservdocstore: fin', ['ID_SolSer' => $id]);

		/*ajuste de los precios para facturacion en cada residuo de la solicitud segun los rangos de tarifas */

		foreach ($SolicitudServicio->SolicitudResiduo as $key => $solres) {
			switch ($solres->SolResTypeUnidad) {
				case 'Unidad':
					$tarifatipo = 'Unid';
					break;

				case 'Litros':
					$tarifatipo = 'Lt';
					break;

				default:
					$tarifatipo = 'Kg';
					break;
			}

			$tarifaCliente = CTarifa::with('rangos')
			->where('FK_Cliente', $cliente->ID_Cli)
			->where('FK_Tratamiento', $solres->requerimiento->FK_ReqTrata)
			->where('Tarifatipo', $tarifatipo)
			->first();

			$tarifaResiduo = $solres->requerimiento->tarifa;


			$residuoparaprecio = SolicitudResiduo::where('ID_SolRes', $solres->ID_SolRes)->first();

			if ($tarifaResiduo === null || $solres->SolResKgConciliado <= 0) {
				$residuoparaprecio->SolResPrecio = 0;
			} else {
				if ($tarifaResiduo->TarifaSpecial === 1) {
					foreach ($tarifaResiduo->rangos as $rango) {
						switch ($solres->SolResTypeUnidad) {
							case 'Unidad':
							case 'Litros':
								if ($solres->SolResCantiUnidadConciliada >= $rango->TarifaDesde) {
									$residuoparaprecio->SolResPrecio = $rango->TarifaPrecio;
									$residuoparaprecio->SolResTypePrecio = 1;
									break 2;
								}
								break;
							default:
								if ($solres->SolResKgConciliado >= $rango->TarifaDesde) {
									$residuoparaprecio->SolResPrecio = $rango->TarifaPrecio;
									$residuoparaprecio->SolResTypePrecio = 1;
									break 2;
								}
								break;
						}
					}
				}else{
					if ($tarifaCliente !== null) {
						foreach ($tarifaCliente->rangos as $rango) {
							switch ($solres->SolResTypeUnidad) {
								case 'Unidad':
								case 'Litros':
									if ($solres->SolResCantiUnidadConciliada > $rango->CTarifaDesde) {
										$residuoparaprecio->SolResPrecio = $rango->CTarifaPrecio;
										$residuoparaprecio->SolResTypePrecio = 2;
										break 2;
									}
									break;
								default:
									if ($solres->SolResKgConciliado > $rango->CTarifaDesde) {
										$residuoparaprecio->SolResPrecio = $rango->CTarifaPrecio;
										$residuoparaprecio->SolResTypePrecio = 2;
										break 2;
									}
									break;
							}
						}
					}

				}
			}
			$residuoparaprecio->save();
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
		if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL)){
			$Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
			if (!$Solicitud) {
				abort(404);
			}
			if($Solicitud->SolSerStatus !== 'Residuo Faltante' && $Solicitud->SolSerStatus !== 'Programado'  && $Solicitud->SolSerStatus !== 'Notificado'){
				abort(403, 'El servicio no se encuentra en el estado correcto para añadir residuos');
			}
			$Departamento = null;
			$Municipios = collect();
			$Departamento2 = null;
			$Municipios2 = collect();
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
				->where('clientes.ID_Cli', $Solicitud->FK_SolSerCliente)
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
            //return view('solicitud-serv.addrespel', compact('Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios', 'Departamento2','Municipios2', 'Sedes', 'totalenviado', 'Requerimientos'));
			//return view('solicitud-serv.addrespel', compact('Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios', 'Sedes', 'totalenviado', 'Requerimientos',);
			return view('solicitud-serv.addrespel', compact('Solicitud','Cliente','Persona','Personals','Departamentos','SGeneradors', 'Departamento','Municipios','Departamento2','Municipios2', 'Sedes', 'totalenviado', 'Requerimientos'));
			//return $SGeneradors;
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

		if(in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL)){
			$SolicitudServicio->SolSerStatus = 'Programado';
		} else {
			$SolicitudServicio->SolSerStatus = 'Notificado';
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
		$Observacion->ObsTipo = 'cliente';
		$Observacion->ObsRepeat = 1;
		$Observacion->ObsDate = now();
		$Observacion->ObsUser = Auth::user()->email;
		$Observacion->ObsRol = Auth::user()->UsRol;
		$Observacion->FK_ObsSolSer = $SolicitudServicio->ID_SolSer;
		$Observacion->save();

		$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();
		$destinatarios = [self::MAIL_PROGRAMACIONES_INTERNO];
		$destinatarioscc = [];
		$comercial = "";

		if (!is_null($SolicitudServicio['cliente']) && $SolicitudServicio['cliente']->CliComercial <> null) {
			$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
		}

		$SolicitudServicio['comercial'] = $comercial;
		$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();

		// Copias del cliente (solo fuera de solicitud regular comercial; regulares concentran avisos en buzones internos).
		if (! in_array($SolicitudServicio->SolSerTipo, ['Cliente', 'Externo', 'Generador'], true)
			&& ! is_null($SolicitudServicio->SolServMailCopia)
			&& $SolicitudServicio->SolServMailCopia !== 'null') {
			$mailCopia = json_decode($SolicitudServicio->SolServMailCopia, true);
			if (is_array($mailCopia)) {
				foreach ($mailCopia as $value) {
					if (!empty($value)) {
						array_push($destinatarioscc, $value);
					}
				}
			}
		}

		$destinatarios = array_values(array_unique(array_filter($destinatarios)));
		$destinatarioscc = array_values(array_unique(array_filter($destinatarioscc)));

		try {
			Mail::to($destinatarios)->cc($destinatarioscc)->send(new SolSerLeftRespel($SolicitudServicio));
		} catch (\Throwable $e) {
			Log::error('No fue posible enviar correo de residuos faltantes en updateRespel', [
				'solicitud_id' => $SolicitudServicio->ID_SolSer,
				'solicitud_slug' => $SolicitudServicio->SolSerSlug,
				'destinatarios' => $destinatarios,
				'destinatarios_cc' => $destinatarioscc,
				'error' => $e->getMessage(),
			]);
		}

		if(in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL)){
			return redirect()->route('recibo.material', ['id' => $id]);
		} else {
			return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function serviciosRecepcionado()
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

		//return view('solicitud-serv.indexrecordatorios', compact('Servicios', 'Residuos', 'Cliente'));
		return view('solicitud-serv.indexrecordatorios', compact('Servicios', 'Cliente'));
	}

	public function reversarStatus(Request $request)
	{
		$Solicitud = SolicitudServicio::with('SolicitudResiduo')->where('SolSerSlug', $request->input('solserslug'))->first();
		if (!$Solicitud) {
			abort(404);
		}
		if ($Solicitud->SolSerStatus == 'Certificacion') {
			if (!in_array(Auth::user()->UsRol, Permisos::REVERSARADMIN) && !in_array(Auth::user()->UsRol2, Permisos::REVERSARADMIN)) {
				abort(403, 'el servicio ya ha sido certificado y no admite cambios de status');
			}
		}
        // se guarda el status nuevo y el anterior
        $oldValue = $Solicitud->SolSerStatus;
        $newValue = $request->input('solserstatus');


		switch ($request->input('solserstatus')) {
			case 'Notificado':
			case 'Completado':
			case 'Residuo Faltante':
			case 'Corregido':
			case 'Programado':
			case 'No Conciliado':
			case 'Residuo Faltante':
				if ($Solicitud->SolSerStatus == 'Conciliado'||$Solicitud->SolSerStatus == 'Tratado'||$Solicitud->SolSerStatus == 'Certificacion'||$Solicitud->SolSerStatus == 'Facturado') {
					// Certificado (0) y Manifiesto (1): borrar documento y certdato para que se recreen con las modificaciones.
					// Certificado de terceros (2): NO tocar, preservar documento y certdato.
					$certificadosDelete = Certificado::with('certdato')->where('FK_CertSolser', $Solicitud->ID_SolSer)->get();
					foreach ($certificadosDelete as $key => $value) {
						if ((int) $value->CertType === 2) {
							continue; // No borrar certificados de terceros
						}
						foreach ($value->certdato as $key2 => $value2) {
							$value2->delete();
						}
						$value->delete();
					}
					foreach ($Solicitud->SolicitudResiduo as $key => $residuoparareversar) {
						$residuoparareversar->SolResPrecio = 0;
						$residuoparareversar->SolResTypePrecio = 0;
						$residuoparareversar->save();
					}
					$prefaturaToDelete = Prefactura::with('prefacTratamiento.prefacresiduo')->where('FK_Servicio', $Solicitud->ID_SolSer)->get();
					foreach ($prefaturaToDelete as $key => $value) {
						foreach ($value->prefacTratamiento as $key2 => $value2) {
							foreach ($value2->prefacresiduo as $key3 => $value3) {
								$value3->delete();
							}
							$value2->delete();
						}
						$value->delete();
					}
				}
				break;

			case 'Conciliado':
				if ($Solicitud->SolSerStatus == 'Tratado'||$Solicitud->SolSerStatus == 'Certificacion'||$Solicitud->SolSerStatus == 'Facturado') {
					$prefaturaToDelete = Prefactura::with('prefacTratamiento.prefacresiduo')->where('FK_Servicio', $Solicitud->ID_SolSer)->get();
					foreach ($prefaturaToDelete as $key => $value) {
						foreach ($value->prefacTratamiento as $key2 => $value2) {
							foreach ($value2->prefacresiduo as $key3 => $value3) {
								$value3->delete();
							}
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

        $Solicitud['oldValue'] = $oldValue;
        $Solicitud['newValue'] = $newValue;

        $SolicitudServicio = $Solicitud;

        $SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();

        Mail::to(self::MAIL_REGULARES_INTERNO)->send(new ServicioReversado($SolicitudServicio, $Observacion));

		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $Solicitud->SolSerSlug]);
		//return redirect()->route('solicitud-servicio.show', ['id' => $Solicitud->SolSerSlug]);

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
		return redirect()->route('solicitud-servicio.index');
	}

	/**
	 * ingresa el numero de factura a la base de datos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	public function NumFactura(Request $request, $id)
	{

		$Solicitud = SolicitudServicio::where('SolSerSlug', $id)->first();
		if (!$Solicitud) {
			abort(404);
		}

		$Solicitud->SolNumeroFactura=$request->input('numero_factura');
		$Solicitud->save();

		$log = new audit();
		$log->AuditTabla="solicitud_servicios";
		$log->AuditType="actualizado la FVE";
		$log->AuditRegistro=$Solicitud->ID_SolSer;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$request;
		$log->save();

		//return $Solicitud;

		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);

	}

	/**
	 * ingresa el numero de factura a la base de datos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	 public function recibomaterial($id)
	 {
		 $users = Auth::user();

		 // ===================== CARGA INICIAL DE LA SOLICITUD =====================
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
				 $SolSerCollectAddress = $Address ? $Address->SedeName . ' - ' . $Address->SedeAddress : 'Dirección no disponible';
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
					 $total['recibido']   += $residio->SolResKgRecibido ?? 0;
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
				 $SolSerCollectAddress = $Address ? $Address->SedeName . ' - ' . $Address->SedeAddress : 'Dirección no disponible';
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
					 return view('solicitud-serv.rm', compact(
						 'SolicitudServicio', 'Residuos', 'GenerResiduos', 'Cliente',
						 'SolSerConductor', 'Programaciones', 'ProgramacionesActivas',
						 'total', 'cantidadesXtratamiento', 'tratamientos', 'PublicRespels'
					 ));
					 break;
				 case 'Programado':
					 return view('solicitud-serv.rm', compact(
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
	 * ingresa el numero de factura a la base de datos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	 public function firmacliente(Request $request, $id)
	 {
		//return $request;
		 $idGener = $request->input('ID_Gener');
		 $slugFirmas = $request->input('SlugFirmas');

		 $solser = DB::table('solicitud_servicios')
			 ->select('ID_SolSer', 'SolSerTypeCollect')
			 ->where('SolSerSlug', $id)
			 ->first();

		$firmacliente = null;
		if ($solser) {
			// Prioridad 1: cuando llega SlugFirmas (flujo de planta), actualizar exactamente esa fila.
			if (!empty($slugFirmas)) {
				$firmacliente = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('SlugFirmas', $slugFirmas)
					->first();
			}

			// Prioridad 2: flujo regular por FK_SGener.
			if (!$firmacliente && !empty($idGener)) {
				$firmacliente = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('FK_SGener', $idGener)
					->first();
			}

			// Compatibilidad: si en alguna vista enviaron ID_Gener por error.
			if (!$firmacliente && !empty($idGener)) {
				$firmacliente = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('FK_Gener', $idGener)
					->first();
			}

			// Fallback planta: en servicios de planta la fila de firmas suele estar en FK_SGener = 0.
			if (!$firmacliente && is_null($solser->SolSerTypeCollect)) {
				$firmacliente = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->where('FK_SGener', 0)
					->first();
			}

			// Último fallback para roles amplios.
			if (
				!$firmacliente &&
				(in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) ||
				 in_array(Auth::user()->UsRol, Permisos::SUPERVISOR) ||
				 in_array(Auth::user()->UsRol, Permisos::TESORERIA) ||
				 in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) ||
				 in_array(Auth::user()->UsRol, Permisos::LOGISTICA))
			) {
				$firmacliente = DB::table('firmas_servicio')
					->where('FK_SolSer', $solser->ID_SolSer)
					->orderBy('updated_at', 'desc')
					->first();
			}
		}


		 // Guardar la firma del cliente
		 $data_uri = $request->input('FirmaCliente');
		 if (empty($data_uri) || strpos($data_uri, 'base64,') === false) {
			Log::warning('RM firma cliente: payload de firma inválido', [
				'slug_servicio' => $id,
				'id_gener' => $idGener,
				'slug_firmas' => $slugFirmas,
			]);
			return redirect()->route('recibo.material', ['id' => $id])
				->with('error', 'La firma del cliente no fue capturada correctamente. Intente nuevamente.');
		 }
		 $encoded_image = explode(",", $data_uri)[1];
		 $decoded_image = base64_decode($encoded_image);
		 $nombreDeFirma = hash('md5', rand() . time());
		 Storage::disk('public')->put('FirmasClientesRegulares/' . $nombreDeFirma . '.png', $decoded_image);


		 // Guardar la firma en la base de datos
		 if ($firmacliente) {
			DB::table('firmas_servicio')
				->where('ID_Firmas', $firmacliente->ID_Firmas)
				->update([
					'FirmaCliente' => $nombreDeFirma,
					'NombreFuncionario' =>$request->input('NombreFuncionario'),
					'Cedula' => $request->input('CedulaFuncionario'),
					'Observaciones' => $request->input('Observacion'),
				]);

			$firmaclienteActualizada = DB::table('firmas_servicio')
				->where('ID_Firmas', $firmacliente->ID_Firmas)
				->first();
			Log::info('RM firma cliente guardada', [
				'slug_servicio' => $id,
				'id_firmas' => $firmacliente->ID_Firmas,
				'fk_solser' => $firmacliente->FK_SolSer ?? null,
				'fk_sgener' => $firmacliente->FK_SGener ?? null,
				'slug_firmas' => $firmacliente->SlugFirmas ?? null,
				'firma_cliente' => $firmaclienteActualizada->FirmaCliente ?? null,
				'archivo_existe' => Storage::disk('public')->exists('FirmasClientesRegulares/' . $nombreDeFirma . '.png'),
			]);
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

		 return redirect()->route('recibo.material', ['id' => $id]);
	 }

	 /**
	 * ingresa el numero de factura a la base de datos.
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

		 // Guardar la firma del cliente
		 $data_uri = $request->input('FirmaConductor');
		 $encoded_image = explode(",", $data_uri)[1];
		 $decoded_image = base64_decode($encoded_image);
		 $nombreDeFirma = hash('md5', rand() . time());
		 Storage::put('public/FirmaConductor/' . $nombreDeFirma . '.png', $decoded_image, 'public');

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
		 return redirect()->route('recibo.material', ['id' => $id]);
	 }

	 /**
	 * ingresa el numero de factura a la base de datos.
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



		 // Guardar la firma del cliente
		 $data_uri = $request->input('FirmaPDA');
		 $encoded_image = explode(",", $data_uri)[1];
		 $decoded_image = base64_decode($encoded_image);
		 $nombreDeFirma = hash('md5', rand() . time());
		 Storage::put('public/FirmaPDA/' . $nombreDeFirma . '.png', $decoded_image, 'public');

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
		 return redirect()->route('recibo.material', ['id' => $id]);
	 }

	 /**
	 * ingresa el numero de factura a la base de datos.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function rmtemplate($id, $slug)
	{
		// Priorizar coincidencia exacta por SlugFirmas (flujo de planta).
		$firmas = DB::table('firmas_servicio')
			->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('generadors' , 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
			->where('firmas_servicio.SlugFirmas', $id)
			->where('solicitud_servicios.SolSerSlug',$slug )
			->select('firmas_servicio.*', 'clientes.CliName', 'generadors.*')
			->first();

		// Si la fila por slug no tiene firma aún, intentar usar la fila del mismo servicio
		// que sí tenga firma (evita "Sin firma registrada" por desalineaciones puntuales).
		if ($firmas && (empty($firmas->FirmaCliente) || $firmas->FirmaCliente === '0')) {
			$firmasConFirma = DB::table('firmas_servicio')
				->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
				->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
				->join('generadors' , 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
				->where('solicitud_servicios.SolSerSlug', $slug)
				->whereNotNull('firmas_servicio.FirmaCliente')
				->where('firmas_servicio.FirmaCliente', '!=', '0')
				->orderBy('firmas_servicio.updated_at', 'desc')
				->select('firmas_servicio.*', 'clientes.CliName', 'generadors.*')
				->first();
			if ($firmasConFirma) {
				$firmas = $firmasConFirma;
			}
		}

		Log::info('RM wordtemplate firma seleccionada', [
			'slug_firmas_url' => $id,
			'slug_servicio' => $slug,
			'id_firmas' => $firmas->ID_Firmas ?? null,
			'fk_sgener' => $firmas->FK_SGener ?? null,
			'slug_firmas_db' => $firmas->SlugFirmas ?? null,
			'firma_cliente' => $firmas->FirmaCliente ?? null,
		]);

		if (!$firmas && (in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol, Permisos::SUPERVISOR))) {
    		$firmas = DB::table('firmas_servicio')
    			->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
    			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
    			->join('generadors' , 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
    			//->where('firmas_servicio.FK_SGener', $id)
    			->where('solicitud_servicios.SolSerSlug',$slug )
    			->select('firmas_servicio.*', 'clientes.CliName', 'generadors.ID_Gener', 'generadors.GenerNit', 'generadors.GenerName', 'generadors.GenerShortname', 'generadors.GenerCode', 'generadors.GenerType', 'generadors.GenerSlug', 'generadors.FK_GenerCli', 'generadors.GenerDelete')
    			->first();
		} elseif (!$firmas) {
		    	// Primero intentar buscar con el FK_SGener específico
		    	$firmas = DB::table('firmas_servicio')
    			->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
    			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
    			->join('generadors' , 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
    			->where('firmas_servicio.FK_SGener', $id)
    			->where('solicitud_servicios.SolSerSlug',$slug )
    			->select('firmas_servicio.*', 'clientes.CliName', 'generadors.*')
    			->first();

    		// Si no se encuentra, intentar buscar con FK_SGener = 0 (recepción en planta)
    		if (!$firmas) {
    			$firmas = DB::table('firmas_servicio')
    				->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
    				->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
    				->join('generadors' , 'generadors.ID_Gener', '=', 'firmas_servicio.FK_Gener')
    				->where('firmas_servicio.FK_SGener', 0)
    				->where('solicitud_servicios.SolSerSlug',$slug )
    				->select('firmas_servicio.*', 'clientes.CliName', 'generadors.*')
    				->first();
    		}
		}

		// Validar que se encontró el registro de firmas
		if (!$firmas) {
			// Intentar obtener la solicitud de servicio directamente por el slug para diagnosticar
			$solicitud = DB::table('solicitud_servicios')
				->where('SolSerSlug', $slug)
				->first();

			if (!$solicitud) {
				abort(404, 'No se encontró la solicitud de servicio con el slug proporcionado.');
			}

			// Verificar si existe alguna firma para esta solicitud
			$firmasExistentes = DB::table('firmas_servicio')
				->where('FK_SolSer', $solicitud->ID_SolSer)
				->get();

			if ($firmasExistentes->isEmpty()) {
				abort(404, 'No se encontró ningún registro de firmas para esta solicitud de servicio. Es posible que falte crear el registro en la tabla firmas_servicio.');
			}

			// Si hay firmas pero no coinciden con el FK_SGener, mostrar información de diagnóstico
			$firmasInfo = $firmasExistentes->map(function($f) {
				return "FK_SGener: {$f->FK_SGener}, FK_Gener: {$f->FK_Gener}, FK_SolSer: {$f->FK_SolSer}";
			})->implode(' | ');

			abort(404, "No se encontró una firma que coincida con FK_SGener={$id} o FK_SGener=0 y SolSerSlug={$slug}. Firmas existentes: {$firmasInfo}");
		}

		//return $firmas;
		$SolicitudServicio = DB::table('solicitud_servicios')
			->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
			->join('cargos', 'personals.FK_PersCargo', '=', 'ID_Carg')
			->select('solicitud_servicios.*','personals.PersFirstName','personals.PersLastName', 'personals.PersEmail', 'personals.PersCellphone', 'cargos.CargName')
			->where('solicitud_servicios.ID_SolSer', $firmas->FK_SolSer)
			->first();
		//return $SolicitudServicio;

		if (!$SolicitudServicio) {
			abort(404);
		}

		if($SolicitudServicio->SolSerTypeCollect === Null){

			$SolSerCollectAddress = $SolicitudServicio->SolSerCollectAddress;
			$SolSerConductor = $SolicitudServicio->SolSerConductor;

			//return $SolicitudServicio;

			$Programaciones = DB::table('progvehiculos')
				//->join('personals', 'personals.ID_Pers', '=', 'progvehiculos.FK_ProgAyudante')
				->where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
				->where('ProgVehDelete', 0)
				->select('*')
				->first();
			//return $Programaciones;

			// Precintos: usar de Programaciones cuando haya vehículo asignado
			if (!$Programaciones) {
				$precintosString = 'No Aplica, el cliente trae en su propio equipo';
			} elseif (!empty($Programaciones->ProgVehPrecintos)) {
				$val = is_array($Programaciones->ProgVehPrecintos) ? $Programaciones->ProgVehPrecintos : json_decode($Programaciones->ProgVehPrecintos, true);
				if (is_array($val) && count($val) > 0) {
					$precintosString = implode(', ', $val);
				} else {
					$precintosString = 'No se asignó precinto';
				}
			} else {
				$precintosString = 'No se asignó precinto';
			}


			$Cliente = DB::table('clientes')
				->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
				->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
				->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
				->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
				->first();

			if($firmas){
			$GenerResiduos = DB::table('solicitud_residuos')
				->distinct()
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
				->join('municipios', 'municipios.ID_Mun', '=', 'gener_sedes.FK_GSedeMun')
				->select('gener_sedes.GSedeName', 'residuos_geners.FK_SGener','generadors.ID_Gener', 'generadors.GenerName','gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress', 'gener_sedes.GSedeEmail', 'gener_sedes.GSedeCelular', 'municipios.MunName')
				//->where('generadors.ID_Gener', $firmas->FK_Gener)
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->get();
			}
			//return $GenerResiduos;

			$Residuosoriginal = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
				->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->join('requerimientos' , 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
				->join('tratamientos' , 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
				->join('sedes' , 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
				->join('clientes' , 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('solicitud_residuos.*','residuos_geners.FK_SGener', 'respels.*', 'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName', 'gener_sedes.FK_GSede', 'generadors.*')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				//->where('generadors.ID_Gener', $firmas->FK_Gener )
				// ->where('requerimientos.ofertado', 1)
				// ->where('forevaluation', 0)
				->get();
			//return $Residuosoriginal;

			$Residuos = $Residuosoriginal->map(function ($item) {
				$requerimientos = Requerimiento::with(['pretratamientosSelected', 'tarifa.rangos' => function($query){
					$query->orderBy('TarifaDesde');
				}])
				->where('ID_Req', $item->FK_SolResRequerimiento)
				// ->where('forevaluation', 0)
				->first();

				$rm = SolicitudResiduo::with('SolicitudServicio')->where('SolResSlug', $item->SolResSlug)->first(['SolResRM', 'FK_SolResSolSer']);

				$item->pretratamientosSelected = $requerimientos->pretratamientosSelected;
				$item->tarifa = $requerimientos->tarifa;
				if ($requerimientos->tarifa->TarifaSpecial === 1) {
					switch ($item->SolResTypeUnidad) {
						case 'Unidad':
							$tarifatipo = 'Unid';
							break;

						case 'Litros':
							$tarifatipo = 'Lt';
							break;

						default:
							$tarifatipo = 'Kg';
							break;
					}

					$tarifaResiduo = CTarifa::with('rangos')
						->where('FK_Cliente', $rm->SolicitudServicio->FK_SolSerCliente)
						->where('FK_Tratamiento', $requerimientos->FK_ReqTrata)
						->where('Tarifatipo', $tarifatipo)
						->first();

					if ($tarifaResiduo === null) {
						$item->ctarifa = null;
					}else{
						$item->ctarifa = $tarifaResiduo;
					}
				}else{
					$item->ctarifa = null;
				}
				$item->SolResRM2 = $rm->SolResRM;
				return $item;
			});

			$SolicitudServicio->Repetible = 0;

			/* se convierte el tipo de dato a aray mediante la consulta en el modelo de la columna SolSerRMs usando eloquent*/
			$rms = SolicitudServicio::where('SolSerSlug', $SolicitudServicio->SolSerSlug)->first('SolSerRMs');
			$SolicitudServicio->SolSerRMs = $rms->SolSerRMs;

			// return $Residuos;

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

			$SolicitudesServicioscount = DB::table('solicitud_servicios')
				->join('solicitud_residuos' , 'solicitud_residuos.FK_SolResSolSer', '=', 'solicitud_servicios.ID_SolSer')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
				->where('ID_SolSer', $SolicitudServicio->ID_SolSer)
				->select('solicitud_residuos.*', 'generadors.*')
				->get();

				$cantidadArreglos = $SolicitudesServicioscount->count();

				$totales = 0;

				foreach ($SolicitudesServicioscount as $servicio){

					$pesorecibido = $servicio->SolResKgRecibido;
					$totales = $totales + $pesorecibido;
				}

			if (in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol, Permisos::SolSer1)) {
				$tratamientos = Tratamiento::join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
				->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
				->select('*')
				->get();
			}else{
				$tratamientos = 'NoAutorizado';
			}
			//Buscar corrientes del residuo

				$PublicRespels = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->select('respels.ID_Respel', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->distinct()
				->get();

			$user = Auth::user();

			// Validación para encontrar la fecha de recepción en planta del servicio
		$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)->programacionesrecibidas()->first();
		if($fechaRecepcion){
			$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
		}else{
			$SolicitudServicio->recepcion = null;
		}

			//Generación de PDF
				$pdf = PDF::setPaper('letter', 'portrait')->loadView('solicitud-serv.rmtemplateplanta', compact(['SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente',  'SolSerConductor',  'Programaciones',  'totales', 'tratamientos', 'PublicRespels', 'precintosString', 'firmas', 'user']));
				$nombre = $firmas->SlugFirmas . '.pdf';
				$path = 'RecibosdeMaterial/' . sprintf("%0s", $nombre);

				Storage::disk('public')->makeDirectory('RecibosdeMaterial');
				Storage::disk('public')->put($path, $pdf->output());

				$pdfPath = storage_path('app/public/RecibosdeMaterial/' . $firmas->SlugFirmas . '.pdf');
			//Envio de documento al correo

			Mail::to(self::MAIL_RECIBO_MATERIAL_INTERNO)->send(new SolSerRM($pdf, $pdfPath, $Cliente, $GenerResiduos, $firmas));
			if (!empty($SolicitudServicio->PersEmail)) {
				Mail::to($SolicitudServicio->PersEmail)->send(new SolSerRM($pdf, $pdfPath, $Cliente, $GenerResiduos, $firmas));
			}

			return response($pdf->output(), 200, [
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => 'inline; filename="Recibo_Material_' . $SolicitudServicio->ID_SolSer . '.pdf"'
			]);
			//return view ('solicitud-serv.rmtemplate', compact('SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente',  'SolSerConductor',  'Programaciones', 'totales', 'tratamientos', 'PublicRespels', 'precintosString', 'firmas'));

		} else {

		$SolSerCollectAddress = $SolicitudServicio->SolSerCollectAddress;
		$SolSerConductor = $SolicitudServicio->SolSerConductor;

		$Programaciones = DB::table('progvehiculos')
			->join('personals', 'personals.ID_Pers', '=', 'progvehiculos.FK_ProgAyudante')
			->where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
			->where('ProgVehDelete', 0)
			->select('*')
			->first();
		//return $Programaciones;

		// Nombre del conductor para firma simulada en PDF (RM regular): priorizar programación de servicios
		$nombreConductor = 'No asignado';
		if ($Programaciones) {
			if (!empty($Programaciones->ProgVehNameConductorEXT)) {
				$nombreConductor = trim($Programaciones->ProgVehNameConductorEXT);
			} elseif (!empty($Programaciones->FK_ProgConductor)) {
				$pers = Personal::find($Programaciones->FK_ProgConductor);
				if ($pers) {
					$nombreConductor = trim(($pers->PersFirstName ?? '') . ' ' . ($pers->PersLastName ?? ''));
				}
			}
		}
		if ($nombreConductor === 'No asignado' && $SolicitudServicio->SolSerTipo == 'Interno' && is_numeric($SolicitudServicio->SolSerConductor)) {
			$pers = Personal::find($SolicitudServicio->SolSerConductor);
			if ($pers) {
				$nombreConductor = trim(($pers->PersFirstName ?? '') . ' ' . ($pers->PersLastName ?? ''));
			}
		} elseif ($nombreConductor === 'No asignado' && !empty($SolicitudServicio->SolSerConductor)) {
			$nombreConductor = trim((string) $SolicitudServicio->SolSerConductor);
		}

		// Precintos: usar de Programación cuando exista y tenga precintos (recolección)
		$Precintos = ProgramacionVehiculo::where('FK_ProgServi', $SolicitudServicio->ID_SolSer)
			->where('ProgVehDelete', 0)
			->select('ProgVehPrecintos')
			->first();

		if ($Precintos && !empty($Precintos->ProgVehPrecintos)) {
			$val = is_array($Precintos->ProgVehPrecintos) ? $Precintos->ProgVehPrecintos : json_decode($Precintos->ProgVehPrecintos, true);
			if (is_array($val) && count($val) > 0) {
				$precintosString = implode(', ', $val);
			} else {
				$precintosString = 'No se asignó precinto';
			}
		} else {
			$precintosString = 'No se asignó precinto';
		}


		$Cliente = DB::table('clientes')
			->join('sedes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->join('municipios', 'sedes.FK_SedeMun', '=', 'municipios.ID_Mun')
			->select('clientes.CliNit', 'clientes.CliName', 'sedes.SedeAddress', 'municipios.MunName')
			->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
			->first();
		//return $firmas;
		if($firmas){
			$GenerResiduos = DB::table('solicitud_residuos')
			->distinct()
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			->join('firmas_servicio', 'firmas_servicio.FK_Gener', '=', 'generadors.ID_Gener')
			->join('municipios', 'municipios.ID_Mun', '=', 'gener_sedes.FK_GSedeMun')
			->select('gener_sedes.GSedeName', 'residuos_geners.FK_SGener','generadors.ID_Gener', 'generadors.GenerName','gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress', 'gener_sedes.GSedeEmail', 'gener_sedes.GSedeCelular', 'municipios.MunName')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->where('generadors.ID_Gener', $firmas->FK_Gener)
			->where('residuos_geners.FK_SGener',$firmas->FK_SGener)
			->get();

		}

		$Residuosoriginal = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->join('requerimientos' , 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
			->join('tratamientos' , 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
			->join('sedes' , 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
			->join('clientes' , 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
			->select('solicitud_residuos.*','residuos_geners.FK_SGener', 'respels.*', 'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName', 'gener_sedes.FK_GSede', 'generadors.*')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->where('generadors.ID_Gener', $firmas->FK_Gener )
			->where('residuos_geners.FK_SGener', $firmas->FK_SGener)
			->get();

		// Fallback: si no hay residuos con el filtro de generador, probar sin filtro (desajuste firmas↔residuos en BD)
		if ($Residuosoriginal->isEmpty()) {
			$Residuosoriginal = DB::table('solicitud_residuos')
				->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
				->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
				->join('respels', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->join('requerimientos', 'solicitud_residuos.FK_SolResRequerimiento', '=', 'requerimientos.ID_Req')
				->join('tratamientos', 'requerimientos.FK_ReqTrata', '=', 'tratamientos.ID_Trat')
				->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('solicitud_residuos.*', 'residuos_geners.FK_SGener', 'respels.*', 'requerimientos.ID_Req', 'tratamientos.TratName', 'tratamientos.ID_Trat', 'clientes.CliShortName', 'gener_sedes.FK_GSede', 'generadors.*')
				->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
				->get();
			if (!$Residuosoriginal->isEmpty()) {
				Log::warning('RM PDF: Residuos obtenidos sin filtro de generador. Revisar firmas_servicio (FK_Gener=' . ($firmas->FK_Gener ?? 'null') . ', FK_SGener=' . ($firmas->FK_SGener ?? 'null') . ') vs residuos del servicio ' . $SolicitudServicio->ID_SolSer);
				$GenerResiduos = DB::table('solicitud_residuos')
					->distinct()
					->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
					->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
					->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
					->join('municipios', 'municipios.ID_Mun', '=', 'gener_sedes.FK_GSedeMun')
					->select('gener_sedes.GSedeName', 'residuos_geners.FK_SGener', 'generadors.ID_Gener', 'generadors.GenerName', 'gener_sedes.GSedeSlug', 'gener_sedes.GSedeAddress', 'gener_sedes.GSedeEmail', 'gener_sedes.GSedeCelular', 'municipios.MunName')
					->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
					->get();
			}
		}

		//return $Residuosoriginal;


		$Residuos = $Residuosoriginal->map(function ($item) {
			$requerimientos = Requerimiento::with(['pretratamientosSelected', 'tarifa.rangos' => function($query){
				$query->orderBy('TarifaDesde');
			}])
			->where('ID_Req', $item->FK_SolResRequerimiento)
			// ->where('forevaluation', 0)
			->first();

			$rm = SolicitudResiduo::with('SolicitudServicio')->where('SolResSlug', $item->SolResSlug)->first(['SolResRM', 'FK_SolResSolSer']);

	        $item->pretratamientosSelected = $requerimientos->pretratamientosSelected;
	        $item->tarifa = $requerimientos->tarifa;
			if ($requerimientos->tarifa->TarifaSpecial === 1) {
				switch ($item->SolResTypeUnidad) {
					case 'Unidad':
						$tarifatipo = 'Unid';
						break;

					case 'Litros':
						$tarifatipo = 'Lt';
						break;

					default:
						$tarifatipo = 'Kg';
						break;
				}

				$tarifaResiduo = CTarifa::with('rangos')
					->where('FK_Cliente', $rm->SolicitudServicio->FK_SolSerCliente)
					->where('FK_Tratamiento', $requerimientos->FK_ReqTrata)
					->where('Tarifatipo', $tarifatipo)
					->first();

				if ($tarifaResiduo === null) {
					$item->ctarifa = null;
				}else{
					$item->ctarifa = $tarifaResiduo;
				}
			}else{
				$item->ctarifa = null;
			}
	        $item->SolResRM2 = $rm->SolResRM;
		  	return $item;
		});


		$SolicitudServicio->Repetible = 0;

		/* se convierte el tipo de dato a aray mediante la consulta en el modelo de la columna SolSerRMs usando eloquent*/
		$rms = SolicitudServicio::where('SolSerSlug', $SolicitudServicio->SolSerSlug)->first('SolSerRMs');
		$SolicitudServicio->SolSerRMs = $rms->SolSerRMs;

		// return $Residuos;

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

		  $SolicitudesServicioscount = DB::table('solicitud_servicios')
			  ->join('solicitud_residuos' , 'solicitud_residuos.FK_SolResSolSer', '=', 'solicitud_servicios.ID_SolSer')
			  ->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			  ->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
			  ->join('generadors' , 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
			  ->where('ID_SolSer', $SolicitudServicio->ID_SolSer)
			  ->where('gener_sedes.ID_GSede', $firmas->FK_SGener)
			  ->select('solicitud_residuos.*', 'generadors.*')
			  ->get();

			  $totales = 0;
			  foreach ($SolicitudesServicioscount as $servicio) {
				$totales += $servicio->SolResKgRecibido ?? 0;
			  }
			  // Si totales sigue en 0 (desajuste firmas/generador), sumar desde los residuos que sí tenemos
			  if ($totales == 0 && $Residuosoriginal->isNotEmpty()) {
				$totales = $Residuosoriginal->sum(function ($r) {
					$recibido = (float) ($r->SolResKgRecibido ?? 0);
					if ($recibido <= 0 && isset($r->SolResKgConciliado)) {
						$recibido = (float) $r->SolResKgConciliado;
					}
					return $recibido;
				});
				if ($totales > 0) {
					Log::warning('RM PDF: totales calculado desde Residuos (firmas no coincidían). Servicio ' . $SolicitudServicio->ID_SolSer);
				}
			  }

		if (in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol, Permisos::SolSer1)) {
			$tratamientos = Tratamiento::join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
			->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
			->select('*')
			->get();
		}else{
			$tratamientos = 'NoAutorizado';
		}
		//Buscar corrientes del residuo

			$PublicRespels = DB::table('solicitud_residuos')
			->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
			->join('respels' , 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
			->select('respels.ID_Respel', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741')
			->where('solicitud_residuos.FK_SolResSolSer', $SolicitudServicio->ID_SolSer)
			->distinct()
			->get();

			$user = Auth::user();

		// Validación para encontrar la fecha de recepción en planta del servicio
		$fechaRecepcion = SolicitudServicio::find($SolicitudServicio->ID_SolSer)->programacionesrecibidas()->first();
		if($fechaRecepcion){
			$SolicitudServicio->recepcion = $fechaRecepcion->ProgVehSalida;
		}else{
			$SolicitudServicio->recepcion = null;
		}

		//	return $user;

		//Generación de PDF
		try {
			// Validar que los datos necesarios estén presentes
			if (empty($Residuos) || $Residuos->isEmpty()) {
				Log::error('Error generando PDF RM: No hay residuos para el servicio', [
					'servicio_id' => $SolicitudServicio->ID_SolSer ?? null,
					'firmas_id' => $firmas->ID_Firmas ?? null
				]);
				throw new \Exception('No hay residuos para generar el PDF del RM');
			}

			$pdf = PDF::setPaper('letter', 'portrait')->loadView('solicitud-serv.rmtemplate', compact(['SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente',  'SolSerConductor',  'Programaciones',  'totales', 'tratamientos', 'PublicRespels', 'precintosString', 'firmas', 'user', 'nombreConductor']));
            $nombre = $firmas->SlugFirmas . '.pdf';
			$path = 'RecibosdeMaterial/' . sprintf("%0s", $nombre);
        //return $GenerResiduos;
		   Storage::disk('public')->makeDirectory('RecibosdeMaterial');
		   Storage::disk('public')->put($path, $pdf->output());
		} catch (\Exception $e) {
			Log::error('Error generando PDF RM', [
				'servicio_id' => $SolicitudServicio->ID_SolSer ?? null,
				'firmas_id' => $firmas->ID_Firmas ?? null,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
				'residuos_count' => $Residuos->count() ?? 0,
				'gener_residuos_count' => $GenerResiduos->count() ?? 0
			]);
			throw $e;
		}


			$pdfPath = storage_path('app/public/RecibosdeMaterial/' . $firmas->SlugFirmas . '.pdf');
		//Envio de documento al correo

		$hostSmtp = (string) env('MAIL_HOST');
		$usuarioSmtp = (string) env('MAIL_USERNAME');
		$relaySinAuth = empty($usuarioSmtp) && str_contains($hostSmtp, 'mail.protection.outlook.com');

		if ($relaySinAuth) {
			try {
				Mail::to(self::MAIL_RECIBO_MATERIAL_INTERNO)->send(new SolSerRM($pdf, $pdfPath, $Cliente, $GenerResiduos, $firmas));
				if (!empty($SolicitudServicio->PersEmail)) {
					Mail::to($SolicitudServicio->PersEmail)->send(new SolSerRM($pdf, $pdfPath, $Cliente, $GenerResiduos, $firmas));
				}
			} catch (\Throwable $e) {
				Log::warning('Fallo envio RM (relay sin auth)', [
					'servicio_id' => $SolicitudServicio->ID_SolSer,
					'error' => $e->getMessage(),
				]);
			}
		} else {
			try {
				Mail::to(self::MAIL_RECIBO_MATERIAL_INTERNO)->send(new SolSerRM($pdf, $pdfPath, $Cliente, $GenerResiduos, $firmas));
				if (!empty($SolicitudServicio->PersEmail)) {
					Mail::to($SolicitudServicio->PersEmail)->send(new SolSerRM($pdf, $pdfPath, $Cliente, $GenerResiduos, $firmas));
				}
			} catch (\Throwable $e) {
				Log::error('Fallo envio RM por SMTP', [
					'servicio_id' => $SolicitudServicio->ID_SolSer,
					'error' => $e->getMessage(),
				]);
			}
		}

		return response($pdf->output(), 200, [
			'Content-Type' => 'application/pdf',
			'Content-Disposition' => 'inline; filename="Recibo_Material_' . $SolicitudServicio->ID_SolSer . '.pdf"'
		]);
		//return view ('solicitud-serv.rmtemplate', compact('SolicitudServicio','Residuos', 'GenerResiduos', 'Cliente',  'SolSerConductor',  'Programaciones', 'totales', 'tratamientos', 'PublicRespels', 'precintosString', 'firmas'));
		}
	}
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

	 public function NuevoRespel(Request $request, $id)
	 {
		$SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();

		$idGener = $request->input('SGenerador');

		$Gener = DB::table('generadors')
			->join('gener_sedes', 'gener_sedes.FK_GSede', '=', 'generadors.ID_Gener')
			->where('generadors.GenerName', $idGener)
			->select('*')
			->first();

		$respels = ResiduosGener::select('ID_SGenerRes')
			->where('FK_SGener', $Gener->ID_Gener)
			->first();

		$Generadors = DB::table('generadors')
        ->join('gener_sedes', 'gener_sedes.FK_GSede', '=', 'generadors.ID_Gener')
		->join('sedes', 'sedes.ID_Sede', '=', 'generadors.FK_GenerCli')
        ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
		->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
		->get();

		//return $Gener;

		//Enlazar el residuo a generador
		$numeroDeGeneradores = count($Generadors);

			foreach($Generadors as $Generador){
				$RespelSedeGener = new ResiduosGener;
				$RespelSedeGener->FK_SGener = $Generador->ID_GSede;
				$RespelSedeGener->FK_Respel = $request->input('residuo-select');
				$RespelSedeGener->SlugSGenerRes = hash('sha256', rand().time().$RespelSedeGener->FK_SGener);
				$RespelSedeGener->DeleteSGenerRes = 0;
				$RespelSedeGener->save();
			}

					$SolicitudResiduo = new SolicitudResiduo();
					$SolicitudResiduo->SolResKgEnviado = 0;
					$SolicitudResiduo->SolResKgRecibido = $request->input('SolResCantiUnidad');
					$SolicitudResiduo->SolResKgConciliado = 0;
					$SolicitudResiduo->SolResKgTratado = 0;
					$SolicitudResiduo->SolResDelete = 0;
					$SolicitudResiduo->SolResSlug = hash('sha256', rand().time().$SolicitudResiduo->SolResKgEnviado);
					$SolicitudResiduo->FK_SolResSolSer = $SolicitudServicio->ID_SolSer;
					switch ($request->input('SolResEmbalaje')) {
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
					$SolicitudResiduo->SolResAlto = "";
					$SolicitudResiduo->SolResAncho = "";
					$SolicitudResiduo->SolResProfundo = "";
					$SolicitudResiduo->SolResFotoDescargue_Pesaje = "";
					$SolicitudResiduo->SolResFotoTratamiento = "";
					$SolicitudResiduo->SolResVideoDescargue_Pesaje = "";
					$SolicitudResiduo->SolResVideoTratamiento = "";
					$SolicitudResiduo->SolResAuditoria = "";
					$SolicitudResiduo->SolResDevolucion = "";
					$SolicitudResiduo->FK_SolResRg = ResiduosGener::select('ID_SGenerRes')->where('FK_SGener', $Gener->ID_GSede)->latest('ID_SGenerRes')->first()->ID_SGenerRes;
					/*validar el residuo para saber el tratamiento*/
					$respelref = ResiduosGener::select('FK_Respel')->where('FK_SGener', $Gener->ID_GSede)->first()->FK_Respel;

					$requerimientoparacopiar = Requerimiento::with(['pretratamientosSelected'])
					->where('FK_ReqRespel', $respelref)
					->where('ofertado', 1)
					->where('forevaluation', 1)
					->first();

					//return $requerimientoparacopiar;

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



        return redirect()->route('recibo.material', ['id' => $SolicitudServicio->SolSerSlug]);
	 }

	  public function duplicarpesos(Request $request, $id){

		$solicitud = DB::table('solicitud_servicios')
			->where('SolSerSlug', $id)
			->select('ID_SolSer')
			->first();

		$residuos = DB::table('solicitud_residuos')
			->where('FK_SolResSolSer', $solicitud->ID_SolSer)
			->select('SolResKgEnviado', 'ID_SolRes')
			->get();

		//return $residuos;

		foreach ($residuos as $residuo) {

			$SolResKgRecibido = $residuo->SolResKgEnviado;
			DB::table('solicitud_residuos')
			->where('FK_SolResSolSer', $solicitud->ID_SolSer)
			->where('ID_SolRes', $residuo->ID_SolRes)
			->update([
				'SolResKgRecibido' => $SolResKgRecibido
			]);
		}

		return $this->recibomaterial($id);

	 }
}
