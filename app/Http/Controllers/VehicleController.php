<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Vehiculo;
use App\audit;
use App\Permisos;

class VehicleController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{ 
		if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)){
			// Obtener fechas del filtro (por defecto último mes)
			$fechaInicio = $request->input('fecha_inicio', date('Y-m-01')); // Primer día del mes actual
			$fechaFin = $request->input('fecha_fin', date('Y-m-t')); // Último día del mes actual
			
			$Vehicles = DB::table('vehiculos')
				->Join('sedes', 'vehiculos.FK_VehiSede', '=', 'sedes.ID_Sede')
				->select('vehiculos.*', 'sedes.SedeName')
				->where('vehiculos.FK_VehiSede', 1)
				->where('VehicDelete', 0) // Filtrar siempre los eliminados
				->get();
			
			// Agregar datos del informe gerencial para cada vehículo
			foreach ($Vehicles as $vehicle) {
				// Cantidad de servicios efectuados por transportador
				$serviciosPorTransportador = DB::table('progvehiculos')
					->leftJoin('personals as conductor', function($join) {
						$join->on('conductor.ID_Pers', '=', 'progvehiculos.FK_ProgConductor');
					})
					->where('progvehiculos.FK_ProgVehiculo', $vehicle->ID_Vehic)
					->where('progvehiculos.ProgVehDelete', 0)
					->whereNotNull('progvehiculos.FK_ProgServi')
					->whereBetween('progvehiculos.ProgVehFecha', [$fechaInicio, $fechaFin])
					->select(
						DB::raw('COALESCE(conductor.PersFirstName, progvehiculos.ProgVehNameConductorEXT) as nombre_conductor'),
						DB::raw('COALESCE(conductor.PersLastName, "") as apellido_conductor'),
						DB::raw('COUNT(DISTINCT progvehiculos.FK_ProgServi) as cantidad_servicios'),
						DB::raw('CASE 
							WHEN progvehiculos.ProgVehtipo = 2 THEN "Externo"
							WHEN progvehiculos.ProgVehtipo = 1 THEN "Interno"
							ELSE "Otro"
						END as tipo_transportador')
					)
					->groupBy('nombre_conductor', 'apellido_conductor', 'tipo_transportador')
					->get();
				
				$vehicle->servicios_por_transportador = $serviciosPorTransportador;
				
				// Total de servicios del vehículo
				$vehicle->total_servicios = DB::table('progvehiculos')
					->where('FK_ProgVehiculo', $vehicle->ID_Vehic)
					->where('ProgVehDelete', 0)
					->whereNotNull('FK_ProgServi') 
					->whereBetween('ProgVehFecha', [$fechaInicio, $fechaFin])
					->distinct('FK_ProgServi')
					->count('FK_ProgServi');
				
				// Kilos transportados (usando SolResKgRecibido o SolResKgConciliado si está disponible)
				$kilosTransportados = DB::table('progvehiculos')
					->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
					->join('solicitud_residuos', 'solicitud_residuos.FK_SolResSolSer', '=', 'solicitud_servicios.ID_SolSer')
					->where('progvehiculos.FK_ProgVehiculo', $vehicle->ID_Vehic)
					->where('progvehiculos.ProgVehDelete', 0)
					->whereBetween('progvehiculos.ProgVehFecha', [$fechaInicio, $fechaFin])
					->select(
						DB::raw('SUM(COALESCE(solicitud_residuos.SolResKgConciliado, solicitud_residuos.SolResKgRecibido, solicitud_residuos.SolResKgEnviado, 0)) as total_kilos'),
						DB::raw('DATE(progvehiculos.ProgVehFecha) as fecha')
					)
					->groupBy('fecha')
					->get();
				
				$vehicle->kilos_por_dia = $kilosTransportados;
				$vehicle->total_kilos = $kilosTransportados->sum('total_kilos');
				
				// Kilometraje diario - obtener el último registro de cada día
				$kilometrajeDiario = DB::table('progvehiculos')
					->where('FK_ProgVehiculo', $vehicle->ID_Vehic)
					->where('ProgVehDelete', 0)
					->whereNotNull('progVehKm')
					->whereBetween('ProgVehFecha', [$fechaInicio, $fechaFin])
					->select(
						DB::raw('DATE(ProgVehFecha) as fecha'),
						DB::raw('MAX(progVehKm) as km_final'),
						DB::raw('MIN(progVehKm) as km_inicial'),
						DB::raw('CASE WHEN MAX(progVehKm) - MIN(progVehKm) < 0 THEN 0 ELSE MAX(progVehKm) - MIN(progVehKm) END as km_recorridos')
					)
					->groupBy(DB::raw('DATE(ProgVehFecha)'))
					->orderBy('fecha', 'desc')
					->get();
				
				// Calcular km_recorridos en PHP para mayor precisión
				foreach ($kilometrajeDiario as $km) {
					$km->km_recorridos = max(0, ($km->km_final - $km->km_inicial));
				}
				
				$vehicle->kilometraje_diario = $kilometrajeDiario;
				$vehicle->total_km_recorridos = $kilometrajeDiario->sum('km_recorridos');

				// Combustible cargado en el período (galones)
				$combustiblePeriodo = DB::table('vehiculo_combustible')
					->where('FK_Vehiculo', $vehicle->ID_Vehic)
					->whereBetween('fecha', [$fechaInicio, $fechaFin])
					->sum('cantidad');
				$vehicle->total_combustible = (float) $combustiblePeriodo;
			}			
			return view('vehicle.index', compact('Vehicles', 'fechaInicio', 'fechaFin'));
		}
		else{
			abort(403);
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1)){
			$Sedes = DB::table('sedes')
				->select('ID_Sede', 'SedeName')
				->where('FK_SedeCli', userController::IDClienteSegunUsuario())
				->where('SedeDelete', 0)
				->get();
			return view('vehicle.create', compact('Sedes'));
		}
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
		$request->validate([
			'VehicPlaca' => 'required|string|max:12',
			'VehicCapacidad' => 'required|numeric',
			'VehicKmActual' => 'nullable|numeric',
			'VehicTipo' => 'required|string|max:64',
			'FK_VehiSede' => 'required|exists:sedes,ID_Sede',
			'VehicSoatVencimiento' => 'required|date',
			'VehicTecnomecanicaVencimiento' => 'required|date',
			'VehicPolizaVencimiento' => 'required|date',
			'VehicTarjetaPropiedadVencimiento' => 'required|date',
			'VehicExtintor1Vencimiento' => 'required|date',
			'VehicExtintor2Vencimiento' => 'required|date',
			'VehicSoat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicTecnomecanica' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicPoliza' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicTarjetaPropiedad' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicExtintor1' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicExtintor2' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
		], [
			'VehicSoat.required' => 'El archivo SOAT es obligatorio.',
			'VehicSoatVencimiento.required' => 'La fecha de vencimiento del SOAT es obligatoria.',
			'VehicTecnomecanica.required' => 'El archivo de Tecnomecánica es obligatorio.',
			'VehicTecnomecanicaVencimiento.required' => 'La fecha de vencimiento de Tecnomecánica es obligatoria.',
			'VehicPoliza.required' => 'El archivo de Póliza es obligatorio.',
			'VehicPolizaVencimiento.required' => 'La fecha de vencimiento de la Póliza es obligatoria.',
			'VehicTarjetaPropiedad.required' => 'El archivo de Tarjeta de propiedad es obligatorio.',
			'VehicTarjetaPropiedadVencimiento.required' => 'La fecha de vencimiento de la Tarjeta de propiedad es obligatoria.',
			'VehicExtintor1.required' => 'El documento del Extintor 1 es obligatorio (mínimo 2 extintores por ley).',
			'VehicExtintor1Vencimiento.required' => 'La fecha de vencimiento del Extintor 1 es obligatoria.',
			'VehicExtintor2.required' => 'El documento del Extintor 2 es obligatorio (mínimo 2 extintores por ley).',
			'VehicExtintor2Vencimiento.required' => 'La fecha de vencimiento del Extintor 2 es obligatoria.',
		]);

		$Vehicle = new Vehiculo();
		$Vehicle->VehicPlaca = $request->input('VehicPlaca');
		$Vehicle->VehicCapacidad = $request->input('VehicCapacidad');
		$Vehicle->VehicKmActual = $request->input('VehicKmActual');
		$Vehicle->VehicTipo = $request->input('VehicTipo');
		$Vehicle->FK_VehiSede = $request->input('FK_VehiSede');
		$Vehicle->VehicInternExtern = 1;
		$Vehicle->VehicDelete = 0;

		$Vehicle->VehicSoatVencimiento = $request->input('VehicSoatVencimiento') ?: null;
		$Vehicle->VehicTecnomecanicaVencimiento = $request->input('VehicTecnomecanicaVencimiento') ?: null;
		$Vehicle->VehicPolizaVencimiento = $request->input('VehicPolizaVencimiento') ?: null;
		$Vehicle->VehicTarjetaPropiedadVencimiento = $request->input('VehicTarjetaPropiedadVencimiento') ?: null;
		$Vehicle->VehicExtintor1Vencimiento = $request->input('VehicExtintor1Vencimiento') ?: null;
		$Vehicle->VehicExtintor2Vencimiento = $request->input('VehicExtintor2Vencimiento') ?: null;

		$placaSlug = Str::slug($request->input('VehicPlaca'));
		if ($request->hasFile('VehicSoat')) {
			$Vehicle->VehicSoat = $request->file('VehicSoat')->storeAs(
				'vehiculos/soat',
				$placaSlug . '_' . time() . '.' . $request->file('VehicSoat')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicTecnomecanica')) {
			$Vehicle->VehicTecnomecanica = $request->file('VehicTecnomecanica')->storeAs(
				'vehiculos/tecnomecanica',
				$placaSlug . '_' . time() . '.' . $request->file('VehicTecnomecanica')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicPoliza')) {
			$Vehicle->VehicPoliza = $request->file('VehicPoliza')->storeAs(
				'vehiculos/poliza',
				$placaSlug . '_' . time() . '.' . $request->file('VehicPoliza')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicTarjetaPropiedad')) {
			$Vehicle->VehicTarjetaPropiedad = $request->file('VehicTarjetaPropiedad')->storeAs(
				'vehiculos/tarjeta_propiedad',
				$placaSlug . '_' . time() . '.' . $request->file('VehicTarjetaPropiedad')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicExtintor1')) {
			$Vehicle->VehicExtintor1 = $request->file('VehicExtintor1')->storeAs(
				'vehiculos/extintores',
				$placaSlug . '_ext1_' . time() . '.' . $request->file('VehicExtintor1')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicExtintor2')) {
			$Vehicle->VehicExtintor2 = $request->file('VehicExtintor2')->storeAs(
				'vehiculos/extintores',
				$placaSlug . '_ext2_' . time() . '.' . $request->file('VehicExtintor2')->getClientOriginalExtension(),
				'public'
			);
		}

		$Vehicle->save();

		return redirect()->route('vehicle.index');
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{   
		//
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1)){
			$Vehicle = Vehiculo::where('VehicPlaca', $id)->first();
			if (!$Vehicle) {
				abort(404);
			}
			$Sedes = DB::table('sedes')
				->select('ID_Sede', 'SedeName')
				->where('FK_SedeCli', userController::IDClienteSegunUsuario())
				->where('SedeDelete', 0)
				->get();
			return view('vehicle.edit', compact('Vehicle','Sedes'));
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
		$Vehicle = Vehiculo::where('VehicPlaca', $id)->first();
		if (!$Vehicle) {
			abort(404);
		}

		$request->validate([
			'VehicPlaca' => 'required|string|max:12',
			'VehicCapacidad' => 'required|numeric',
			'VehicKmActual' => 'nullable|numeric',
			'VehicTipo' => 'required|string|max:64',
			'FK_VehiSede' => 'required|exists:sedes,ID_Sede',
			'VehicSoatVencimiento' => 'required|date',
			'VehicTecnomecanicaVencimiento' => 'required|date',
			'VehicPolizaVencimiento' => 'required|date',
			'VehicTarjetaPropiedadVencimiento' => 'required|date',
			'VehicSoat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicTecnomecanica' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicPoliza' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
			'VehicTarjetaPropiedad' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
		], [
			'VehicSoatVencimiento.required' => 'La fecha de vencimiento del SOAT es obligatoria.',
			'VehicTecnomecanicaVencimiento.required' => 'La fecha de vencimiento de Tecnomecánica es obligatoria.',
			'VehicPolizaVencimiento.required' => 'La fecha de vencimiento de la Póliza es obligatoria.',
			'VehicTarjetaPropiedadVencimiento.required' => 'La fecha de vencimiento de la Tarjeta de propiedad es obligatoria.',
		]);

		$Vehicle->VehicPlaca = $request->input('VehicPlaca');
		$Vehicle->VehicCapacidad = $request->input('VehicCapacidad');
		$Vehicle->VehicKmActual = $request->input('VehicKmActual');
		$Vehicle->VehicTipo = $request->input('VehicTipo');
		$Vehicle->FK_VehiSede = $request->input('FK_VehiSede');
		$Vehicle->VehicInternExtern = 1;
		$Vehicle->VehicSoatVencimiento = $request->input('VehicSoatVencimiento') ?: null;
		$Vehicle->VehicTecnomecanicaVencimiento = $request->input('VehicTecnomecanicaVencimiento') ?: null;
		$Vehicle->VehicPolizaVencimiento = $request->input('VehicPolizaVencimiento') ?: null;
		$Vehicle->VehicTarjetaPropiedadVencimiento = $request->input('VehicTarjetaPropiedadVencimiento') ?: null;
		// Extintores se gestionan en Elementos de ley

		if ($request->hasFile('VehicSoat')) {
			if ($Vehicle->VehicSoat) {
				Storage::disk('public')->delete($Vehicle->VehicSoat);
			}
			$Vehicle->VehicSoat = $request->file('VehicSoat')->storeAs(
				'vehiculos/soat',
				Str::slug($Vehicle->VehicPlaca) . '_' . time() . '.' . $request->file('VehicSoat')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicTecnomecanica')) {
			if ($Vehicle->VehicTecnomecanica) {
				Storage::disk('public')->delete($Vehicle->VehicTecnomecanica);
			}
			$Vehicle->VehicTecnomecanica = $request->file('VehicTecnomecanica')->storeAs(
				'vehiculos/tecnomecanica',
				Str::slug($Vehicle->VehicPlaca) . '_' . time() . '.' . $request->file('VehicTecnomecanica')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicPoliza')) {
			if ($Vehicle->VehicPoliza) {
				Storage::disk('public')->delete($Vehicle->VehicPoliza);
			}
			$Vehicle->VehicPoliza = $request->file('VehicPoliza')->storeAs(
				'vehiculos/poliza',
				Str::slug($Vehicle->VehicPlaca) . '_' . time() . '.' . $request->file('VehicPoliza')->getClientOriginalExtension(),
				'public'
			);
		}
		if ($request->hasFile('VehicTarjetaPropiedad')) {
			if ($Vehicle->VehicTarjetaPropiedad) {
				Storage::disk('public')->delete($Vehicle->VehicTarjetaPropiedad);
			}
			$Vehicle->VehicTarjetaPropiedad = $request->file('VehicTarjetaPropiedad')->storeAs(
				'vehiculos/tarjeta_propiedad',
				Str::slug($Vehicle->VehicPlaca) . '_' . time() . '.' . $request->file('VehicTarjetaPropiedad')->getClientOriginalExtension(),
				'public'
			);
		}
		$Vehicle->save();

		$log = new audit();
		$log->AuditTabla = "vehiculos";
		$log->AuditType = "Modificado";
		$log->AuditRegistro = $Vehicle->VehicPlaca;
		$log->AuditUser = Auth::user()->email;
		$log->Auditlog = $request->except(['VehicSoat', 'VehicTecnomecanica', 'VehicPoliza', 'VehicTarjetaPropiedad', 'VehicExtintor1', 'VehicExtintor2']);
		$log->save();

		return redirect()->route('vehicle.index');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$Vehicle = Vehiculo::where('VehicPlaca', $id)->first();
		if (!$Vehicle) {
			abort(404);
		}
			if ($Vehicle->VehicDelete == 0) {
				$Vehicle->VehicDelete = 1;
			}
			else{
				$Vehicle->VehicDelete = 0;
			}
		$Vehicle->save();

		$log = new audit();
		$log->AuditTabla = "vehiculos";
		$log->AuditType = "Eliminado";
		$log->AuditRegistro = $Vehicle->VehicPlaca;
		$log->AuditUser = Auth::user()->email;
		$log->Auditlog = $Vehicle->VehicDelete;
		$log->save();

		return redirect()->route('vehicle.index');
	}

	/**
	 * Export vehicle report to Excel
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function exportToExcel(Request $request)
	{
		if(!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)){
			abort(403);
		}

		// Obtener fechas del filtro
		$fechaInicio = $request->input('fecha_inicio', date('Y-m-01'));
		$fechaFin = $request->input('fecha_fin', date('Y-m-t'));
		
		$Vehicles = DB::table('vehiculos')
			->Join('sedes', 'vehiculos.FK_VehiSede', '=', 'sedes.ID_Sede')
			->select('vehiculos.*', 'sedes.SedeName')
			->where('vehiculos.FK_VehiSede', 1)
			->where('VehicDelete', 0)
			->get();
		
		// Agregar datos del informe gerencial para cada vehículo
		foreach ($Vehicles as $vehicle) {
			// Servicios por transportador
			$serviciosPorTransportador = DB::table('progvehiculos')
				->leftJoin('personals as conductor', function($join) {
					$join->on('conductor.ID_Pers', '=', 'progvehiculos.FK_ProgConductor');
				})
				->where('progvehiculos.FK_ProgVehiculo', $vehicle->ID_Vehic)
				->where('progvehiculos.ProgVehDelete', 0)
				->whereNotNull('progvehiculos.FK_ProgServi')
				->whereBetween('progvehiculos.ProgVehFecha', [$fechaInicio, $fechaFin])
				->select(
					DB::raw('COALESCE(conductor.PersFirstName, progvehiculos.ProgVehNameConductorEXT) as nombre_conductor'),
					DB::raw('COALESCE(conductor.PersLastName, "") as apellido_conductor'),
					DB::raw('COUNT(DISTINCT progvehiculos.FK_ProgServi) as cantidad_servicios'),
					DB::raw('CASE 
						WHEN progvehiculos.ProgVehtipo = 2 THEN "Externo"
						WHEN progvehiculos.ProgVehtipo = 1 THEN "Interno"
						ELSE "Otro"
					END as tipo_transportador')
				)
				->groupBy('nombre_conductor', 'apellido_conductor', 'tipo_transportador')
				->get();
			
			$vehicle->servicios_por_transportador = $serviciosPorTransportador;
			$vehicle->total_servicios = DB::table('progvehiculos')
				->where('FK_ProgVehiculo', $vehicle->ID_Vehic)
				->where('ProgVehDelete', 0)
				->whereNotNull('FK_ProgServi')
				->whereBetween('ProgVehFecha', [$fechaInicio, $fechaFin])
				->distinct('FK_ProgServi')
				->count('FK_ProgServi');
			
			// Kilos transportados
			$kilosTransportados = DB::table('progvehiculos')
				->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'progvehiculos.FK_ProgServi')
				->join('solicitud_residuos', 'solicitud_residuos.FK_SolResSolSer', '=', 'solicitud_servicios.ID_SolSer')
				->where('progvehiculos.FK_ProgVehiculo', $vehicle->ID_Vehic)
				->where('progvehiculos.ProgVehDelete', 0)
				->whereBetween('progvehiculos.ProgVehFecha', [$fechaInicio, $fechaFin])
				->select(
					DB::raw('SUM(COALESCE(solicitud_residuos.SolResKgConciliado, solicitud_residuos.SolResKgRecibido, solicitud_residuos.SolResKgEnviado, 0)) as total_kilos'),
					DB::raw('DATE(progvehiculos.ProgVehFecha) as fecha')
				)
				->groupBy('fecha')
				->get();
			
			$vehicle->kilos_por_dia = $kilosTransportados;
			$vehicle->total_kilos = $kilosTransportados->sum('total_kilos');
			
			// Kilometraje diario
			$kilometrajeDiario = DB::table('progvehiculos')
				->where('FK_ProgVehiculo', $vehicle->ID_Vehic)
				->where('ProgVehDelete', 0)
				->whereNotNull('progVehKm')
				->whereBetween('ProgVehFecha', [$fechaInicio, $fechaFin])
				->select(
					DB::raw('DATE(ProgVehFecha) as fecha'),
					DB::raw('MAX(progVehKm) as km_final'),
					DB::raw('MIN(progVehKm) as km_inicial'),
					DB::raw('CASE WHEN MAX(progVehKm) - MIN(progVehKm) < 0 THEN 0 ELSE MAX(progVehKm) - MIN(progVehKm) END as km_recorridos')
				)
				->groupBy(DB::raw('DATE(ProgVehFecha)'))
				->orderBy('fecha', 'desc')
				->get();
			
			foreach ($kilometrajeDiario as $km) {
				$km->km_recorridos = max(0, ($km->km_final - $km->km_inicial));
			}
			
			$vehicle->kilometraje_diario = $kilometrajeDiario;
			$vehicle->total_km_recorridos = $kilometrajeDiario->sum('km_recorridos');
		}
		
		// Generar el contenido HTML
		$html = view('vehicle.excel', compact('Vehicles', 'fechaInicio', 'fechaFin'))->render();
		
		// Configurar los headers para forzar la descarga
		$headers = [
			'Content-Type' => 'application/vnd.ms-excel',
			'Content-Disposition' => 'attachment; filename="Informe_Vehiculos_'.date('Y-m-d').'.xls"',
		];
		
		return response($html, 200, $headers);
	}
}
