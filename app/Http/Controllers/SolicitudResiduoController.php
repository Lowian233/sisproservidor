<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CantConciliadaUpdated;
use App\SolicitudResiduo;
use App\Http\Requests\SolResUpdateRequest;
use App\audit;
use App\Respel;
use App\Recurso;
use App\ResiduosGener;
use App\SolicitudServicio;
use App\Requerimiento;
use App\ProgramacionVehiculo;
use App\Cliente;
use App\Personal;
use App\Docdato;
use App\Tratamiento;
use App\Generador;
use App\Certificado;
// use App\Incineracion; // Comentado: incineraciones/inventario en desarrollo
use App\Permisos;
use App\Http\Controllers\userController;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ServiciosExport;

class SolicitudResiduoController extends Controller
{
	/** Buzón interno único para notificaciones de residuos / solicitud de residuos. */
	private const MAIL_RESIDUOS_INTERNO = 'residuos@prosarc.com.co';

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//comparte show con recursos
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
				$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
				if (!$SolRes) {
					abort(404);
				}
				$SolSer = SolicitudServicio::where('ID_SolSer', $SolRes->FK_SolResSolSer)->first();
			$RespelSgener = ResiduosGener::where('ID_SGenerRes', $SolRes->FK_SolResRg)->first();
			$Respel = DB::table('respels')
				->join('residuos_geners', 'respels.ID_Respel', '=', 'residuos_geners.FK_Respel')
				->join('solicitud_residuos', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
				->select('respels.RespelSlug', 'respels.RespelName', 'respels.ID_Respel')
				->where('residuos_geners.ID_SGenerRes', $SolRes->FK_SolResRg)
				->first();
			$Requerimientos = DB::table('requerimientos')
				->where('requerimientos.FK_ReqRespel', $Respel->ID_Respel)
				->where('requerimientos.ofertado', '=', 1)
				->first();
			//return $Requerimientos;
			if($SolSer->SolSerStatus === 'Programado' || $SolSer->SolSerStatus === 'Completado' || $SolSer->SolSerStatus === 'Conciliado' || $SolSer->SolSerStatus === 'Tratado'  || $SolSer->SolSerStatus === 'Certificacion'){
				abort(403);
			}
			$KGenviados = DB::table('solicitud_residuos')
				->select('SolResKgEnviado')
				->where('FK_SolResSolSer', $SolSer->ID_SolSer)
				->where('ID_SolRes', '<>', $SolRes->ID_SolRes)
				->get();
			$totalenviado = 0;
			foreach ($KGenviados as $KGenviado) {
				$totalenviado = $totalenviado + $KGenviado->SolResKgEnviado;
			}
			//return view('solicitud-resid.edit', compact('SolRes', 'Respel', 'RespelSgener', 'SolSer', 'Programacion', 'totalenviado', 'Requerimientos'));
			return view('solicitud-resid.edit', compact('SolRes', 'Respel', 'RespelSgener', 'SolSer', 'totalenviado', 'Requerimientos'));
		}else{
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

	public function updateSolRes(Request $request, $id){
		// return $request->input('SolResRM');

		$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
		if (!$SolRes) {
			abort(404);
		}
		$SolSer = SolicitudServicio::where('ID_SolSer', $SolRes->FK_SolResSolSer)->first();

		// Actualizar cantidad de embalaje si viene desde el modal del "lapicito"
		if ($request->has('SolResCantEmbalaje') && !$request->has('SolResKg')) {
			$SolRes->SolResCantEmbalaje = $request->input('SolResCantEmbalaje');
			$SolRes->save();

			$log = new audit();
			$log->AuditTabla = "solicitud_residuos";
			$log->AuditType = "Modificado Cant. Embalaje";
			$log->AuditRegistro = $SolRes->ID_SolRes;
			$log->AuditUser = Auth::user()->email;
			$log->Auditlog = json_encode($request->only('SolResCantEmbalaje'));
			$log->save();

			$Cliente = Cliente::where('ID_Cli', $SolSer->FK_SolSerCliente)->first();
			$id = $SolSer->SolSerSlug;

			// 1) Si viene desde RM, respeta el origen por Referer (Express vs Regular)
			if ($request->has('from_recibo') && $request->input('from_recibo') == 'true') {
				if (in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol2, Permisos::RECIBOMATERIAL)) {
					$referer = $request->headers->get('referer');
					if ($referer && strpos($referer, '/serviciosexpress/') !== false && !empty($id)) {
						return redirect()->to("/serviciosexpress/{$id}/recibomaterial");
					}
					return redirect()->route('recibo.material', ['id' => $id]);
				}
			}

			// 2) Flujo normal sin from_recibo
			if ($Cliente && $Cliente->CliCategoria == 'ClientePrepago') {
				return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $id]);
			}

			if (in_array(Auth::user()->UsRol, Permisos::SUPERVISOR) || in_array(Auth::user()->UsRol, Permisos::SUPERVISOR)) {
				return redirect()->route('informe');
			}

			return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
		}


		$Cliente = Cliente::where('ID_Cli', $SolSer->FK_SolSerCliente)->first();

        if(!$Cliente) {
            $Cliente = DB::table('clientes_express')
                ->where('id', $SolSer->FK_Cliente_Express)
                ->first();

            if ($Cliente) {
                $Cliente->CliCategoria = 'ClientePrepago';
            }
        }

		if ($Cliente->CliCategoria == 'ClientePrepago') {
			$Validate = $request->validate([
				'SolResKg'  => 'required|numeric|nullable',
				'SolResCantiUnidadRecibida'  => 'numeric|nullable',
			]);
			switch($SolSer->SolSerStatus){
				case 'Notificado':
				case 'Programado':
				case 'Notificado':
					if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
						$SolRes->SolResCantiUnidadRecibida = $request->input('SolResCantiUnidadRecibida');
						$SolRes->SolResCantiUnidadConciliada = $request->input('SolResCantiUnidadRecibida');
					}
					$SolRes->SolResKgRecibido = $request->input('SolResKg');
					$SolRes->SolResKgConciliado = $request->input('SolResKg');
					$SolRes->SolResRM = $request->input('SolResRM');
					break;
				case 'No Conciliado':
				case 'Completado':
				case 'Recepcionado':
					if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
						$SolRes->SolResCantiUnidadConciliada = $request->input('SolResCantiUnidadConciliada');
						$SolRes->SolResKgConciliado = $request->input('SolResKg');
					}else{
						$SolRes->SolResKgConciliado = $request->input('SolResKg');
					}
					$SolRes->SolResRM = $request->input('SolResRM');
					break;
				case 'Conciliado':
				case 'Certificacion':
					if( $request->input('ValorConciliado') == NULL){
						if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
							$SolRes->SolResCantiUnidadTratada = $request->input('SolResCantiUnidadTratada');
							$SolRes->SolResKgTratado = $request->input('SolResKg');
						}else{
							$SolRes->SolResKgTratado = $request->input('SolResKg');
						}
					}else{
						$SolRes->SolResKgTratado = $request->input('ValorConciliado');
					}
					$SolRes->SolResRM = $request->input('SolResRM');
					// break;
				default:
					abort(500);
					break;
			}
			$SolRes->save();

			if(isset($request['SupportPay'])){
				if($SolSer->SolSerSupport <> null && file_exists(public_path().'/img/SupportPay/'.$SolSer->SolSerSupport)){
					unlink(public_path().'/img/SupportPay/'.$SolSer->SolSerSupport);
				}
				$fileSupport = $request['SupportPay'];
				$nameSupport = hash('sha256', rand().time().$fileSupport->getClientOriginalName()).'.pdf';
				$fileSupport->move(public_path().'\img\SupportPay/',$nameSupport);
				$SolSer->SolSerSupport = $nameSupport;
				$SolSer->save();
			}

			$log = new audit();
			$log->AuditTabla="solicitud_residuos";
			$log->AuditType="Modificado EXPRESS";
			$log->AuditRegistro=$SolRes->ID_SolRes;
			$log->AuditUser=Auth::user()->email;
			$log->Auditlog=json_encode($request->all());
			$log->save();

			$id = $SolSer->SolSerSlug;
			// Si el servicio ya está conciliado o en certificación, vuelve siempre al show
if ($SolSer->SolSerStatus === 'Conciliado' || $SolSer->SolSerStatus === 'Certificacion') {
    return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $id]);
}

// Para otros estados, si viene desde RM y el usuario tiene rol de ReciboMaterial, vuelve al RM Express
if ($request->has('from_recibo') && $request->input('from_recibo') == 'true') {
    if ((in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol2, Permisos::RECIBOMATERIAL)) && !empty($id)) {
        return redirect()->to("/serviciosexpress/{$id}/recibomaterial");
    }
}

// En cualquier otro caso, regresa al show
return redirect()->route('serviciosexpress.show', ['serviciosexpress' => $id]);
		} else {
			$Validate = $request->validate([
				'SolResKg'  => 'required|numeric|max:50000|nullable',
				'SolResCantiUnidadRecibida'  => 'numeric|max:50000|nullable',
			]);
			switch($SolSer->SolSerStatus){
				case 'Notificado':
				case 'Programado':
				case 'Notificado':
					if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
						$SolRes->SolResCantiUnidadRecibida = $request->input('SolResCantiUnidadRecibida');
						$SolRes->SolResCantiUnidadConciliada = $request->input('SolResCantiUnidadRecibida');
					}
					$SolRes->SolResKgRecibido = $request->input('SolResKg');
					$SolRes->SolResKgConciliado = $request->input('SolResKg');
					$SolRes->SolResRM = $request->input('SolResRM');
					break;
				case 'No Conciliado':
				case 'Completado':
				case 'Recepcionado':
					if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
						$SolRes->SolResCantiUnidadConciliada = $request->input('SolResCantiUnidadConciliada');
						$SolRes->SolResKgConciliado = $request->input('SolResKg');
					}else{
						$SolRes->SolResKgConciliado = $request->input('SolResKg');
					}
					$SolRes->SolResRM = $request->input('SolResRM');
					break;
				case 'Conciliado':
				case 'Certificacion':
					if( $request->input('ValorConciliado') == NULL){
						if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
							$SolRes->SolResCantiUnidadTratada = $request->input('SolResCantiUnidadTratada');
							$SolRes->SolResKgTratado = $request->input('SolResKg');
						}else{
							$SolRes->SolResKgTratado = $request->input('SolResKg');
						}
					}else{
						$SolRes->SolResKgTratado = $request->input('ValorConciliado');
					}
					$SolRes->SolResRM = $request->input('SolResRM');
					break;
				default:
					abort(500);
					break;
			}
			$SolRes->save();

			// Comentado: incineraciones/inventario en desarrollo - descomentar cuando esté listo
			// if(in_array(Auth::user()->UsRol, Permisos::SUPERVISOR) || in_array(Auth::user()->UsRol2, Permisos::SUPERVISOR)){
			// 	$incineraciones = SolicitudResiduo::where('SolResSlug', $id)->first();
			// 	$incinerar = Incineracion::where('FK_SolRes', $incineraciones->ID_SolRes)->first();
			// 	$ejecutado = intval($request->input('SolResKg'));
			// 	$porcentaje = ($incinerar->Cantidadprog * 100) / $ejecutado;
			// 	$incinerar->CantidadEje = $request->input('SolResKg');
			// 	$incinerar->EjecutadovsProgramado = $porcentaje;
			// 	$incinerar->save();
			// }

			if(isset($request['SupportPay'])){
				if($SolSer->SolSerSupport <> null && file_exists(public_path().'/img/SupportPay/'.$SolSer->SolSerSupport)){
					unlink(public_path().'/img/SupportPay/'.$SolSer->SolSerSupport);
				}
				$fileSupport = $request['SupportPay'];
				$nameSupport = hash('sha256', rand().time().$fileSupport->getClientOriginalName()).'.pdf';
				$fileSupport->move(public_path().'\img\SupportPay/',$nameSupport);
				$SolSer->SolSerSupport = $nameSupport;
				$SolSer->save();
			}

			$log = new audit();
			$log->AuditTabla="solicitud_residuos";
			$log->AuditType="Modificado";
			$log->AuditRegistro=$SolRes->ID_SolRes;
			$log->AuditUser=Auth::user()->email;
			$log->Auditlog=json_encode($request->all());
			$log->save();

		$id = $SolSer->SolSerSlug;

		// Si viene desde RM, vuelve al RM Regular
		if ($request->has('from_recibo') && $request->input('from_recibo') == 'true') {
			if (in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol2, Permisos::RECIBOMATERIAL)) {
				return redirect()->route('recibo.material', ['id' => $id]);
			}
		}

		// Flujo normal (desde modal de conciliación)
		if(in_array(Auth::user()->UsRol, Permisos::SUPERVISOR) || in_array(Auth::user()->UsRol, Permisos::SUPERVISOR)) {
			return redirect()->route('informe');
		}
		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	public function updateSolResPrice(Request $request, $id){
		$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
		if (!$SolRes) {
			abort(404);
		}
		$SolSer = SolicitudServicio::where('ID_SolSer', $SolRes->FK_SolResSolSer)->first();

		$Validate = $request->validate([
			'SolResPrecio'  => 'required|numeric|nullable',
		]);
		$SolRes->SolResPrecio = $request->input('SolResPrecio');
		$SolRes->SolResTypePrecio = 3;
		$SolRes->save();

		$log = new audit();
		$log->AuditTabla="solicitud_residuos";
		$log->AuditType="Modificado el precio";
		$log->AuditRegistro=$SolRes->ID_SolRes;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

		$idServicio = $SolSer->SolSerSlug;

		return redirect()->route('solicitud-servicio.show', compact('idServicio'));
	}

	public function update(SolResUpdateRequest $request, $id)
	{
		// return $request;
		$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
		if (!$SolRes) {
			abort(404);
		}
		$Respel = Respel::select('ID_Respel')->where('RespelSlug', $request->input('FK_SolResSolSer'))->first();

		$SolRes->SolResTypeUnidad = $request->input('SolResTypeUnidad');
		$SolRes->SolResCantiUnidad = $request->input('SolResCantiUnidad');
		$SolRes->SolResKgEnviado = $request->input('SolResKgEnviado');
		$SolRes->SolResAlto = $request->input('SolResAlto');
		$SolRes->SolResAncho = $request->input('SolResAncho');
		$SolRes->SolResProfundo = $request->input('SolResProfundo');
		/*se verifica el requerimiento actualmente ofertado para el residuo*/
		$respelgener= ResiduosGener::find($SolRes->FK_SolResRg);

		$requerimientoOfertado = Requerimiento::with(['pretratamientosSelected'])
	        ->where('FK_ReqRespel', '=', $respelgener->FK_Respel)
	        ->where('ofertado', '=', 1)
	        ->first();
		if ($requerimientoOfertado->ReqFotoDescargue==0) {
			$SolRes->SolResFotoDescargue_Pesaje = 0;
		}else{
			$SolRes->SolResFotoDescargue_Pesaje = $request->input('SolResFotoDescargue_Pesaje');
		}

		if ($requerimientoOfertado->ReqFotoDestruccion==0) {
			$SolRes->SolResFotoTratamiento = 0;
		}else{
			$SolRes->SolResFotoTratamiento = $request->input('SolResFotoTratamiento');
		}

		if ($requerimientoOfertado->ReqVideoDescargue==0) {
			$SolRes->SolResVideoDescargue_Pesaje = 0;
		}else{
			$SolRes->SolResVideoDescargue_Pesaje = $request->input('SolResVideoDescargue_Pesaje');
		}

		if ($requerimientoOfertado->ReqVideoDestruccion==0) {
			$SolRes->SolResVideoTratamiento = 0;
		}else{
			$SolRes->SolResVideoTratamiento = $request->input('SolResVideoTratamiento');
		}

		if ($requerimientoOfertado->ReqDevolucion==0) {
			$SolRes->SolResDevolucion = 0;
		}else{
			$SolRes->SolResDevolucion = $request->input('SolResDevolucion');
		}

		if ($requerimientoOfertado->ReqAuditoria==0) {
			$SolRes->SolResAuditoria = 0;
		}else{
			$SolRes->SolResAuditoria = $request->input('SolResAuditoria');
		}
		// $SolRes->SolResFotoDescargue_Pesaje = $request->input('SolResFotoDescargue_Pesaje');
		// $SolRes->SolResFotoTratamiento = $request->input('SolResFotoTratamiento');
		// $SolRes->SolResVideoDescargue_Pesaje = $request->input('SolResVideoDescargue_Pesaje');
		// $SolRes->SolResVideoTratamiento = $request->input('SolResVideoTratamiento');
		// $SolRes->SolResDevolucion = $request->input('SolResDevolucion');
		// $SolRes->SolResAuditoria = $request->input('SolResAuditoria');
		$SolRes->SolResTypeUnidad = $request->input('SolResTypeUnidad');

		switch ($request->input('SolResEmbalaje')) {
			case 99:
				$SolRes->SolResEmbalaje = "Sacos/Bolsas";
				break;
			case 98:
				$SolRes->SolResEmbalaje = "Bidones Pequeños";
				break;
			case 97:
				$SolRes->SolResEmbalaje = "Bidones Grandes";
				break;
			case 96:
				$SolRes->SolResEmbalaje = "Estibas";
				break;
			case 95:
				$SolRes->SolResEmbalaje = "Garrafones/Jerricanes";
				break;
			case 94:
				$SolRes->SolResEmbalaje = "Cajas";
				break;
			case 93:
				$SolRes->SolResEmbalaje = "Cuñetes";
				break;
			case 92:
				$SolRes->SolResEmbalaje = "Big Bags";
				break;
			case 91:
				$SolRes->SolResEmbalaje = "Isotanques";
				break;
			case 90:
				$SolRes->SolResEmbalaje = "Tachos";
				break;
			case 89:
				$SolRes->SolResEmbalaje = "Embalajes Compuestos";
				break;
			case 88:
				$SolRes->SolResEmbalaje = "Granel";
				break;
			case 87:
				$SolRes->SolResEmbalaje = "Canecas 55 gal.";
				break;
			case 86:
				$SolRes->SolResEmbalaje = "Canecas 05 gal.";
				break;
			default:
				abort(500);
		}
		$SolRes->SolResCantEmbalaje = $request->input('SolResCantEmbalaje');
		$SolRes->save();

		$log = new audit();
		$log->AuditTabla="solicitud_residuos";
		$log->AuditType="Modificado";
		$log->AuditRegistro=$SolRes->ID_SolRes;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

		return redirect()->route('recurso.show', ['recurso' => $id]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
		if (!$SolRes) {
			abort(404);
		}
		$Recursos = Recurso::where('FK_RecSolRes', $SolRes->ID_SolRes)->get();
		$SolSer = SolicitudServicio::where('ID_SolSer', $SolRes->FK_SolResSolSer)->first();

		$log = new audit();
		$log->AuditTabla="solicitud_residuos";
		$log->AuditType="Eliminado";
		$log->AuditRegistro=$SolRes->ID_SolRes;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=$SolRes->SolResDelete;
		$log->save();

		if(!is_null($Recursos) && count($Recursos) > 0){
			foreach($Recursos as $Recurso){
				unlink(public_path("img/Recursos/$Recurso->RecSrc")."/$Recurso->RecRmSrc");
			}
			rmdir(public_path("img/Recursos/").$Recursos[0]->RecSrc);
		}

		$datos = Docdato::where('FK_DatoSolRes', $SolRes->ID_SolRes)->get();

		if(!is_null($datos)){
			foreach($datos as $dato){
				Docdato::destroy($dato->ID_Dato);
			}
		}

		SolicitudResiduo::destroy($SolRes->ID_SolRes);
		$id = $SolSer->SolSerSlug;

		return redirect()->route('solicitud-servicio.show', compact('id'));
	}

		/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	public function corregirSolRes(Request $request, $id){
		$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
		if (!$SolRes) {
			abort(404);
		}
		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $SolRes->FK_SolResSolSer)->first();

		$Validate = $request->validate([
			'SolResKg'  => 'required|numeric|max:50000|nullable',
			'SolResCantiUnidadRecibida'  => 'numeric|max:50000|nullable',
		]);

		switch($SolicitudServicio->SolSerStatus){
			case 'Conciliado':
			case 'Certificacion':
				$oldValue=$SolRes->SolResKgConciliado;
				$newValue=$request->input('SolResKg');
				if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
					$SolRes->SolResCantiUnidadConciliada = $request->input('SolResCantiUnidadConciliada');
					$SolRes->SolResKgConciliado = $request->input('SolResKg');
				}else{
					$SolRes->SolResKgConciliado = $request->input('SolResKg');
				}
				$SolRes->SolResRM = $request->input('SolResRM');
				break;
			default:
				abort(500);
				break;
		}
		$SolRes->save();


		$SolRes['oldValue'] = $oldValue;
		$SolRes['newValue'] = $newValue;
		$SolRes['RespelName'] = $SolRes->requerimiento->respel->RespelName;
		// se verifica si el cliente tiene comercial asignado
		$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();
		$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();
		// se establece la lista de destinatarios
		if ($SolicitudServicio['cliente']->CliComercial <> null) {
			$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
			$destinatarios = [$SolicitudServicio['personalcliente']->PersEmail];

			$cc = [self::MAIL_RESIDUOS_INTERNO];
			if ($SolicitudServicio->SolServMailCopia !== "null" && $SolicitudServicio->SolServMailCopia !== null) {
                $correoParaAñadir = json_decode($SolicitudServicio->SolServMailCopia);
                if ($correoParaAñadir !== "null" && $correoParaAñadir !== null) {
                    $cc = array_merge($cc, json_decode($SolicitudServicio->SolServMailCopia));
                }
			}
			$cc = array_values(array_unique(array_filter($cc)));
		}else{
			abort(500, 'el cliente no tiene comercial asignado durante el envío de la notificación de cantidad conciliada modificada');
		}

		$SolicitudServicio['comercial'] = $comercial;
		$SolicitudServicio->SolServMailCopia = json_encode($request->input('SolServMailCopia'));



		// se envia un correo por con la informacion del residuo modificado
		Mail::to($destinatarios)
		->cc($cc)
		->send(new CantConciliadaUpdated($SolRes, $SolicitudServicio));

		$log = new audit();
		$log->AuditTabla="solicitud_residuos";
		$log->AuditType="Corregido por Direccion planta";
		$log->AuditRegistro=$SolRes->ID_SolRes;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

		$id = $SolicitudServicio->SolSerSlug;

		return redirect()->route('solicitud-servicio.show', ['solicitud_servicio' => $id]);
	}

		/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function reportes()
	{
		if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)) {

        	return view('reportes.indextemp');
		}else{
			abort(503, "no tiene permisos para acceder a la pagina de reportes");
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function reportesreg(Request $request)
	{
		// Si es POST, procesar la consulta
		if ($request->isMethod('post')) {
			return $this->reportesRegulares($request);
		}

		// Si es GET, mostrar el formulario
		if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			$cliente_id = userController::IDClienteSegunUsuario();
			$clientes = Cliente::where('ID_Cli', $cliente_id)
				->where('CliDelete', 0)
				->get();
		} else {
			$clientes = Cliente::where('CliDelete', 0)
				->where('CliCategoria', 'Cliente')
				->get();
		}

		// Obtener todos los tratamientos activos
		$tratamientos = Tratamiento::where('TratDelete', 0)
			->orderBy('TratName', 'asc')
			->get();

		return view('reportes.ReportRegular', compact('clientes', 'tratamientos'));
	}

	public function reportesRegulares(Request $request)
	{
		// Convertir fechas de DD/MM/YYYY a YYYY-MM-DD
		$fechaInicio = $request->input('Fecha_Inicio');
		$fechaFin = $request->input('Fecha_Fin');

		try {
			// Intentar parsear con formato DD/MM/YYYY
			$FechaInicial = \DateTime::createFromFormat('d/m/Y', $fechaInicio);
			$FechaFinal = \DateTime::createFromFormat('d/m/Y', $fechaFin);

			if ($FechaInicial && $FechaFinal) {
				$FechaInicial = $FechaInicial->format('Y-m-d');
				$FechaFinal = $FechaFinal->format('Y-m-d');
			} else {
				// Si falla, usar las fechas tal como vienen
				$FechaInicial = $fechaInicio;
				$FechaFinal = $fechaFin;
			}
		} catch (\Exception $e) {
			// Fallback: usar fechas por defecto
			$FechaInicial = date('d-m-Y', strtotime('-30 days'));
			$FechaFinal = date('d-m-Y');
		}

		$cliente_id = $request->input('cliente_id');
		$tratamiento_id = $request->input('tratamiento_id');

		$query = SolicitudServicio::with([
			'SolicitudResiduo.generespel.respels',
			'SolicitudResiduo.generespel.gener_sedes.generadors',
			'SolicitudResiduo.certdatoexpress.certificado',
			'cliente.comercialAsignado',
			'SolicitudResiduo.requerimiento.tratamiento',
			'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
			'programacionesrecibidas.Conductor',
			'programacionesrecibidas.Vehiculo',
			'programacionesrealizadas.Conductor',
			'programacionesrealizadas.Vehiculo',
		])
		->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
		->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
		->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
		->where('CliCategoria', 'Cliente')
		->where('progvehiculos.ProgVehDelete', '=', 0);

		// Si es un cliente, solo puede ver sus propias solicitudes
		if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			$cliente_id = userController::IDClienteSegunUsuario();
			$query->where('clientes.ID_Cli', $cliente_id);
		} else if ($cliente_id) {
			// Si es otro tipo de usuario y seleccionó un cliente específico
			$query->where('clientes.ID_Cli', $cliente_id);
		}

		// Filtro por tratamiento
		if ($tratamiento_id) {
			$query->whereHas('SolicitudResiduo.requerimiento', function($q) use ($tratamiento_id) {
				$q->where('FK_ReqTrata', $tratamiento_id);
			});
		}

		$servicios = $query->get();
		$Residuosoriginal = null;

		return view('reportes.Regular', compact('servicios', 'Residuosoriginal'));
	}

public function reportesCliente()
	 {
		 if (!in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			 return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección');
		 }

		 return view('reportes.ReportCliente');
	 }

	 public function reportesClienteGenerar(Request $request)
	 {
		 if (!in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			 return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección');
		 }

		 $fechaInicio = $request->input('Fecha_Inicio');
		 $fechaFin = $request->input('Fecha_Fin');
		 $cliente_id = userController::IDClienteSegunUsuario();

		 // Validar que el cliente_id no sea nulo
		 if (!$cliente_id) {
			 return redirect()->route('home')->with('error', 'No se pudo identificar su cuenta de cliente');
		 }

		 // Convertir fechas si vienen en formato diferente
		 try {
			 // Si vienen en formato YYYY-MM-DD (del input type="date"), usarlas directamente
			 if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
				 $fecha_inicio = $fechaInicio;
				 $fecha_fin = $fechaFin;
			 } else {
				 // Intentar parsear con formato DD/MM/YYYY
				 $fecha_inicio_obj = \DateTime::createFromFormat('d/m/Y', $fechaInicio);
				 $fecha_fin_obj = \DateTime::createFromFormat('d/m/Y', $fechaFin);

				 if ($fecha_inicio_obj && $fecha_fin_obj) {
					 $fecha_inicio = $fecha_inicio_obj->format('Y-m-d');
					 $fecha_fin = $fecha_fin_obj->format('Y-m-d');
				 } else {
					 // Si falla, usar las fechas tal como vienen
					 $fecha_inicio = $fechaInicio;
					 $fecha_fin = $fechaFin;
				 }
			 }
		 } catch (\Exception $e) {
			 Log::error('Error al procesar fechas en reportesClienteGenerar', [
				 'fecha_inicio' => $fechaInicio,
				 'fecha_fin' => $fechaFin,
				 'error' => $e->getMessage()
			 ]);
			 // Fallback: usar fechas por defecto
			 $fecha_inicio = date('Y-m-d', strtotime('-30 days'));
			 $fecha_fin = date('Y-m-d');
		 }

		 $caseTipoTransporte = "
    CASE progvehiculos.ProgVehtipo
        WHEN 0 THEN 'Externo'
        WHEN 1 THEN 'PROSARC'
        WHEN 2 THEN 'Alquilado'
        ELSE 'No Especificado'
    END
";
		 $servicios = SolicitudServicio::with([
			 'SolicitudResiduo.generespel.respels',
			 'SolicitudResiduo.generespel.gener_sedes.generadors',
			 'SolicitudResiduo.generespel.gener_sedes.municipio',
			 'SolicitudResiduo.certdatoexpress.certificado',
			 'SolicitudResiduo.certdato.certificado',
			 'cliente.comercialAsignado',
			 'SolicitudResiduo.requerimiento.tratamiento',
			 'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
			 'programacionesrecibidas.Conductor',
			 'programacionesrecibidas.Ayudante',
			 'programacionesrecibidas.Vehiculo',
			 'programacionesrealizadas.Conductor',
			 'programacionesrealizadas.Ayudante',
			 'programacionesrealizadas.Vehiculo',
			 'municipio',
		 ])
		 ->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
		 ->leftJoin('personals as conductor', function($join) {
			 $join->on('conductor.ID_Pers', '=', 'progvehiculos.FK_ProgConductor')
				  ->where('progvehiculos.ProgVehtipo', '=', 1);
		 })
		 ->leftJoin('personals as ayudante', function($join) {
			 $join->on('ayudante.ID_Pers', '=', 'progvehiculos.FK_ProgAyudante')
				  ->where('progvehiculos.ProgVehtipo', '=', 1);
		 })
		 ->leftJoin('vehiculos', 'vehiculos.ID_Vehic', '=', 'progvehiculos.FK_ProgVehiculo')
		 ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
		 ->join('solicitud_residuos', 'solicitud_servicios.ID_SolSer', '=', 'solicitud_residuos.FK_SolResSolSer')
		 ->select(
			 'solicitud_servicios.*',
			 'progvehiculos.ProgVehSalida',
			 'progvehiculos.ProgVehFecha',
			 'progvehiculos.ProgVehExclusive',
			 'progvehiculos.ProgVehtipo',
			 DB::raw("$caseTipoTransporte as TipoTransporteCalc"),
			 'conductor.PersFirstName as conductor_nombre',
			 'conductor.PersLastName as conductor_apellido',
			 'ayudante.PersFirstName as ayudante_nombre',
			 'ayudante.PersLastName as ayudante_apellido',
			 'vehiculos.VehicPlaca as vehiculo_placa',
			 'progvehiculos.ProgVehNameConductorEXT',
			 'progvehiculos.ProgVehNameAuxiliarEXT',
			 'progvehiculos.ProgVehPlacaEXT'
		 )
		 ->whereBetween('progvehiculos.ProgVehSalida', [$fecha_inicio, $fecha_fin])
		 ->where('clientes.ID_Cli', $cliente_id)
		 ->where('progvehiculos.ProgVehDelete', '=', 0)
		 ->where('solicitud_servicios.FK_SolSerCliente', $cliente_id)
		 ->where('solicitud_residuos.SolResDelete', '=', 0)
		 // Filtros menos restrictivos: mostrar servicios con datos de conciliación o recibidos
		 ->where(function($query) {
			 $query->whereNotNull('solicitud_residuos.SolResKgConciliado')
				   ->where('solicitud_residuos.SolResKgConciliado', '>', 0)
				   ->orWhere(function($q) {
					   $q->whereNotNull('solicitud_residuos.SolResKgRecibido')
						 ->where('solicitud_residuos.SolResKgRecibido', '>', 0);
				   });
		 })
		 // Buscar servicios que tengan certificado (express o regular)
		 ->where(function($query) {
			 $query->whereHas('SolicitudResiduo.certdatoexpress.certificado')
				   ->orWhereHas('SolicitudResiduo.certdato.certificado');
		 })
		 ->distinct()
		 ->get();

		 return view('reportes.ReportCliente', compact('servicios', 'request'));
	 }

	 public function exportToExcel(Request $request)
	 {
		 if (!in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			 return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección');
		 }

		 // Convertir fechas de DD/MM/YYYY a YYYY-MM-DD
		 $fechaInicio = $request->input('Fecha_Inicio');
		 $fechaFin = $request->input('Fecha_Fin');

		 try {
			 // Si vienen en formato YYYY-MM-DD (del input type="date"), usarlas directamente
			 if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
				 $fecha_inicio = $fechaInicio;
				 $fecha_fin = $fechaFin;
			 } else {
				 // Intentar parsear con formato DD/MM/YYYY
				 $fecha_inicio_obj = \DateTime::createFromFormat('d/m/Y', $fechaInicio);
				 $fecha_fin_obj = \DateTime::createFromFormat('d/m/Y', $fechaFin);

				 if ($fecha_inicio_obj && $fecha_fin_obj) {
					 $fecha_inicio = $fecha_inicio_obj->format('Y-m-d');
					 $fecha_fin = $fecha_fin_obj->format('Y-m-d');
				 } else {
					 // Si falla, usar las fechas tal como vienen
					 $fecha_inicio = $fechaInicio;
					 $fecha_fin = $fechaFin;
				 }
			 }
		 } catch (\Exception $e) {
			 Log::error('Error al procesar fechas en exportToExcel', [
				 'fecha_inicio' => $fechaInicio,
				 'fecha_fin' => $fechaFin,
				 'error' => $e->getMessage()
			 ]);
			 // Fallback: usar fechas por defecto
			 $fecha_inicio = date('Y-m-d', strtotime('-30 days'));
			 $fecha_fin = date('Y-m-d');
		 }

		 $cliente_id = userController::IDClienteSegunUsuario();

		 // Validar que el cliente_id no sea nulo
		 if (!$cliente_id) {
			 return redirect()->route('home')->with('error', 'No se pudo identificar su cuenta de cliente');
		 }

		 $caseTipoTransporte = "
    CASE progvehiculos.ProgVehtipo
        WHEN 0 THEN 'Externo'
        WHEN 1 THEN 'PROSARC'
        WHEN 2 THEN 'Alquilado'
        ELSE 'No Especificado'
    END
";
		 $servicios = SolicitudServicio::with([
			 'SolicitudResiduo.generespel.respels',
			 'SolicitudResiduo.generespel.gener_sedes.generadors',
			 'SolicitudResiduo.generespel.gener_sedes.municipio',
			 'SolicitudResiduo.certdatoexpress.certificado',
			 'SolicitudResiduo.certdato.certificado',
			 'cliente.comercialAsignado',
			 'SolicitudResiduo.requerimiento.tratamiento',
			 'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
			 'programacionesrecibidas.Conductor',
			 'programacionesrecibidas.Ayudante',
			 'programacionesrecibidas.Vehiculo',
			 'programacionesrealizadas.Conductor',
			 'programacionesrealizadas.Ayudante',
			 'programacionesrealizadas.Vehiculo',
			 'municipio',
		 ])
		 ->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
		 ->leftJoin('personals as conductor', function($join) {
			 $join->on('conductor.ID_Pers', '=', 'progvehiculos.FK_ProgConductor')
				  ->where('progvehiculos.ProgVehtipo', '=', 1);
		 })
		 ->leftJoin('personals as ayudante', function($join) {
			 $join->on('ayudante.ID_Pers', '=', 'progvehiculos.FK_ProgAyudante')
				  ->where('progvehiculos.ProgVehtipo', '=', 1);
		 })
		 ->leftJoin('vehiculos', 'vehiculos.ID_Vehic', '=', 'progvehiculos.FK_ProgVehiculo')
		 ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
		 ->join('solicitud_residuos', 'solicitud_servicios.ID_SolSer', '=', 'solicitud_residuos.FK_SolResSolSer')
		 ->select(
			 'solicitud_servicios.*',
			 'progvehiculos.ProgVehSalida',
			 'progvehiculos.ProgVehFecha',
			 'progvehiculos.ProgVehExclusive',
			 'progvehiculos.ProgVehtipo',
			 DB::raw("$caseTipoTransporte as TipoTransporteCalc"),
			 'conductor.PersFirstName as conductor_nombre',
			 'conductor.PersLastName as conductor_apellido',
			 'ayudante.PersFirstName as ayudante_nombre',
			 'ayudante.PersLastName as ayudante_apellido',
			 'vehiculos.VehicPlaca as vehiculo_placa',
			 'progvehiculos.ProgVehNameConductorEXT',
			 'progvehiculos.ProgVehNameAuxiliarEXT',
			 'progvehiculos.ProgVehPlacaEXT'
		 )
		 ->whereBetween('progvehiculos.ProgVehSalida', [$fecha_inicio, $fecha_fin])
		 ->where('clientes.ID_Cli', $cliente_id)
		 ->where('progvehiculos.ProgVehDelete', '=', 0)
		 ->where('solicitud_servicios.FK_SolSerCliente', $cliente_id)
		 ->where('solicitud_residuos.SolResDelete', '=', 0)
		 // Filtros menos restrictivos: mostrar servicios con datos de conciliación o recibidos
		 ->where(function($query) {
			 $query->whereNotNull('solicitud_residuos.SolResKgConciliado')
				   ->where('solicitud_residuos.SolResKgConciliado', '>', 0)
				   ->orWhere(function($q) {
					   $q->whereNotNull('solicitud_residuos.SolResKgRecibido')
						 ->where('solicitud_residuos.SolResKgRecibido', '>', 0);
				   });
		 })
		 // Buscar servicios que tengan certificado (express o regular)
		 ->where(function($query) {
			 $query->whereHas('SolicitudResiduo.certdatoexpress.certificado')
				   ->orWhereHas('SolicitudResiduo.certdato.certificado');
		 })
		 ->distinct()
		 ->get();

		 // Generar el contenido HTML
		 $html = view('reportes.excel', compact('servicios'))->render();

		 // Configurar los headers para forzar la descarga
		 $headers = [
			 'Content-Type' => 'application/vnd.ms-excel',
			 'Content-Disposition' => 'attachment; filename="Reporte_Servicios_'.date('Y-m-d').'.xls"',
		 ];

		 return response($html, 200, $headers);
	 }
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function tiporeporte(Request $request)
	{
		return view('reportes.Tiporeporte');

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function refechas(Request $request)
	{
		return view('reportes.refechas');

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function ventasfechas(Request $request)
	{
		return view('reportes.ventasfechas');

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function ventas(Request $request)
	{

		$FechaInicial = $request->input('Fecha_Inicio');
		$FechaFinal = $request->input('Fecha_Fin');

		$servicios = DB::table('solicitud_servicios')
			->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
			->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
			->join('personals', 'personals.ID_Pers', '=', 'CliComercial')
			->join('solicitud_residuos', 'solicitud_residuos.FK_SolResSolSer', '=', 'solicitud_servicios.ID_SolSer')
			->join('requerimientos', 'requerimientos.ID_Req', '=', 'solicitud_residuos.FK_SolResRequerimiento')
			->join('respels', 'respels.ID_Respel', '=', 'requerimientos.FK_ReqRespel')
			->join('tratamientos', 'tratamientos.ID_Trat', '=', 'requerimientos.FK_ReqTrata')
			->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
			->where('CliCategoria', 'Cliente')
			->where('progvehiculos.ProgVehDelete', '=', 0)
			->select('solicitud_servicios.SolSerRMs', 'solicitud_servicios.ID_SolSer', 'solicitud_servicios.SolNumeroFactura', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliNit', 'solicitud_servicios.SolSerCollectAddress', 'respels.RespelName', 'respels.RespelEstado', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741', 'tratamientos.TratName', 'solicitud_residuos.SolResKgEnviado', 'solicitud_residuos.SolResKgRecibido', 'solicitud_residuos.SolResKgConciliado', 'personals.PersFirstName', 'personals.PersSecondName', 'personals.PersLastName')
			->get();

			//return $servicios;



		return view('reportes.ventas', compact('servicios'));

	}




	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function reportesexpress(Request $request)
	{

	return view('reportes.ReportExpress');

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function reportesExpr(Request $request)
	{

		$fechaInicio = $request->input('Fecha_Inicio');
		$fechaFin = $request->input('Fecha_Fin');

		// Convertir fechas de DD/MM/YYYY a YYYY-MM-DD
		try {
			// Intentar parsear con formato DD/MM/YYYY
			$FechaInicial = \DateTime::createFromFormat('d/m/Y', $fechaInicio);
			$FechaFinal = \DateTime::createFromFormat('d/m/Y', $fechaFin);

			if ($FechaInicial && $FechaFinal) {
				$FechaInicial = $FechaInicial->format('Y-m-d');
				$FechaFinal = $FechaFinal->format('Y-m-d');
			} else {
				// Si falla, usar las fechas tal como vienen (por si vienen en formato YYYY-MM-DD)
				$FechaInicial = $fechaInicio;
				$FechaFinal = $fechaFin;
			}
		} catch (\Exception $e) {
			// Fallback: usar fechas por defecto
			$FechaInicial = date('Y-m-d', strtotime('-30 days'));
			$FechaFinal = date('Y-m-d');
		}

		if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)) {

			switch (Auth::user()->UsRol) {
				case ('Programador'):
				case ('AdministradorBogota'):
				case ('AdministradorPlanta'):
				case ('AsistenteComercial'):
				case ('JefeOperaciones'):
				case ('Supervisor'):
				case ('Tesorería'):
				case ('AsistenteLogistica'):
				case ('JefeLogistica'):

					$servicios = SolicitudServicio::with([
						'SolicitudResiduo.generespel.respels',
						'SolicitudResiduo.generespel.gener_sedes.generadors',
						'SolicitudResiduo.certdatoexpress.certificado',
						'cliente.comercialAsignado',
						'SolicitudResiduo.requerimiento.tratamiento',
						'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
						'programacionesrecibidas',
						'programacionesrealizadas',
					])
					->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
					->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
					//->join('certificadosexpress', 'solicitud_servicios.ID_SolSer', '=', 'certificadosexpress.FK_CertSolser' )
					//->select('progvehiculos.ProgVehSalida')
					->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
					->where('CliCategoria', 'ClientePrepago')
					->where('progvehiculos.ProgVehDelete', '=', 0)
					->get();
					break;

				case ('Comercial'):
					$servicios = SolicitudServicio::with([
						'SolicitudResiduo.generespel.respels',
						'SolicitudResiduo.generespel.gener_sedes.generadors',
						'SolicitudResiduo.certdatoexpress.certificado',
						'cliente.comercialAsignado',
						'SolicitudResiduo.requerimiento.tratamiento',
						'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
						'programacionesrecibidas',
						'programacionesrealizadas',
					])
					->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
					//->select('progvehiculos.ProgVehSalida')
					->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
					//->join('certificadosexpress', 'solicitud_servicios.ID_SolSer', '=', 'certificadosexpress.FK_CertSolser' )
					->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
					->where('CliCategoria', 'ClientePrepago')
					->where('progvehiculos.ProgVehDelete', '=', 0)
					->get();

					break;

				default:
				$servicios = SolicitudServicio::with([
					'SolicitudResiduo.generespel.respels',
					'SolicitudResiduo.generespel.gener_sedes.generadors',
					'SolicitudResiduo.certdatoexpress.certificado',
					'cliente.comercialAsignado',
					'SolicitudResiduo.requerimiento.tratamiento',
					'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
					'programacionesrecibidas',
					'programacionesrealizadas',
				])
				->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
				->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
				//->join('certificadosexpress', 'solicitud_servicios.ID_SolSer', '=', 'certificadosexpress.FK_CertSolser' )
				//->select('progvehiculos.ProgVehSalida')
				->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
				->where('CliCategoria', 'ClientePrepago')
				->where('progvehiculos.ProgVehDelete', '=', 0)
				->get();
				break;
			}

			//return $servicios;
			//return $servicios;
        	return view('reportes.Express', compact('servicios'));
		}else{
			//return $FechaInicio;
			abort(503, "no tiene permisos para acceder a la pagina de reportes");
		}
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function registroentrada(Request $request)
	{

		$FechaInicial = $request->input('Fecha_Inicio');
		$FechaFinal = $request->input('Fecha_Fin');

		if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)) {

			switch (Auth::user()->UsRol) {
				case ('Programador'):
				case ('AdministradorBogota'):
				case ('AdministradorPlanta'):
				case ('AsistenteComercial'):
				case ('JefeOperaciones'):
				case ('Supervisor'):
				case ('Tesorería'):
				case ('AsistenteLogistica'):
				case ('JefeLogistica'):

					$servicios = SolicitudServicio::with([
						'SolicitudResiduo.generespel.respels',
						'SolicitudResiduo.generespel.gener_sedes',
						'SolicitudResiduo.generespel.gener_sedes.generadors',
						'SolicitudResiduo.certdato.certificado',
						'cliente.comercialAsignado',
						'SolicitudResiduo.requerimiento.tratamiento',
						'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
						'programacionesrecibidas',
						'programacionesrealizadas',
					])
					->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
					->join('personals', 'personals.ID_Pers', '=', 'progvehiculos.FK_ProgConductor')
					//->select('progvehiculos.ProgVehSalida')
					->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
					->where('progvehiculos.ProgVehDelete', '=', 0)
					->get();

					break;

				case ('Comercial'):
					$idcomercial = Auth::user()->persona->ID_Pers;
					$servicios = SolicitudServicio::with([
						'SolicitudResiduo.generespel.respels',
						'SolicitudResiduo.generespel.gener_sedes',
						'SolicitudResiduo.generespel.gener_sedes.generadors',
						'SolicitudResiduo.certdato.certificado',
						'cliente.comercialAsignado',
						'SolicitudResiduo.requerimiento.tratamiento',
						'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
						'programacionesrecibidas',
						'programacionesrealizadas',
					])
					->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
					->join('personals', 'personals.ID_Pers', '=', 'progvehiculos.FK_ProgConductor')
					//->select('progvehiculos.ProgVehSalida')
					->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
					->where('progvehiculos.ProgVehDelete', '=', 0)
					->whereHas('cliente', function ($query) use ($idcomercial) {
							$query->where('CliComercial', $idcomercial);
						}
					)
					->get();

					break;

				default:
					$servicios = SolicitudServicio::with([
					'SolicitudResiduo.generespel.respels',
					'SolicitudResiduo.generespel.gener_sedes',
					'SolicitudResiduo.generespel.gener_sedes.generadors',
					'SolicitudResiduo.certdato.certificado',
					'cliente.comercialAsignado',
					'SolicitudResiduo.requerimiento.tratamiento',
					'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
					'programacionesrecibidas',
					'programacionesrealizadas',
				])
				->join('progvehiculos', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
				->join('personals', 'personals.ID_Pers', '=', 'progvehiculos.FK_ProgConductor')
				//->select('progvehiculos.ProgVehSalida')
				->whereBetween('progvehiculos.ProgVehSalida',[$FechaInicial, $FechaFinal])
				->get();
				break;
			}

			//return $FechaInicial;
			//return $servicios;
        	return view('reportes.Regular', compact('servicios'));
		}else{
			//return $FechaInicio;
			abort(503, "no tiene permisos para acceder a la pagina de reportes");
		}
	}
    		/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	public function corregirSolResExpress(Request $request, $id){
		$SolRes = SolicitudResiduo::where('SolResSlug', $id)->first();
		if (!$SolRes) {
			abort(404);
		}
		$SolicitudServicio = SolicitudServicio::where('ID_SolSer', $SolRes->FK_SolResSolSer)->first();

		$Validate = $request->validate([
			'SolResKg'  => 'required|numeric|max:50000|nullable',
			'SolResCantiUnidadRecibida'  => 'numeric|max:50000|nullable',
		]);

		switch($SolicitudServicio->SolSerStatus){
			case 'Conciliado':
			case 'Certificacion':
				$oldValue=$SolRes->SolResKgConciliado;
				$newValue=$request->input('SolResKg');
				if($SolRes->SolResTypeUnidad == 'Litros' || $SolRes->SolResTypeUnidad == 'Unidad'){
					$SolRes->SolResCantiUnidadRecibida = $request->input('SolResCantiUnidadConciliada');
					$SolRes->SolResCantiUnidadConciliada = $request->input('SolResCantiUnidadConciliada');
					$SolRes->SolResCantiUnidadTratada = $request->input('SolResCantiUnidadConciliada');
					$SolRes->SolResKgConciliado = $request->input('SolResKg');
					$SolRes->SolResKgRecibido = $request->input('SolResKg');
					$SolRes->SolResKgTratado= $request->input('SolResKg');
				}else{
					$SolRes->SolResKgConciliado = $request->input('SolResKg');
					$SolRes->SolResKgRecibido = $request->input('SolResKg');
					$SolRes->SolResKgTratado = $request->input('SolResKg');
				}
				$SolRes->SolResRM = $request->input('SolResRM');
				break;
			default:
				abort(500);
				break;
		}
		$SolRes->save();


		$SolRes['oldValue'] = $oldValue;
		$SolRes['newValue'] = $newValue;
		$SolRes['RespelName'] = $SolRes->requerimiento->respel->RespelName;
		// se verifica si el cliente tiene comercial asignado
		$SolicitudServicio['cliente'] = Cliente::where('ID_Cli', $SolicitudServicio->FK_SolSerCliente)->first();
		$SolicitudServicio['personalcliente'] = Personal::where('ID_Pers', $SolicitudServicio->FK_SolSerPersona)->first();
		// se establece la lista de destinatarios
		if ($SolicitudServicio['cliente']->CliComercial <> null) {
			$comercial = Personal::where('ID_Pers', $SolicitudServicio['cliente']->CliComercial)->first();
			$destinatarios = [self::MAIL_RESIDUOS_INTERNO];
		}else{
			abort(500, 'el cliente no tiene comercial asignado durante el envío de la notificación de cantidad conciliada modificada');
		}

		$SolicitudServicio['comercial'] = $comercial;
		$SolicitudServicio->SolServMailCopia = json_encode($request->input('SolServMailCopia'));



		// se envia un correo por con la informacion del residuo modificado
		Mail::to($destinatarios)->send(new CantConciliadaUpdated($SolRes, $SolicitudServicio));

		$log = new audit();
		$log->AuditTabla="solicitud_residuos";
		$log->AuditType="Corregido por Direccion planta";
		$log->AuditRegistro=$SolRes->ID_SolRes;
		$log->AuditUser=Auth::user()->email;
		$log->Auditlog=json_encode($request->all());
		$log->save();

		$id = $SolicitudServicio->SolSerSlug;

		return redirect()->route('serviciosexpress.show', compact('id'));
	}

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

	 public function Respelcliente($id)
	 {
		$SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();

		$Generadors = DB::table('generadors')
        ->join('sedes', 'generadors.FK_GenerCli', '=', 'sedes.ID_Sede')
        ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
		->where('clientes.ID_Cli', $SolicitudServicio->FK_SolSerCliente)
		->select('*')
		->get();

		$Respels = DB::table('respels')
		->where('RespelStatus', '=', 'Aprobado')
		->where('SustanciaControlada', '=', 0)
		->where('AceiteUsado', '=', 0)
		->where('RespelDelete', '=', 0)
		->whereBetween('respels.created_at',['2024-01-01 00:00:00','2024-12-31 23:59:00'])
		->select('*')
		->get();


		//return $SolicitudServicio->ID_SolSer;
		return view('solicitud-serv.AñadirNuevoRespel', compact('Generadors', 'Respels', 'SolicitudServicio'));

	 }

	/**
	 * Muestra el formulario de filtros para el reporte de logística
	 */
	public function showReportLogistica()
	{
		if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			$cliente_id = userController::IDClienteSegunUsuario();
			$clientes = Cliente::where('ID_Cli', $cliente_id)
				->where('CliDelete', 0)
				->get();
		} else {
			$clientes = Cliente::where('CliDelete', 0)
				->whereIn('CliCategoria', ['Cliente', 'ClientePrepago'])
				->get();
		}

		return view('reportes.ReportLogistica', compact('clientes'));
	}

	/**
	 * Genera el reporte de logística con los filtros aplicados
	 */
	 public function reportesLogistica(Request $request)
	 {
	 // Convertir fechas de DD/MM/YYYY a YYYY-MM-DD con validación
	 $fechaInicio = $request->input('Fecha_Inicio');
		$fechaFin = $request->input('Fecha_Fin');

		// Convertir fechas de DD/MM/YYYY a YYYY-MM-DD
		try {
			// Intentar parsear con formato DD/MM/YYYY
			$FechaInicial = \DateTime::createFromFormat('d/m/Y', $fechaInicio);
			$FechaFinal = \DateTime::createFromFormat('d/m/Y', $fechaFin);

			if ($FechaInicial && $FechaFinal) {
				$FechaInicial = $FechaInicial->format('Y-m-d');
				$FechaFinal = $FechaFinal->format('Y-m-d');
			} else {
				// Si falla, usar las fechas tal como vienen (pueden ser Y-m-d)
				$FechaInicial = $fechaInicio;
				$FechaFinal = $fechaFin;
			}
		} catch (\Exception $e) {
			// Fallback: usar fechas por defecto
			$FechaInicial = date('Y-m-d', strtotime('-30 days'));
			$FechaFinal = date('Y-m-d');
		}

		$cliente_id = $request->input('cliente_id');

		// Si es un cliente, solo puede ver sus propias solicitudes
		if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
			$cliente_id = userController::IDClienteSegunUsuario();
		}

		// Obtener el tipo de transporte si está presente
		$tipoTransporte = null;
		if ($request->filled('tipo_transporte')) {
			$tipo = strtolower(trim($request->input('tipo_transporte')));
			$map = ['prosarc' => 1, 'alquilado' => 2, 'externo' => 0];
			if (array_key_exists($tipo, $map)) {
				$tipoTransporte = $map[$tipo];
			}
		}

		$query = SolicitudServicio::with([
			'SolicitudResiduo.generespel.respels',
			'SolicitudResiduo.generespel.gener_sedes.generadors',
			'SolicitudResiduo.certdatoexpress.certificado',
			'cliente.comercialAsignado',
			'SolicitudResiduo.requerimiento.tratamiento',
			'SolicitudResiduo.requerimiento.tratamiento.gestor.clientes',
			'programacionesrecibidas.conductor',
			'programacionesrecibidas.ayudante',
			'programacionesrecibidas.vehiculo',
			'programacionesrealizadas.conductor',
			'programacionesrealizadas.ayudante',
			'programacionesrealizadas.vehiculo',
			'programaciones' => function($q) use ($FechaInicial, $FechaFinal) {
				$q->whereBetween('ProgVehSalida', [$FechaInicial . ' 00:00:00', $FechaFinal . ' 23:59:59'])
				  ->where('ProgVehDelete', '=', 0)
				  ->orderBy('ProgVehSalida', 'desc')
				  ->with(['conductor', 'ayudante', 'vehiculo']);
			}
		])
		->whereHas('programaciones', function($q) use ($FechaInicial, $FechaFinal, $tipoTransporte) {
			$q->whereBetween('ProgVehSalida', [$FechaInicial . ' 00:00:00', $FechaFinal . ' 23:59:59'])
			  ->where('ProgVehDelete', '=', 0);

			// Aplicar filtro de tipo de transporte si existe
			if ($tipoTransporte !== null) {
				$q->where('ProgVehtipo', $tipoTransporte);
			}
		})
		->whereHas('cliente', function($q) use ($cliente_id) {
			$q->whereIn('CliCategoria', ['Cliente', 'ClientePrepago']);
			if ($cliente_id) {
				$q->where('ID_Cli', $cliente_id);
			}
		});

		// Cargar las solicitudes sin JOINs que causen duplicados
		$servicios = $query->get();

		// Agregar atributos de programación a cada servicio para compatibilidad con la vista
		$servicios->each(function($servicio) use ($FechaInicial, $FechaFinal) {
			// Obtener la primera programación válida en el rango de fechas
			$programacion = $servicio->programaciones
				->where('ProgVehDelete', 0)
				->whereBetween('ProgVehSalida', [$FechaInicial . ' 00:00:00', $FechaFinal . ' 23:59:59'])
				->sortByDesc('ProgVehSalida')
				->first();

			if ($programacion) {
				$servicio->ProgVehtipo = $programacion->ProgVehtipo;
				$servicio->ProgVehExclusive = $programacion->ProgVehExclusive;
				$servicio->ProgVehNameConductorEXT = $programacion->ProgVehNameConductorEXT;
				$servicio->ProgVehNameAuxiliarEXT = $programacion->ProgVehNameAuxiliarEXT;
				$servicio->ProgVehPlacaEXT = $programacion->ProgVehPlacaEXT;

				// Datos del conductor y ayudante si existen
				if ($programacion->conductor) {
					$servicio->conductor_nombre = $programacion->conductor->PersFirstName;
					$servicio->conductor_apellido = $programacion->conductor->PersLastName;
				}
				if ($programacion->ayudante) {
					$servicio->ayudante_nombre = $programacion->ayudante->PersFirstName;
					$servicio->ayudante_apellido = $programacion->ayudante->PersLastName;
				}
				if ($programacion->vehiculo) {
					$servicio->vehiculo_placa = $programacion->vehiculo->VehicPlaca;
				}

				// Calcular tipo de transporte
				$caseTipoTransporte = "";
				switch($programacion->ProgVehtipo) {
					case 0: $caseTipoTransporte = 'Externo'; break;
					case 1: $caseTipoTransporte = 'PROSARC'; break;
					case 2: $caseTipoTransporte = 'Alquilado'; break;
					default: $caseTipoTransporte = 'No Especificado';
				}
				$servicio->TipoTransporteCalc = $caseTipoTransporte;
			}
		});
		$Residuosoriginal = null;

		return view('reportes.ReporteLogistica', compact('servicios', 'Residuosoriginal'));
	}
}
