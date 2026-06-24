@extends('layouts.app')
@section('htmlheader_title')
{{__('adminlte::message.vehicletitle')}}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.vehicletitle') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					@component('layouts.partials.modal')
						@slot('slug')
							{{$Vehicle->VehicPlaca}}
						@endslot
						@slot('textModal')
							el vehiculo con placa <b>{{$Vehicle->VehicPlaca}}</b>
						@endslot
					@endcomponent
					<h3 class="box-title">{{__('adminlte::message.vehicleedit')}}</h3>
					<a href="{{ route('vehicle.elementos-ley.edit', $Vehicle->VehicPlaca) }}" class="btn btn-info pull-right" style="margin-right: 8px;">
						<i class="fa fa-gavel"></i> Elementos de ley
					</a>
					@if($Vehicle->VehicDelete === 0)
					<a method='get' href='#' data-toggle='modal' data-target='#myModal{{$Vehicle->VehicPlaca}}'  class='btn btn-danger pull-right'><i class="fas fa-trash-alt"></i><b> {{ __('adminlte::message.delete') }}</b></a>
					<form action='/vehicle/{{$Vehicle->VehicPlaca}}' method='POST'>
						@method('DELETE')
						@csrf
						<input  type="submit" id="Eliminar{{$Vehicle->VehicPlaca}}" style="display: none;">
					</form>
					@else
					<form action='/vehicle/{{$Vehicle->VehicPlaca}}' method='POST' style="float: right;">
						@method('DELETE')
						@csrf
						<button type="submit" class='btn btn-success btn-block'>{{ __('adminlte::message.add') }}</button>
					</form>
					@endif
				</div>
				<div class="box box-info">
					<form role="form" action="/vehicle/{{$Vehicle->VehicPlaca}}" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@method('PUT')
						@csrf
						<div class="box-body">
							<div class="form-group col-md-12">
								<label for="FK_VehiSede">{{__('adminlte::message.vehicsedes')}}</label>
								<small class="help-block with-errors">*</small>
								<select class="form-control" id="FK_VehiSede" name="FK_VehiSede" required="true">
									<option value="">{{ __('adminlte::message.select') }}</option>
									@foreach($Sedes as $Sede)
										<option value="{{$Sede->ID_Sede}}" {{$Vehicle->FK_VehiSede == $Sede->ID_Sede ? 'selected' : ''}}>{{$Sede->SedeName}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="VehicPlaca">{{__('adminlte::message.vehicplaca')}}</label>
								<small class="help-block with-errors">*</small>
								<input type="text" class="form-control placa" id="VehicPlaca" name="VehicPlaca" required="true" data-minlength="7" value="{{$Vehicle->VehicPlaca}}">
							</div>
							
							<div class="form-group col-md-6">
								<label for="VehicCapacidad">{{__('adminlte::message.vehiccapacidad')}}</label>
								<small class="help-block with-errors">*</small>
								<input type="number" class="form-control" id="VehicCapacidad" name="VehicCapacidad" max="999999" value="{{$Vehicle->VehicCapacidad}}">
							</div>
							<div class="form-group col-md-6">
								<label for="VehicKmActual">{{__('adminlte::message.vehickm')}}</label>
								{{-- <small class="help-block with-errors">*</small> --}}
								<input disabled type="number" class="form-control" id="VehicKmActual" name="VehicKmActual" max="999999" value="{{$Vehicle->VehicKmActual}}">
							</div>
							<div class="form-group col-md-6">
								<label for="VehicTipo">{{__('adminlte::message.vehictipo')}}</label>
								<small class="help-block with-errors">*</small>
								<select class="form-control" id="VehicTipo" name="VehicTipo" required="true" maxlength="64">
									<option value="Camión sencillo (2 Ejes)" {{ $Vehicle->VehicTipo == 'Camión sencillo (2 Ejes)' ? 'selected' : '' }}>Camión sencillo (2 Ejes)</option>
									<option value="Dobletroque (3 Ejes)" {{ $Vehicle->VehicTipo == 'Dobletroque (3 Ejes)' ? 'selected' : '' }}>Dobletroque (3 Ejes)</option>
									<option value="Camión de 4 ejes" {{ $Vehicle->VehicTipo == 'Camión de 4 ejes' ? 'selected' : '' }}>Camión de 4 ejes</option>
									<option value="Tractocamión (2S1)" {{ $Vehicle->VehicTipo == 'Tractocamión (2S1)' ? 'selected' : '' }}>Tractocamión (2S1)</option>
									<option value="Tractocamión (2S3)" {{ $Vehicle->VehicTipo == 'Tractocamión (2S3)' ? 'selected' : '' }}>Tractocamión (2S3)</option>
									<option value="Tractocamión (3S1)" {{ $Vehicle->VehicTipo == 'Tractocamión (3S1)' ? 'selected' : '' }}>Tractocamión (3S1)</option>
									<option value="Tractocamión (3S2)" {{ $Vehicle->VehicTipo == 'Tractocamión (3S2)' ? 'selected' : '' }}>Tractocamión (3S2)</option>
									<option value="Tractocamión (3S3)" {{ $Vehicle->VehicTipo == 'Tractocamión (3S3)' ? 'selected' : '' }}>Tractocamión (3S3)</option>
								</select>
							</div>

							<!-- Documentos del vehículo -->
							<div class="col-md-12"><hr><h4><i class="fa fa-file-alt"></i> Documentos del vehículo</h4></div>

							<div class="col-md-12">
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicSoat"><strong>SOAT</strong> — Archivo</label>
										@if($Vehicle->VehicSoat)
											<p><a href="{{ Storage::disk('public')->url($Vehicle->VehicSoat) }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link-alt"></i> Ver documento actual</a></p>
										@endif
										<input type="file" class="form-control" name="VehicSoat" accept=".pdf,.jpg,.jpeg,.png">
										<small class="text-muted">Dejar vacío para mantener el actual. PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicSoatVencimiento"><strong>SOAT</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicSoatVencimiento" value="{{ $Vehicle->VehicSoatVencimiento ? $Vehicle->VehicSoatVencimiento->format('Y-m-d') : '' }}" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicTecnomecanica"><strong>Tecnomecánica</strong> — Archivo</label>
										@if($Vehicle->VehicTecnomecanica)
											<p><a href="{{ Storage::disk('public')->url($Vehicle->VehicTecnomecanica) }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link-alt"></i> Ver documento actual</a></p>
										@endif
										<input type="file" class="form-control" name="VehicTecnomecanica" accept=".pdf,.jpg,.jpeg,.png">
										<small class="text-muted">Dejar vacío para mantener el actual.</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicTecnomecanicaVencimiento"><strong>Tecnomecánica</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicTecnomecanicaVencimiento" value="{{ $Vehicle->VehicTecnomecanicaVencimiento ? $Vehicle->VehicTecnomecanicaVencimiento->format('Y-m-d') : '' }}" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicPoliza"><strong>Póliza</strong> — Archivo</label>
										@if($Vehicle->VehicPoliza)
											<p><a href="{{ Storage::disk('public')->url($Vehicle->VehicPoliza) }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link-alt"></i> Ver documento actual</a></p>
										@endif
										<input type="file" class="form-control" name="VehicPoliza" accept=".pdf,.jpg,.jpeg,.png">
										<small class="text-muted">Dejar vacío para mantener el actual.</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicPolizaVencimiento"><strong>Póliza</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicPolizaVencimiento" value="{{ $Vehicle->VehicPolizaVencimiento ? $Vehicle->VehicPolizaVencimiento->format('Y-m-d') : '' }}" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicTarjetaPropiedad"><strong>Tarjeta de propiedad</strong> — Archivo</label>
										@if($Vehicle->VehicTarjetaPropiedad)
											<p><a href="{{ Storage::disk('public')->url($Vehicle->VehicTarjetaPropiedad) }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-external-link-alt"></i> Ver documento actual</a></p>
										@endif
										<input type="file" class="form-control" name="VehicTarjetaPropiedad" accept=".pdf,.jpg,.jpeg,.png">
										<small class="text-muted">Dejar vacío para mantener el actual.</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicTarjetaPropiedadVencimiento"><strong>Tarjeta de propiedad</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicTarjetaPropiedadVencimiento" value="{{ $Vehicle->VehicTarjetaPropiedadVencimiento ? $Vehicle->VehicTarjetaPropiedadVencimiento->format('Y-m-d') : '' }}" required>
									</div>
								</div>
								<div class="col-md-12" style="margin-top: 10px;">
									<p class="text-muted"><i class="fa fa-fire-extinguisher"></i> Los extintores se gestionan en <a href="{{ route('vehicle.elementos-ley.edit', $Vehicle->VehicPlaca) }}">Elementos de ley</a>.</p>
								</div>
							</div>
						</div>
						<!-- /.box-body -->
						<div class="box box-info">
							<div class="box-footer">
								<button type="submit" class="btn btn-success pull-right">{{__('adminlte::message.update')}}</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection