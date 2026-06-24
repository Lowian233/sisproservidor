@extends('layouts.app')
@section('htmlheader_title')
Historial Formularios Preoperacionales
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #fbc2eb, #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
	{{'Historial Formularios Preoperacionales'}}
	<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Historial de Formularios Preoperacionales</h3>
					@if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
						<a href="{{ route('vehicle-programacion.index') }}" class="btn btn-info pull-right">
							<i class="fas fa-arrow-left"></i> Volver a Programaciones
						</a>
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						@if(session('success'))
							<div class="alert alert-success">
								{{ session('success') }}
							</div>
						@endif

						<table id="HistorialPreoperacionalTable" class="table table-compact table-bordered table-striped" data-order='[[ 1, "desc"]]'>
							<thead>
								<tr>
									<th>Fecha Programación</th>
									<th>Fecha Formulario</th>
									<th>Vehículo</th>
									<th>Conductor</th>
									<th>Km Inicial</th>
									<th>Km Final</th>
									<th>Km Recorridos</th>
									<th>Estado Vehículo</th>
									<th>Estado Formulario</th>
									<th>Ver PDF</th>
									@if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
									<th>Ver Detalles</th>
									@endif
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach($programaciones as $programacion)
								@php
									$vehiculoPlaca = 'No definido';
									if($programacion->FK_ProgVehiculo){
										foreach($vehiculos as $vehiculoItem){
											if($programacion->FK_ProgVehiculo == $vehiculoItem->ID_Vehic){
												$vehiculoPlaca = $vehiculoItem->VehicPlaca;
												break;
											}
										}
									}
									$conductorNombre = 'No definido';
									if($programacion->FK_ProgConductor){
										foreach($personals as $personal){
											if($programacion->FK_ProgConductor == $personal->ID_Pers){
												$conductorNombre = $personal->PersFirstName.' '.$personal->PersLastName;
												break;
											}
										}
									}
									$kmRecorridos = null;
									if($programacion->ProgVehKmInicial && $programacion->ProgVehKmFinal){
										$kmRecorridos = $programacion->ProgVehKmFinal - $programacion->ProgVehKmInicial;
									}
								@endphp
								<tr>
									<td>{{ date('d/m/Y', strtotime($programacion->ProgVehFecha)) }}</td>
									<td>
										@if($programacion->ProgVehFechaPreoperacional)
											{{ date('d/m/Y h:i A', strtotime($programacion->ProgVehFechaPreoperacional)) }}
										@else
											N/A
										@endif
									</td>
									<td>{{ $vehiculoPlaca }}</td>
									<td>{{ $conductorNombre }}</td>
									<td style="text-align: center;">{{ $programacion->ProgVehKmInicial ?? 'N/A' }}</td>
									<td style="text-align: center;">{{ $programacion->ProgVehKmFinal ?? 'N/A' }}</td>
									<td style="text-align: center;">
										@if($kmRecorridos !== null)
											{{ $kmRecorridos }} km
										@else
											N/A
										@endif
									</td>
									<td>
										@if($programacion->ProgVehEstadoVehiculo)
											@if($programacion->ProgVehEstadoVehiculo == 'Funcional')
												<span class="label label-success">{{ $programacion->ProgVehEstadoVehiculo }}</span>
											@elseif($programacion->ProgVehEstadoVehiculo == 'Requiere Mantenimiento')
												<span class="label label-warning">{{ $programacion->ProgVehEstadoVehiculo }}</span>
											@else
												<span class="label label-danger">{{ $programacion->ProgVehEstadoVehiculo }}</span>
											@endif
										@else
											N/A
										@endif
									</td>
									<td>
										@if($programacion->ProgVehPreoperacionalCompletado)
											<span class="label label-success">Completado</span>
										@else
											<span class="label label-default">Pendiente</span>
										@endif
									</td>
									<td>
										@if($programacion->ProgVehPdfPreoperacional)
											<a href="{{ route('vehicle-programacion.download-pdf-preoperacional', $programacion->ID_ProgVeh) }}" 
											   target="_blank" 
											   class="btn btn-danger btn-block">
												<i class="fas fa-file-pdf"></i> <b>Ver PDF</b>
											</a>
										@else
											<span class="text-muted">No disponible</span>
										@endif
									</td>
									@if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
										<td>
											<a href="{{ route('vehicle-programacion.preoperacional', $programacion->ID_ProgVeh) }}" 
											   class="btn btn-info btn-block">
												<i class="fas fa-eye"></i> <b>Ver</b>
											</a>
										</td>
									@endif
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('NewScript')
<script>
	$(document).ready(function() {
		var selector = '#HistorialPreoperacionalTable';

		// Evitar "Cannot reinitialise DataTable" porque el layout ya inicializa .table
		if ($.fn.dataTable && $.fn.dataTable.isDataTable(selector)) {
			$(selector).DataTable().order([1, 'desc']).draw();
			return;
		}

		$(selector).DataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
			},
			"pageLength": 25,
			"order": [[1, "desc"]]
		});
	});
</script>
@endsection
