@extends('layouts.app')

@section('htmlheader_title')
{{ __('adminlte::message.areatitle') }}
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.areatitle') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">{{ __('adminlte::message.createarea') }}</h3>
					</div>
					<div class="box box-info">
						<form role="form" action="/areasInterno" method="POST" enctype="multipart/form-data" data-toggle="validator">
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
									<label for="SedeSelect">{{ __('adminlte::message.sclientsede') }}</label><small class="help-block with-errors">*</small>
									<select name="FK_AreaSede" id="SedeSelect" class="form-control select" required>
										<option value="">{{ __('adminlte::message.select') }}</option>
										@foreach($Sedes as $Sede)
											<option value="{{$Sede->SedeSlug}}">{{$Sede->SedeName}}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-xs-12 col-md-12">
									<label for="AreaName">{{ __('adminlte::message.areaname') }}</label><small class="help-block with-errors">*</small>
									<input data-minlength="5" required name="AreaName" autofocus="true" type="text" class="form-control inputText" id="AreaName" value="{{old('AreaName')}}">
								</div>
							</div>
							<div class="box box-info">
								<div class="box-footer">
									<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.register') }}</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
