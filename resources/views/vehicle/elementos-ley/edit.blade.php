@extends('layouts.app')
@section('htmlheader_title')
	Elementos de ley — {{ $Vehicle->VehicPlaca }}
@endsection
@section('contentheader_title')
	<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
		<i class="fa fa-gavel"></i> Elementos de ley — {{ $Vehicle->VehicPlaca }}
		<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
	</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Lista de verificación — Ley 769 de 2002 e HSEQ</h3>
					<div class="box-tools pull-right">
						<a href="{{ url('/vehicle/' . $Vehicle->VehicPlaca . '/edit') }}" class="btn btn-default btn-sm">
							<i class="fa fa-arrow-left"></i> Volver al vehículo
						</a>
						<a href="{{ url('/vehicle') }}" class="btn btn-default btn-sm">
							<i class="fa fa-list"></i> Lista de vehículos
						</a>
					</div>
				</div>
				<div class="box-body">
					<p class="text-muted">
						<i class="fa fa-info-circle"></i> Lista de verificación según <strong>Art. 30 Ley 769 de 2002</strong> y requisitos <strong>HSEQ</strong>. Los elementos se usan en el preoperacional.
					</p>
					<form role="form" action="{{ route('vehicle.elementos-ley.update', $Vehicle->VehicPlaca) }}" method="POST">
						@csrf
						@method('PUT')

						{{-- EXTINTORES (detalle HSEQ) --}}
						<div class="col-md-12"><hr></div>
						<div class="col-md-12">
							<h4><i class="fa fa-fire-extinguisher"></i> Extintores (detalle HSEQ)</h4>
						</div>
						@php
							$estadosOpciones = ['' => 'Seleccione', 'Bueno' => 'Bueno', 'Regular' => 'Regular', 'Malo' => 'Malo', 'No aplica' => 'No aplica'];
						@endphp
						@foreach([1, 2] as $num)
						<div class="col-md-12">
							<h5 class="text-primary"><strong>Extintor {{ $num }}</strong></h5>
							<div class="row">
								<div class="col-md-4 form-group">
									<label>Capacidad del extintor #{{ $num }} multipropósito</label>
									<input type="text" class="form-control input-sm" name="VehicExt{{ $num }}Capacidad" value="{{ $Vehicle->{'VehicExt'.$num.'Capacidad'} ?? '' }}" placeholder="Ej: 5 lb ABC">
								</div>
								<div class="col-md-4 form-group">
									<label>Fecha de próxima recarga</label>
									@php
										$fechaRecarga = $Vehicle->{'VehicExt'.$num.'FechaRecarga'};
										$fechaRecargaVal = $fechaRecarga ? (is_object($fechaRecarga) ? $fechaRecarga->format('Y-m-d') : \Carbon\Carbon::parse($fechaRecarga)->format('Y-m-d')) : '';
									@endphp
									<input type="date" class="form-control input-sm" name="VehicExt{{ $num }}FechaRecarga" value="{{ $fechaRecargaVal }}">
								</div>
								<div class="col-md-4 form-group">
									<label>Estado del manómetro</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoManometro">
										@foreach($estadosOpciones as $v => $l)
											<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoManometro'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado del pasador de seguridad</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoPasador">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoPasador'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado de la manija de descarga y transporte</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoManija">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoManija'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado de la válvula</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoValvula">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoValvula'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado de la manguera</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoManguera">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoManguera'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado de la boquilla</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoBoquilla">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoBoquilla'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado del cuerpo del cilindro</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoCilindro">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoCilindro'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado de la calcomanía de identificación e instrucciones</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoCalcomania">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoCalcomania'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-4 form-group">
									<label>Estado del soporte</label>
									<select class="form-control input-sm" name="VehicExt{{ $num }}EstadoSoporte">@foreach($estadosOpciones as $v => $l)<option value="{{ $v }}" {{ ($Vehicle->{'VehicExt'.$num.'EstadoSoporte'} ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
								</div>
								<div class="col-md-12 form-group">
									<label>Observación adicional del extintor #{{ $num }}</label>
									<input type="text" class="form-control" name="VehicExt{{ $num }}Observacion" value="{{ $Vehicle->{'VehicExt'.$num.'Observacion'} ?? '' }}" placeholder="Opcional">
								</div>
							</div>
						</div>
						@endforeach

						@foreach($catalogo as $tipo_kit => $items)
							<div class="col-md-12"><hr></div>
							<div class="col-md-12">
								<h4>
									@if($tipo_kit === 'kit_carretera')<i class="fa fa-road"></i>
									@elseif($tipo_kit === 'kit_herramientas')<i class="fa fa-wrench"></i>
									@elseif($tipo_kit === 'botiquin')<i class="fa fa-first-aid"></i>
									@elseif($tipo_kit === 'kit_derrames')<i class="fa fa-tint"></i>
									@else<i class="fa fa-list"></i>
									@endif
									{{ \App\ElementoLeyCatalogo::getNombreTipoKit($tipo_kit) }}
								</h4>
							</div>
							<div class="col-md-12">
								<table class="table table-condensed table-bordered">
									<thead>
										<tr>
											@if($items->contains('tipo_entrada', 'checklist'))
												<th style="width: 40px;">Cumple</th>
											@endif
											<th>Elemento</th>
											@if($items->contains('tipo_entrada', 'cantidad'))
												<th style="width: 100px;">Cantidad</th>
											@endif
											@if($items->contains('tipo_entrada', 'estado'))
												<th style="width: 130px;">Estado</th>
											@endif
											@if($items->contains('requiere_vencimiento', true))
												<th style="width: 140px;">Vencimiento</th>
											@endif
											<th style="width: 180px;">Observaciones</th>
										</tr>
									</thead>
									<tbody>
										@foreach($items as $item)
											@php
												$el = $porVehiculo->get($item->id);
												$tipoEntrada = $item->tipo_entrada ?? 'checklist';
											@endphp
											<tr>
												@if($items->contains('tipo_entrada', 'checklist'))
													<td class="text-center">
														@if($tipoEntrada === 'checklist')
															<input type="checkbox" id="cumple_{{ $item->id }}" name="cumple[{{ $item->id }}]" value="1"
																{{ ($el && $el->cumple) ? 'checked' : '' }}>
														@else
															—
														@endif
													</td>
												@endif
												<td>
													<label for="cumple_{{ $item->id }}" style="font-weight: normal; margin: 0; cursor: pointer;">
														{{ $item->nombre }}
													</label>
												</td>
												@if($items->contains('tipo_entrada', 'cantidad'))
													<td>
														@if($tipoEntrada === 'cantidad')
															<input type="text" class="form-control input-sm" name="valor[{{ $item->id }}]" placeholder="Número"
																value="{{ ($el && $el->valor) ? $el->valor : '' }}">
														@else
															—
														@endif
													</td>
												@endif
												@if($items->contains('tipo_entrada', 'estado'))
													<td>
														@if($tipoEntrada === 'estado')
															<select class="form-control input-sm" name="valor[{{ $item->id }}]">
																<option value="">—</option>
																<option value="Bueno" {{ ($el && $el->valor == 'Bueno') ? 'selected' : '' }}>Bueno</option>
																<option value="Regular" {{ ($el && $el->valor == 'Regular') ? 'selected' : '' }}>Regular</option>
																<option value="Malo" {{ ($el && $el->valor == 'Malo') ? 'selected' : '' }}>Malo</option>
																<option value="No aplica" {{ ($el && $el->valor == 'No aplica') ? 'selected' : '' }}>No aplica</option>
															</select>
														@else
															—
														@endif
													</td>
												@endif
												@if($items->contains('requiere_vencimiento', true))
													<td>
														@if($item->requiere_vencimiento)
															@php
																$fv = $el && $el->fecha_vencimiento ? $el->fecha_vencimiento : null;
																$fvVal = $fv ? (is_object($fv) ? $fv->format('Y-m-d') : \Carbon\Carbon::parse($fv)->format('Y-m-d')) : '';
															@endphp
															<input type="date" class="form-control input-sm" name="vencimiento[{{ $item->id }}]"
																value="{{ $fvVal }}">
														@else
															—
														@endif
													</td>
												@endif
												<td>
													<input type="text" class="form-control input-sm" name="observaciones[{{ $item->id }}]"
														placeholder="Opcional" value="{{ ($el && $el->observaciones) ? $el->observaciones : '' }}">
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						@endforeach

						<div class="col-md-12">
							<hr>
							<button type="submit" class="btn btn-success">
								<i class="fa fa-save"></i> Guardar elementos de ley
							</button>
							<a href="{{ url('/vehicle') }}" class="btn btn-default">Cancelar</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
