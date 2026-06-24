@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.mantvehititle') }}
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
							{{$MantVehicles->ID_Mv}}
						@endslot
						@slot('textModal')
							el mantenimiento <b>{{$MantVehicles->MvType}}</b> del vehiculo <b>{{$MantVehicles->FK_VehMan}}</b>
						@endslot
					@endcomponent
					<h3 class="box-title">{{ __('adminlte::message.mantvehititleedit') }}</h3>
					@if($MantVehicles->MvDelete === 0)
					<a method='get' href='#' data-toggle='modal' data-target='#myModal{{$MantVehicles->ID_Mv}}' class='btn btn-danger pull-right'><i class="fas fa-trash-alt"></i><b> {{ __('adminlte::message.delete') }}</b></a>
					<form action='/vehicle-mantenimiento/{{$MantVehicles->ID_Mv}}' method='POST'>
						@method('DELETE')
						@csrf
						<input  type="submit" id="Eliminar{{$MantVehicles->ID_Mv}}" style="display: none;">
					</form>
					@else
					<form action='/vehicle-mantenimiento/{{$MantVehicles->ID_Mv}}' method='POST' style="float: right;">
						@method('DELETE')
						@csrf
						<button type="submit" class='btn btn-success btn-block'>{{ __('adminlte::message.add') }}</button>
					</form>
					@endif
				</div>
				<div class="box box-info">
					<form role="form" action="/vehicle-mantenimiento/{{$MantVehicles->ID_Mv}}" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@method('PUT')
						@csrf
						@if ($errors->createManVeh->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->createManVeh->all() as $error)
										<p>{{$error}}</p>
									@endforeach
								</ul>
							</div>
						@endif
						<div class="box-body">
							<div class="col-xs-12 col-md-12">
								<div class="form-group col-xs-12 col-md-6">
									<label for="FK_VehMan">{{ __('adminlte::message.mantvehivehic') }}</label>
									<select name="FK_VehMan" class="form-control" required id="FK_VehMan">
										<option value="" >{{ __('adminlte::message.select') }}</option>
										@foreach($vehiculos as $vehiculo)
										<option value="{{$vehiculo->ID_Vehic}}" {{$MantVehicles->FK_VehMan == $vehiculo->ID_Vehic ? 'selected' : ''}}>{{$vehiculo->VehicPlaca}}</option>
										@endforeach
									</select>
									<small class="help-block with-errors"></small>
								</div>
								<div class="form-group col-xs-6 col-md-6">
									<label for="MvType">{{ __('adminlte::message.mantvehitype') }}</label>
									<input type="text" class="form-control" required maxlength="255" id="MvType" name="MvType" value="{{$MantVehicles->MvType}}">
									<small class="help-block with-errors"></small>
								</div>
								
							</div>
							<div class="col-xs-12 col-md-12">
								<div class="form-group col-xs-12 col-md-6">
									<label for="HoraMavInicio1">{{ __('adminlte::message.mantvehiinicio1') }}</label>
									<input type="date" required id="HoraMavInicio1" name="HoraMavInicio1" class="form-control" value="{{date('Y-m-d', strtotime($MantVehicles->HoraMavInicio))}}">
									<small class="help-block with-errors"></small>
								</div>
								<div class="form-group col-xs-12 col-md-6">
									<label for="HoraMavFin1">{{ __('adminlte::message.mantvehifin1') }}</label>
									<input type="date" id="HoraMavFin1" required name="HoraMavFin1" class="form-control" value="{{date('Y-m-d', strtotime($MantVehicles->HoraMavFin))}}">
									<small class="help-block with-errors"></small>
								</div>

							</div>
							<div class="col-xs-12 col-md-12">
								<div class="form-group col-xs-12 col-md-6">
									<label for="HoraMavInicio">{{ __('adminlte::message.mantvehiinicio') }}</label>
									<input required class="form-control" type="time" id="HoraMavInicio" name="HoraMavInicio" value="{{date('H:i', strtotime($MantVehicles->HoraMavInicio))}}">
									<small class="help-block with-errors"></small>
								</div>
								<div class="form-group col-xs-12 col-md-6">
									<label for="HoraMavFin">{{ __('adminlte::message.mantvehifin') }}</label>
									<input class="form-control horas" type="time" required id="HoraMavFin" name="HoraMavFin" value="{{date('H:i', strtotime($MantVehicles->HoraMavFin))}}">
									<small class="help-block with-errors"></small>
								</div>
								<div class="form-group col-xs-12 col-md-6">
									<label for="MvKm">{{ __('adminlte::message.mantvehikm') }}</label>
									<input maxlength="11" class="form-control number" required type="text" id="MvKm" name="MvKm" value="{{$MantVehicles->MvKm}}">
									<small class="help-block with-errors"></small>
								</div>
							</div>
							{{-- <div class="col-xs-12 col-md-12">
								
							</div> --}}
						</div>
						<div class="col-md-12 col-xs-12 box box-info"></div>
						<div class="box-footer">
							<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.update') }}</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('NewScript')
<script>
	@if(session('Mensaje'))
	$(document).ready(function(){
		NotifiTrue('{{session('Mensaje')}}');
	});
	@endif
</script>
@endsection