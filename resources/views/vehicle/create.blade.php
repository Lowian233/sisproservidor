@extends('layouts.app')
@section('htmlheader_title')
{{__('adminlte::message.vehicletitle')}}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.vehicletitle') }}
  <div style="background-color:#ecf0f5; position@extends('layouts.app')
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
				<div class="box-header with-border">
					<h3 class="box-title">{{__('adminlte::message.vehiclecreate')}}</h3>
				</div>
				<div class="box box-info">
					<form role="form" action="/vehicle" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@csrf
						<div class="box-body">
							<div class="form-group col-md-12">
								<label for="FK_VehiSede">{{__('adminlte::message.vehicsedes')}}</label>
								<small class="help-block with-errors">*</small>
								<select class="form-control" id="FK_VehiSede" name="FK_VehiSede" required="true">
									<option value="">{{ __('adminlte::message.select') }}</option>
									@foreach($Sedes as $Sede)
										<option value="{{$Sede->ID_Sede}}">{{$Sede->SedeName}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="VehicPlaca">{{__('adminlte::message.vehicplaca')}}</label>
								<small class="help-block with-errors">*</small>
								<input type="text" class="form-control placa" id="VehicPlaca" name="VehicPlaca" data-minlength="7" required="true">
							</div>
							
							<div class="form-group col-md-6">
								<label for="VehicCapacidad">{{__('adminlte::message.vehiccapacidad')}}</label>
								<small class="help-block with-errors">*</small>
								<input type="number" class="form-control" id="VehicCapacidad" name="VehicCapacidad" required="true" max="999999">
							</div>
							<div class="form-group col-md-6">
								<label for="VehicKmActual">{{__('adminlte::message.vehickm')}}</label>
								<small class="help-block with-errors">*</small>
								<input type="number" class="form-control" id="VehicKmActual" name="VehicKmActual" required="true" max="999999">
							</div>
							<div class="form-group col-md-6">
								<label for="VehicTipo">{{__('adminlte::message.vehictipo')}}</label>
								<small class="help-block with-errors">*</small>
								<select class="form-control" id="VehicTipo" name="VehicTipo" required="true" maxlength="64">
									<option value="Camión sencillo (2 Ejes)">Camión sencillo (2 Ejes)</option>
									<option value="Dobletroque (3 Ejes)">Dobletroque (3 Ejes)</option>
									<option value="Camión de 4 ejes">Camión de 4 ejes</option>
									<option value="Tractocamión (2S1)">Tractocamión (2S1)</option>
									<option value="Tractocamión (2S3)">Tractocamión (2S3)</option>
									<option value="Tractocamión (3S1)">Tractocamión (3S1)</option>
									<option value="Tractocamión (3S2)">Tractocamión (3S2)</option>
									<option value="Tractocamión (3S3)">Tractocamión (3S3)</option>
								</select>
							</div>

							<!-- Documentos del vehículo -->
							<div class="col-md-12"><hr><h4><i class="fa fa-file-alt"></i> Documentos del vehículo</h4></div>

							<div class="col-md-12">
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicSoat"><strong>SOAT</strong> — Archivo <span class="text-danger">*</span></label>
										<input type="file" class="form-control" name="VehicSoat" accept=".pdf,.jpg,.jpeg,.png" required>
										<small class="text-muted">PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicSoatVencimiento"><strong>SOAT</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicSoatVencimiento" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicTecnomecanica"><strong>Tecnomecánica</strong> — Archivo <span class="text-danger">*</span></label>
										<input type="file" class="form-control" name="VehicTecnomecanica" accept=".pdf,.jpg,.jpeg,.png" required>
										<small class="text-muted">PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicTecnomecanicaVencimiento"><strong>Tecnomecánica</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicTecnomecanicaVencimiento" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicPoliza"><strong>Póliza</strong> — Archivo <span class="text-danger">*</span></label>
										<input type="file" class="form-control" name="VehicPoliza" accept=".pdf,.jpg,.jpeg,.png" required>
										<small class="text-muted">PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicPolizaVencimiento"><strong>Póliza</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicPolizaVencimiento" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #f9f9f9; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicTarjetaPropiedad"><strong>Tarjeta de propiedad</strong> — Archivo <span class="text-danger">*</span></label>
										<input type="file" class="form-control" name="VehicTarjetaPropiedad" accept=".pdf,.jpg,.jpeg,.png" required>
										<small class="text-muted">PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicTarjetaPropiedadVencimiento"><strong>Tarjeta de propiedad</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicTarjetaPropiedadVencimiento" required>
									</div>
								</div>
								<div class="col-md-12" style="margin-top: 10px;"><strong><i class="fa fa-fire-extinguisher"></i> Extintores (obligatorio 2 por ley)</strong></div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #fff3e0; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicExtintor1"><strong>Extintor 1</strong> — Archivo <span class="text-danger">*</span></label>
										<input type="file" class="form-control" name="VehicExtintor1" accept=".pdf,.jpg,.jpeg,.png" required>
										<small class="text-muted">PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicExtintor1Vencimiento"><strong>Extintor 1</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicExtintor1Vencimiento" required>
									</div>
								</div>
								<div class="row" style="margin-bottom: 15px; padding: 12px; background: #fff3e0; border-radius: 4px;">
									<div class="col-md-6 form-group">
										<label for="VehicExtintor2"><strong>Extintor 2</strong> — Archivo <span class="text-danger">*</span></label>
										<input type="file" class="form-control" name="VehicExtintor2" accept=".pdf,.jpg,.jpeg,.png" required>
										<small class="text-muted">PDF, JPG o PNG. Máx. 5 MB</small>
									</div>
									<div class="col-md-6 form-group">
										<label for="VehicExtintor2Vencimiento"><strong>Extintor 2</strong> — Vencimiento <span class="text-danger">*</span></label>
										<input type="date" class="form-control" name="VehicExtintor2Vencimiento" required>
									</div>
								</div>
							</div>
						</div>
						<div class="box box-info">
							<div class="box-footer">
								<button type="submit" class="btn btn-success pull-right">{{__('adminlte::message.register')}}</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection