@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.gener') }}
@endsection
@section('contentheader_title')
@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE))
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.gener') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@else
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.gener') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endif
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{ __('adminlte::message.Generregistertittle') }}</h3>
				</div>
				<div class="box box-info">
					<!-- form start -->
					<form role="form" action="/generadores" method="POST" enctype="multipart/form-data" data-toggle="validator">
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
						<div class="box-body" id="readyTable">
							<div class="tab-pane" id="addRowWizz">
								{{-- <div class="smartwizard" style="box-shadow:jg3px 3px 5px grey;"> --}}
								<div class="smartwizard" >
									<ul>
										<li><a href="#step-1"><b>{{ __('adminlte::message.Paso 1') }}</b><br/><small>{{ __('adminlte::message.client') }}</small></a></li>
										<li><a href="#step-2"><b>{{ __('adminlte::message.Paso 2') }}</b><br/><small>{{ __('adminlte::message.clientsede') }}</small></a></li>
									</ul>
									<div>
										<div id="step-1" class="tab-pane step-content">
											<div id="form-step-0" role="form" data-toggle="validator">
												<div class="col-md-12 form-group">
													<label for="FK_GenerCli" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.MenuSedes') }}</b>" data-content="{{ __('adminlte::message.misSedes-gener') }}">
														<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
														{{ __('adminlte::message.MenuSedes') }}
													</label>
													<small class="help-block with-errors">*</small>
													<select name="FK_GenerCli" class="form-control select" id="GenerInputTipo" required>
														<option value="">{{ __('adminlte::message.select') }}</option>
														@foreach($Sedes as $Sede)
															<option value="{{$Sede->SedeSlug}}">{{$Sede->SedeName}}</option>
														@endforeach()
													</select>
												</div>
												<div class="col-md-12 form-group">
													<label for="GenerInputNit">{{ __('adminlte::message.clientNIT') }} </label>
													<small class="help-block with-errors">*</small>
													<input class="form-control nit" data-minlength="13" maxlength="13"  name="GenerNit" autofocus="true" type="text" id="GenerInputNit" placeholder="{{ __('adminlte::message.clientNITplacehoder') }}" value="{{ old('GenerNit') }}" required>
												</div>
												<div class="col-md-12 form-group">
													<label for="GenerInputRazon">{{ __('adminlte::message.clirazonsoc') }}</label>
													<small class="help-block with-errors">*</small>
													<input name="GenerName" type="text" class="form-control" id="GenerInputRazon" value="{{ old('GenerName') }}" required>
												</div>
												{{-- <div class="col-md-12 form-group">
													<label for="GenerShortname" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientnombrecorto') }}</b>" data-content="{{ __('adminlte::message.contacclientnombrecortomessage') }}">
														<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
														{{ __('adminlte::message.clientnombrecorto') }}
													</label>
													<small class="help-block with-errors">*</small>
													<input name="GenerShortname" type="text" id="GenerInputNombre" class="form-control" value="{{ old('GenerShortname') }}" required>
												</div> --}}
												<div class="col-md-6 form-group">
													<label for="GenerCode" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.genercode') }}</b>" data-content="{{ __('adminlte::message.code-gener') }}">
														<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
														{{ __('adminlte::message.genercode') }}
													</label>
													<small class="help-block with-errors"></small>
													<input name="GenerCode" type="text" class="form-control" id="GenerCode" value="{{ old('GenerCode') }}">
												</div>
												<div class="col-md-6 form-group">
													<label for="Respels" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.MenuRespel') }}</b>" data-content="{{ __('adminlte::message.respels-gener') }}">
														<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
														{{ __('adminlte::message.MenuRespel') }}
													</label>
													<select class="form-control select-multiple" id="Respels" name="FK_Respel[]" multiple>
														@foreach ($Respels as $Respel)
															<option value="{{$Respel->RespelSlug}}">{{$Respel->RespelName}}</option>
														@endforeach
													</select>
												</div>
											</div>
										</div>
										<div id="step-2" class="tab-pane step-content">
											<div id="form-step-1" role="form" data-toggle="validator">
												<div class="col-md-12">
													<h2>{{ __('adminlte::message.SGenertitle') }}</h2>
													<div class="form-group col-md-6">
														<label for="GSedeinputname">{{ __('adminlte::message.sclientnamesede') }}</label>
														<small class="help-block with-errors">*</small>
														<input type="text" class="form-control" id="GSedeinputname" name="GSedeName" value="{{ old('GSedeName') }}" required>
													</div>
													<div class="col-md-6 form-group">
														<label for="GSedeinputemail">{{ __('adminlte::message.emailaddress') }}</label>
														<small class="help-block with-errors">*</small>
														<input type="email" name="GSedeEmail" class="form-control" id="GSedeinputemail" placeholder="{{ __('adminlte::message.emailplaceholder') }}" value="{{ old('GSedeEmail') }}" required>
													</div>
													<div class="col-md-6 form-group">
														<label for="departamento">{{ __('adminlte::message.departamento') }}</label>
														<small class="help-block with-errors">*</small>
														<select class="form-control select" id="departamento" name="departamento" required>
															<option value="">{{ __('adminlte::message.select') }}</option>
															@foreach ($Departamentos as $Departamento)
																<option value="{{$Departamento->ID_Depart}}">{{$Departamento->DepartName}}</option>
															@endforeach
														</select>
													</div>
													<div class="col-md-6 form-group">
														<label for="municipio">{{ __('adminlte::message.municipio') }}</label><a class="load"></a>
														<select class="form-control select" id="municipio" name="FK_GSedeMun">
															@if (isset($Municipios))
																@foreach ($Municipios as $Municipio)
																	<option value="{{$Municipio->ID_Mun}}">{{$Municipio->MunName}}</option>
																@endforeach
															@endif
														</select>
													</div>
													<div class="col-md-6 form-group">
														<label for="GSedeinputaddress">{{ __('adminlte::message.address') }}</label>
														<small class="help-block with-errors">*</small>
														<input type="text" class="form-control" id="GSedeinputaddress" name="GSedeAddress" value="{{ old('GSedeAddress') }}" placeholder="{{ __('adminlte::message.addressplaceholder') }}" required>
													</div>
													<div class="col-md-6 form-group">
														<label for="GSedeinputcelular">{{ __('adminlte::message.mobile') }}</label>
														<small class="help-block with-errors">*</small>
														<div class="input-group">
															<span class="input-group-addon">(+57)</span>
															<input type="text" class="form-control mobile" id="GSedeinputcelular" name="GSedeCelular" placeholder="{{ __('adminlte::message.mobileplaceholder') }}" data-minlength="12" maxlength="12" value="{{ old('GSedeCelular') }}" required>
														</div>
													</div>
													<div class="col-md-6 form-group">
														<label for="GSedeinputphone1">{{ __('adminlte::message.phone') }}</label>
														<small class="help-block with-errors"></small>
														<input type="tel" class="form-control phone tel" id="GSedeinputphone1" name="GSedePhone1" data-minlength="11" maxlength="11" value="{{ old('GSedePhone1') }}" placeholder="{{ __('adminlte::message.phoneplaceholder') }}">
													</div>
													<div class="col-md-6 form-group">
														<label for="GSedeinputext1">{{ __('adminlte::message.ext') }}</label>
														<small class="help-block with-errors"></small>
														<input type="text" class="form-control extension ext" id="GSedeinputext1" name="GSedeExt1" data-minlength="2" maxlength="5" value="{{ old('GSedeExt1') }}" disabled>
													</div>
													<div id="telefono2" class="col-md-6 form-group" style="display: none;">
														<label for="GSedeinputphone2">{{ __('adminlte::message.phone') }} 2</label>
														<small class="help-block with-errors"></small>
														<input type="tel" class="form-control phone tel2" id="GSedeinputphone2" name="GSedePhone2" data-minlength="11" maxlength="11" value="{{ old('GSedePhone2') }}" placeholder="{{ __('adminlte::message.phoneplaceholder') }}">
													</div>
													<div id="extension2" class="col-md-6 form-group" style="display: none;">
														<label for="GSedeinputext2">{{ __('adminlte::message.ext') }} 2</label> 
														<small class="help-block with-errors"></small>
														<input type="text" class="form-control extension ext2" id="GSedeinputext2" name="GSedeExt2" data-minlength="2" maxlength="5" value="{{ old('GSedeExt2') }}" disabled>
													</div>
													<div class="col-md-12" id="tel" style="display:flex; justify-content:center">
														<a onclick="Tel()"class="btn btn-info">{{ __('adminlte::message.scliotrotelefono') }}</a>
													</div>
												</div>
												<div class="box-footer">
													<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.register') }}</button>
												</div>
											</div>
										</div>
									</div>
								</div>	
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
