@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.sclientsede') }}
@endsection
@section('contentheader_title')
{{ __('adminlte::message.sclientsede') }}
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.sclientregister') }}</h3>
				</div>
				<div class="box box-info">
					<form role="form" action="/sclientes" method="POST" enctype="multipart/form-data" data-toggle="validator">
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
							<div class="form-group col-md-6">
								<label for="sedeinputname">{{ __('adminlte::message.sclientnamesede') }}</label></label><small class="help-block with-errors">*</small>
								<input type="text" class="form-control" id="sedeinputname" name="SedeName" value="{{ old('SedeName') }}" required>
							</div>
							<div class="form-group col-md-6">
									<label for="sedeinputemail">{{ __('adminlte::message.emailaddress') }}</label></label><small class="help-block with-errors">*</small>
									<input type="email" class="form-control" id="sedeinputemail" name="SedeEmail" placeholder="{{ __('adminlte::message.emailplaceholder') }}" value="{{ old('SedeEmail') }}" required>
								</div>
							<div class="form-group col-md-6">
								<label for="departamento">{{ __('adminlte::message.departamento') }}</label></label><small class="help-block with-errors">*</small>
								<select class="form-control select" id="departamento" name="departamento" required>
									<option value="">{{ __('adminlte::message.select') }}</option>
									@foreach ($Departamentos as $Departamento)		
										<option value="{{$Departamento->ID_Depart}}" {{ old('departamento') == $Departamento->ID_Depart ? 'selected' : '' }}>{{$Departamento->DepartName}}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="municipio">{{ __('adminlte::message.municipio') }}</label><a class="load"></a>
								<select class="form-control select" id="municipio" name="FK_SedeMun">
									@if (isset($Municipios))
										@foreach ($Municipios as $Municipio)
											<option value="{{$Municipio->ID_Mun}}" {{ old('FK_SedeMun') == $Municipio->ID_Mun ? 'selected' : '' }}>{{$Municipio->MunName}}</option>
										@endforeach
									@endif
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="sedeinputaddress">{{ __('adminlte::message.address') }}</label></label><small class="help-block with-errors">*</small>
								<input type="text" class="form-control" id="sedeinputaddress" name="SedeAddress" value="{{ old('SedeAddress') }}" placeholder="{{ __('adminlte::message.addressplaceholder') }}" required>
							</div>
							<div class="form-group col-md-6">
								<label for="sedeinputcelular">{{ __('adminlte::message.mobile') }}</label></label><small class="help-block with-errors">*</small>
								<div class="input-group">
									<span class="input-group-addon">(+57)</span>
									<input type="text" class="form-control mobile" id="sedeinputcelular" name="SedeCelular" data-minlength="12" maxlength="12" placeholder="{{ __('adminlte::message.mobileplaceholder') }}" value="{{ old('SedeCelular') }}" required>
								</div>
							</div>
							<div class="col-md-6 form-group">
								<label for="sedeinputphone1">{{ __('adminlte::message.phone') }}</label><small class="help-block with-errors"></small>
								<input type="text" class="form-control phone tel" id="sedeinputphone1" name="SedePhone1" data-minlength="11" value="{{ old('SedePhone1') }}" placeholder="{{ __('adminlte::message.phoneplaceholder') }}">
							</div>
							<div class="col-md-6 form-group">
									<label for="sedeinputext1">{{ __('adminlte::message.ext') }}</label><small class="help-block with-errors"></small>
								<input type="text" class="form-control extension ext" id="sedeinputext1" name="SedeExt1" data-minlength="2" data-maxlength="5" value="{{ old('SedeExt1') }}" disabled>
							</div>
							<div id="telefono2" class="col-md-6 form-group" style="display: none;">
								<label for="sedeinputphone2">{{ __('adminlte::message.phone') }} 2</label><small class="help-block with-errors"></small>
								<input type="tel" class="form-control phone tel2" id="sedeinputphone2" name="SedePhone2" data-minlength="11"  data-maxlength="11" value="{{ old('SedePhone2') }}" placeholder="{{ __('adminlte::message.phoneplaceholder') }}">
							</div>
							<div id="extension2" class="col-md-6 form-group" style="display: none;">
								<label for="sedeinputext2">{{ __('adminlte::message.ext') }} 2</label><small class="help-block with-errors"></small>
								<input type="text" class="form-control extension ext2" id="sedeinputext2" name="SedeExt2" data-minlength="2" maxlength="5" value="{{ old('SedeExt2') }}" disabled>
							</div>
							<div class="col-md-12" id="tel" style="display:flex; justify-content:center">
								<a onclick="Tel()"class="btn btn-info">{{ __('adminlte::message.scliotrotelefono') }}</a>
							</div>
						</div>
						<div class="box box-info">
							<div class="box-footer">
								<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.register') }}</button>
							</div>
						</div>
					</form>
						<!-- /.box -->
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->
			</div>
			<!--/.col (right) -->
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
</div>
@endsection
