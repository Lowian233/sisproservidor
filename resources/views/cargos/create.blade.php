@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.cargotitle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.cargotitle') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{__('adminlte::message.createcargo')}}</h3>
				</div>
				<div class="box box-info">

					<form role="form" action="/cargos" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@csrf
						@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
										<p>{{$error}}</p>
									@endforeach
								</ul>
							</div>
						@endif
						<div class="box-body">
							<div class="form-group col-xs-12 col-md-12">
								<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::message.cargoareatittle') }}" data-content="{{ __('adminlte::message.cargoareainfo') }}" for="AreaSelect"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{__('adminlte::message.inputarea')}}</label>
								<small class="help-block with-errors">*</small>
								<select name="CargArea" required id="AreaSelect" class="form-control select">
									<option value="">{{__('adminlte::message.select')}}</option>
									@foreach($Areas as $Area)
										<option value="{{$Area->AreaSlug}}">{{$Area->AreaName}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group col-xs-12 col-md-12">
								<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::message.cargonametittle') }}" data-content="{{ __('adminlte::message.cargonameinfo') }}" for="NombreCargo"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{__('adminlte::message.cargoname')}}</label>
								<small class="help-block with-errors">*</small>
								<input data-minlength="5" required name="CargName" autofocus="true" type="text" class="form-control inputText" id="NombreCargo" value="{{old('CargName')}}">
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
