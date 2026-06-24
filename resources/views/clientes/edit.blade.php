@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.clientcliente') }}
@endsection
@section('contentheader_title')
@if(Route::currentRouteName()=='clientes.edit')
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.clientmenu') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@else
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.clientcliente') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endif
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.edit') }}</h3>
				</div>
				<div class="box box-info">
					<!-- form start -->
					@if(Route::currentRouteName() === 'clientes.edit')
						<form role="form" action="/clientes/{{$cliente->CliSlug}}" method="POST" enctype="multipart/form-data"  data-toggle="validator" class="form">
					@else
						<form role="form" action="/cliente/{{$cliente->CliSlug}}/update" method="POST" enctype="multipart/form-data"  data-toggle="validator" class="form">
					@endif
						{{csrf_field()}}
						@csrf
						@method('PUT')
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
							<div class="col-md-6 form-group">
								<label for="ClienteInputNit">{{ __('adminlte::message.clientNIT') }}</label><small class="help-block with-errors">*</small>
								<input type="text" name="CliNit" class="form-control nit" id="ClienteInputNit" data-minlength="13" maxlength="13" placeholder="{{ __('adminlte::message.clientNITplacehoder') }}" required value="{{$cliente->CliNit}}">
							</div>
							{{-- <div class="col-md-6 form-group">
								<label for="ClienteInputNombre" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientnombrecorto') }}</b>" data-content="{{ __('adminlte::message.contacclientnombrecortomessage') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientnombrecorto') }}
								</label>
								<small class="help-block with-errors">*</small>
								<input type="text" name="CliShortname" class="form-control" id="ClienteInputNombre" data-minlength="2"  maxlength="100" required value="{{$cliente->CliShortname}}">
							</div> --}}
							<div class="col-md-6 form-group">
								<label for="ClienteInputRazon">{{ __('adminlte::message.clirazonsoc') }}</label><small class="help-block with-errors">*</small>
								<input type="text" name="CliName" class="form-control" id="ClienteInputRazon"  data-minlength="5"  maxlength="100" required value="{{$cliente->CliName}}">
							</div>
							<div class="col-md-6 form-group">
								<label for="CliRut" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientrut') }}</b>" data-content="{{ __('adminlte::message.clientrut-info') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientrut') }}
								</label>
								<small class="help-block with-errors"></small>
								<div class="input-group">
									<input type="file" name="CliRut" class="form-control" id="CliRut" accept=".pdf" data-accept="pdf" data-filesize="5120">
									<div class="input-group-btn ">
										<a class="{{$cliente->CliRut === null ? 'btn btn-default' : 'btn btn-success'}}">
											<i class='{{$cliente->CliRut === null ? 'fas fa-ban' : 'fas fa-file-pdf'}}'></i>
										</a>
									</div>
								</div>
							</div>
							<div class="col-md-6 form-group">
								<label for="CliCamaraComercio" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcamaracomercio') }}</b>" data-content="{{ __('adminlte::message.clientcamaracomercio-info') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientcamaracomercio') }}
								</label>
								<small class="help-block with-errors"></small>
								<div class="input-group">
									<input type="file" name="CliCamaraComercio" class="form-control" id="CliCamaraComercio" accept=".pdf" data-accept="pdf" data-filesize="5120">
									<div class="input-group-btn ">
										<a class="{{$cliente->CliCamaraComercio === null ? 'btn btn-default' : 'btn btn-success'}}">
											<i class='{{$cliente->CliCamaraComercio === null ? 'fas fa-ban' : 'fas fa-file-pdf'}}'></i>
										</a>
									</div>
								</div>
							</div>
							<div class="col-md-6 form-group">
								<label for="CliRepresentanteLegal" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientlegalrepresentative') }}</b>" data-content="{{ __('adminlte::message.clientlegalrepresentative-info') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientlegalrepresentative') }}
								</label>
								<small class="help-block with-errors"></small>
								<div class="input-group">
									<input type="file" name="CliRepresentanteLegal" class="form-control" id="CliRepresentanteLegal" accept=".pdf" data-accept="pdf" data-filesize="5120">
									<div class="input-group-btn ">
										<a class="{{$cliente->CliRepresentanteLegal === null ? 'btn btn-default' : 'btn btn-success'}}">
											<i class='{{$cliente->CliRepresentanteLegal === null ? 'fas fa-ban' : 'fas fa-file-pdf'}}'></i>
										</a>
									</div>
								</div>
							</div>
							<div class="col-md-6 form-group">
								<label for="CliCertificaionBancaria" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientbankcertification') }}</b>" data-content="{{ __('adminlte::message.clientbankcertification-info') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientbankcertification') }}
								</label>
								<small class="help-block with-errors"></small>
								<div class="input-group">
									<input type="file" name="CliCertificaionBancaria" class="form-control" id="CliCertificaionBancaria" accept=".pdf" data-accept="pdf" data-filesize="5120">
									<div class="input-group-btn ">
										<a class="{{$cliente->CliCertificaionBancaria === null ? 'btn btn-default' : 'btn btn-success'}}">
											<i class='{{$cliente->CliCertificaionBancaria === null ? 'fas fa-ban' : 'fas fa-file-pdf'}}'></i>
										</a>
									</div>
								</div>
							</div>
							<div class="col-md-6 form-group">
								<label for="CliCertificaionComercial" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcommercialcertification') }}</b>" data-content="{{ __('adminlte::message.clientcommercialcertification-info') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientcommercialcertification') }}
								</label>
								<small class="help-block with-errors"></small>
								<div class="input-group">
									<input type="file" name="CliCertificaionComercial" class="form-control" id="CliCertificaionComercial" accept=".pdf" data-accept="pdf" data-filesize="5120">
									<div class="input-group-btn ">
										<a class="{{$cliente->CliCertificaionComercial === null ? 'btn btn-default' : 'btn btn-success'}}">
											<i class='{{$cliente->CliCertificaionComercial === null ? 'fas fa-ban' : 'fas fa-file-pdf'}}'></i>
										</a>
									</div>
								</div>
							</div>
							<div class="col-md-6 form-group">
								<label for="CliCertificaionComercial2" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcommercialcertification') }} 2</b>" data-content="{{ __('adminlte::message.clientcommercialcertification-info') }}">
									<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
									{{ __('adminlte::message.clientcommercialcertification') }} 2
								</label>
								<small class="help-block with-errors"></small>
								<div class="input-group">
									<input type="file" name="CliCertificaionComercial2" class="form-control" id="CliCertificaionComercial2" accept=".pdf" data-accept="pdf" data-filesize="5120">
									<div class="input-group-btn ">
										<a class="{{$cliente->CliCertificaionComercial2 === null ? 'btn btn-default' : 'btn btn-success'}}">
											<i class='{{$cliente->CliCertificaionComercial2 === null ? 'fas fa-ban' : 'fas fa-file-pdf'}}'></i>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="box box-info">
							<div class="box-footer">
								<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.update') }}</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
