@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.progvehictitle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #fbc2eb, #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
	{{'Programación'}}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.progvehiclist') }}</h3>
					@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC))
						<a href="/vehicle-programacion/create" class="btn btn-info pull-right"><i class="fas fa-calendar-alt"></i> {{ __('adminlte::message.progvehiccreatetext') }}</a>
					@endif
					@if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
						@php
							$hoy = \Illuminate\Support\Carbon::today()->toDateString();
							$preopPendiente = collect($programacions)->first(function($p) use ($hoy) {
								try {
									$fechaProg = \Illuminate\Support\Carbon::parse($p->ProgVehFecha)->toDateString();
								} catch (\Exception $e) {
									return false;
								}
								$completado = !empty($p->ProgVehPreoperacionalCompletado);
								return $fechaProg === $hoy && !$completado;
							});

							// Botón "Crear" como respaldo: primer preoperacional pendiente desde hoy en adelante
							$preopCrear = collect($programacions)
								->filter(function($p) {
									try {
										$fechaProg = \Illuminate\Support\Carbon::parse($p->ProgVehFecha)->startOfDay();
									} catch (\Exception $e) {
										return false;
									}
									$completado = !empty($p->ProgVehPreoperacionalCompletado);
									return $fechaProg->greaterThanOrEqualTo(\Illuminate\Support\Carbon::today()) && !$completado;
								})
								->sortBy(function($p) {
									try {
										return \Illuminate\Support\Carbon::parse(($p->ProgVehFecha ?? '') . ' ' . ($p->ProgVehSalida ?? '00:00:00'));
									} catch (\Exception $e) {
										return \Illuminate\Support\Carbon::now()->addYears(50);
									}
								})
								->first();
						@endphp
						<a href="{{ route('vehicle-programacion.preoperacional-create') }}" class="btn btn-success pull-right" style="margin-right: 8px;">
							<i class="fas fa-plus-circle"></i> <b>Crear preoperacional</b>
						</a>
						@if($preopPendiente)
							<a href="{{ route('vehicle-programacion.preoperacional', $preopPendiente->ID_ProgVeh) }}" class="btn btn-warning pull-right" style="margin-right: 8px;">
								<i class="fas fa-clipboard-check"></i> <b>Llenar preoperacional (directo)</b>
							</a>
						@endif
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						@if(session('warning'))
							<div class="alert alert-warning" style="margin-bottom: 15px;">
								{{ session('warning') }}
							</div>
						@endif
						@if((in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR)) && collect($programacions)->isEmpty())
							<div class="alert alert-warning" style="margin-bottom: 15px;">
								<b>No tienes programaciones asignadas para mostrar.</b><br>
								Posibles causas:
								<ul style="margin-bottom: 0;">
									<li>La programación no tiene <b>conductor asignado</b> (FK_ProgConductor).</li>
									<li>La solicitud aún no está en estado <b>Programado</b> o <b>Notificado</b>.</li>
									<li>La fecha de programación está fuera del rango configurado (2024-07-01 a 2026-12-31).</li>
								</ul>
							</div>
						@endif
						<table id="ProgVehicleTable" class="table table-compact table-bordered table-striped" data-order='[[ 1, "desc"]]'>
							<thead>
								<tr>
									<th>{{ __('adminlte::message.progvehicclient') }}</th>
									<th>{{ __('adminlte::message.progvehicfech') }}</th>
									<th>{{ __('adminlte::message.progvehicvehic') }}</th>
									<th>{{ __('adminlte::message.progvehicsalida') }}</th>
									<th>{{ __('adminlte::message.progvehicayudan') }}</th>
									{{-- @if(Auth::user()->UsRol <> __('adminlte::message.Conductor') || Auth::user()->UsRol2 <> __('adminlte::message.Conductor')) --}}
									<th>{{ __('adminlte::message.progvehicconduc') }}</th>
									<th>Puntos de recolección</th>
									<th>{{ __('adminlte::message.progvehicllegada') }}</th>
									<th>{{ __('adminlte::message.progvehictype') }}</th>
									<th>Autorización</th>
									{{-- @endif --}}
									@if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
									<th>Acciones</th>
									@endif
									<th>{{ __('adminlte::message.progvehicservi2') }}</th>
									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2))
									<th>{{ __('adminlte::message.edit') }}</th>
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
									<th>{{ __('adminlte::message.progvehicserauth') }}</th>
									@endif
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach($programacions as $programacion)
								@php
									$vehiculoPlaca = 'No definido';
									if($programacion->ProgVehtipo == 1){
										foreach($personals as $personal){
											if($programacion->FK_ProgAyudante == $personal->ID_Pers){
												$ayudante = $personal->PersFirstName.' '.$personal->PersLastName;
											}
										}
										foreach($personals as $personal){
											if($programacion->FK_ProgConductor == $personal->ID_Pers){
												$conductor = $personal->PersFirstName.' '.$personal->PersLastName;
											}
										}
										foreach ($vehiculos as $vehiculo) {
											if($programacion->FK_ProgVehiculo == $vehiculo->ID_Vehic){
												$vehiculoPlaca = $vehiculo->VehicPlaca;
											}
										}
									}
									elseif($programacion->ProgVehtipo == 2){
										foreach($personals as $personal){
											if($programacion->FK_ProgAyudante == $personal->ID_Pers){
												$ayudante = $personal->PersFirstName.' '.$personal->PersLastName;
											}
										}
										$conductor = 'No aplica';
										foreach ($vehiculos as $vehiculo) {
											if($programacion->FK_ProgVehiculo == $vehiculo->ID_Vehic){
												$vehiculoPlaca = $vehiculo->VehicPlaca;
											}
										}
									}
									else{
										$ayudante = 'No aplica';
										$conductor = $programacion->SolSerConductor;
										$vehiculoPlaca = $programacion->SolSerVehiculo;
									}
									if (!isset($ayudante)) {
										$ayudante = 'No definido';
									}
									if (!isset($conductor)) {
										$conductor = 'No definido';
									}
								@endphp
								<tr style="{{$programacion->ProgVehDelete === 1 ? 'color: red' : ''}}">
									<td>{{$programacion->CliName}}</td>
									<td>{{$programacion->ProgVehFecha}}</td>
									<td>{{$vehiculoPlaca}}</td>
									<td>{{date('h:i A', strtotime($programacion->ProgVehSalida))}}</td>
									<td>{{$ayudante}}</td>
									{{-- @if(Auth::user()->UsRol <> __('adminlte::message.Conductor')) --}}
										<td>{{$conductor}}</td>
										<td><ul class="list-group">
											@foreach($programacion->puntosderecoleccion as $Punto)
										    <li data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Dirección de los Puntos</b>" data-content="<p style='width: 50%'>
										    	<ul class='list-group'>
										    	    <li class='list-group-item'><b>Generador:</b>{{$Punto->generadors->GenerName}}<br><b>Sede:</b>{{$Punto->GSedeName}}<br><b>Dirección:</b>{{$Punto->GSedeAddress}}<br><b>Cel:</b>{{$Punto->GSedeCelular}}</li>
										    	</ul>
										    	<br>Para mas detalles comuníquese con su <b>Jefe de Logistica</b> </p>" class="list-group-item">{{$Punto->GSedeName}}</li>
										    @endforeach
										</ul></td>
										<td>{{$programacion->ProgVehEntrada <> null ? date('h:i A', strtotime($programacion->ProgVehEntrada)) : ''}}</td>
										<td>
                                            @if ($programacion->ProgVehtipo == 1)
                                                Interno<br>
                                                @if ($programacion->ProgVehExclusive == 1)
                                                (Exclusivo)
                                                @else
                                                (Recorrido)
                                                @endif
                                            @elseif($programacion->ProgVehtipo == 2)
                                                Alquilado
                                            @else
                                                Externo
                                            @endif
                                        </td>
										<td>{{$programacion->ProgVehStatus}}</td>
									{{-- @endif --}}

									@if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
										<td>
											<a method='get' href='/vehicle-programacion/{{$programacion->ID_ProgVeh}}' class='btn btn-info btn-block' style="margin-bottom: 5px;"><i class="fas fa-search"></i> <b>Datos</b></a>
											@php
												$puedeLlenarPreoperacional = false;
												try {
													$fechaHoy = \Illuminate\Support\Carbon::today();
													$fechaProg = \Illuminate\Support\Carbon::parse($programacion->ProgVehFecha)->startOfDay();
													$puedeLlenarPreoperacional = $fechaProg->greaterThanOrEqualTo($fechaHoy);
												} catch (\Exception $e) {
													$puedeLlenarPreoperacional = false;
												}
											@endphp
											@if($puedeLlenarPreoperacional)
												<a href='{{ route("vehicle-programacion.preoperacional", $programacion->ID_ProgVeh) }}' class='btn btn-{{ isset($programacion->ProgVehPreoperacionalCompletado) && $programacion->ProgVehPreoperacionalCompletado ? "success" : "warning" }} btn-block'>
													<i class="fas fa-clipboard-check"></i> 
													<b>{{ isset($programacion->ProgVehPreoperacionalCompletado) && $programacion->ProgVehPreoperacionalCompletado ? "Ver" : "Llenar" }} Preoperacional</b>
												</a>
											@else
												{{-- Botón de respaldo para abrir sin depender de modales/condiciones --}}
												<a href='{{ route("vehicle-programacion.preoperacional", $programacion->ID_ProgVeh) }}' class='btn btn-default btn-block'>
													<i class="fas fa-clipboard-list"></i> <b>Preoperacional (directo)</b>
												</a>
											@endif
										</td>
									@endif
									<td><a href="/solicitud-servicio/{{$programacion->SolSerSlug}}"class='btn btn-info btn-block' title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i> #{{$programacion->ID_SolSer}}</a></td>
									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2))
										<td><a method='get' href='/vehicle-programacion/{{$programacion->ID_ProgVeh}}/edit' class='btn btn-warning btn-block'><i class="fas fa-edit"></i> <b>{{__('adminlte::message.edit')}}</b></a></td>
									@endif

									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
									@php
										$Status = ['Aprobado', 'Programado', 'Notificado'];
									@endphp
									<td>
										<a onclick="ModalStatus('{{$programacion->ID_ProgVeh}}', '{{$programacion->ID_SolSer}}', '{{in_array($programacion->SolSerStatus, $Status)}}', 'Programado', 'Notificar')" style="text-align: center;" class="btn btn-{{$programacion->SolSerStatus == 'Programado' ? 'success' : ($programacion->SolSerStatus == 'Notificado' ? 'info' : 'default')}}"><i class="fas fa-sign-out-alt"></i> {{ __('adminlte::message.progvehicserauth')}}</a>
									</td>
									@endif
								</tr>
								@endforeach
								<div id="ModalStatus"></div>
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
	var observacion = ``;
	function updatecaracteres() {
		var area = document.getElementById("textDescription");
		var message = document.getElementById("caracteresrestantes");
		var maxLength = 4000;
		message.innerHTML = (maxLength-area.value.length) + " caracteres restantes";
		observacion = area.value;

	}
	function ModalStatus(slug, idServicio, boolean, value, text){
		if(boolean == 1){
			$('#ModalStatus').empty();
			$('#ModalStatus').append(`
				<div class="modal modal-default fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-body">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<div text-align: center; margin: auto;">
									<span style=""><p>¿Quiere `+text+` la fecha programada para la solicitud <b>N° `+idServicio+`</b>?</p></span>
									<form action="/vehicle-programacion/`+slug+`/updateStatus" method="POST" data-toggle="validator" id="SolSer">
										@csrf
										@method('PUT')
										<div class="form-group col-md-12">
											<label  color: black; text-align: left;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="Observaciones de Logistica: <b>(Opcional)</b>" data-content="redacte los detalles u observaciones que desea enviar junto a la notificación de la programación para el servicio #`+idServicio+`"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Observaciones de Logistica:</label>
											<small id="caracteresrestantes" class="help-block with-errors">`+(status == 'No Deacuerdo' ? '*' : '')+`</small>
											<textarea onchange="updatecaracteres()" id="textDescription" rows ="5" style="resize: vertical;" maxlength="4000" class="form-control col-xs-12" required name="solserdescript">`+observacion+`</textarea>
										</div>
										<input type="submit" id="Cambiar`+slug+`" style="display: none;">
										<input type="text" name="solserslug" value="`+slug+`" style="display: none;">
										<input type="text" name="solserstatus" value="`+value+`" style="display: none;">
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
								<label for="Cambiar`+slug+`" class='btn btn-success'>Enviar</label>
							</div>
						</div>
					</div>
				</div>
			`);
			$('#SolSer').validator('update');
			popover();
			envsubmit();
			$('#myModal').modal();
		}
	}
</script>
@endsection
