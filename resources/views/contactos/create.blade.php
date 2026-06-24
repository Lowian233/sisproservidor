@extends('layouts.app')
@section('htmlheader_title')
    {{ __('adminlte::message.clientcontacto') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
    {{ __('adminlte::message.clientcontacto') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
                <div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.smartwizzardtitle') }}</h3>
				</div>
                <div class="box box-info">
                    <form role="form" action="/contactos" method="POST" enctype="multipart/form-data" data-toggle="validator" class="Formcontacto" id="form">
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
                                <div class="smartwizard">
                                    <ul>
                                        <li><a href="#step-1"><b>{{ __('adminlte::message.Paso 1') }}</b><br /><small>{{ __('adminlte::message.client') }}</small></a></li>
                                        <li><a href="#step-2"><b>{{ __('adminlte::message.Paso 2') }}</b><br /><small>{{ __('adminlte::message.clientsede') }}</small></a></li>
                                    </ul>
                                    <div class="row">
                                        <div id="step-1" class="tab-pane step-content" class="Formcontacto">
                                            <div id="form-step-0" role="form" data-toggle="validator" class="Formcontacto">
                                                <div class="form-group col-md-12">
                                                    <label for="ClienteInputNit">{{ __('adminlte::message.clientNIT') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" name="CliNit" class="form-control nit" id="ClienteInputNit" data-minlength="13" data-maxlength="13" placeholder="{{ __('adminlte::message.clientNITplacehoder') }}" value="{{ old('CliNit') }}" required>
                                                </div>
                                                <div class="col-md-12 form-group">
                                                    <label for="ClienteInputRazon">{{ __('adminlte::message.clirazonsoc') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" name="CliName" class="form-control" id="ClienteInputRazon" maxlength="100" required value="{{ old('CliName') }}">
                                                </div>
                                                <div class="col-md-12 form-group">
                                                    <label for="ClienteInputNombre" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientnombrecorto') }}</b>" data-content="{{ __('adminlte::message.contacclientnombrecortomessage') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientnombrecorto') }}
                                                    </label>
                                                    <input type="text" name="CliShortname" class="form-control" id="ClienteInputNombre" maxlength="100" required value="{{ old('CliShortname') }}">
                                                </div>
                                                <div class="col-md-6 form-group"><small class="help-block with-errors">*</small>
                                                    <label for="categoria" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcategoría') }}</b>" data-content="{{ __('adminlte::message.contacclientcategoríamessage1') }}<br> <b>NOTA: </b>{{ __('adminlte::message.contacclientcategoríamessage2') }}" >
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientcategoría') }}
                                                    </label>
                                                    <select class="form-control" id="categoria" name="CliCategoria" required onchange="toggleFields()">
                                                        <option value="">{{ __('adminlte::message.select') }}</option>
                                                        <option onclick="AddVehiculo()" {{ old('CliCategoria') == __('adminlte::message.clienttransportador') ? 'selected' : '' }}>{{ __('adminlte::message.clienttransportador') }}</option>
                                                        <option onclick="NoAddVehiculo(); showTipoProveedor();" {{old('CliCategoria') == __('adminlte::message.clientproveedor') ? 'selected' : '' }}>{{ __('adminlte::message.clientproveedor') }}</option>
                                                    </select>
                                                </div>
                                                <div id="TipoProveedorDiv" class="col-md-6 form-group" style="display:none;">
                                                    <label for="CliTipoProveedor">Tipo de Proveedor</label>
                                                    <small class="help-block with-errors">*</small>
                                                    <select class="form-control" id="CliTipoProveedor" name="CliTipoProveedor">
                                                        <option value="">{{ __('adminlte::message.select') }}</option>
                                                        <option value="Gestor" {{ old('CliTipoProveedor') == 'Gestor' ? 'selected' : '' }}>Gestor</option>
                                                        <option value="Transportador" {{ old('CliTipoProveedor') == 'Transportador' ? 'selected' : '' }}>Transportador</option>
                                                        <option value="Otro" {{ old('CliTipoProveedor') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                                    </select>
                                                </div>
                                                <div id="AddVehiculoPlaca" class="col-md-6 form-group" style="display:none;">
                                                    <label for="VehicPlaca" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.vehicplaca') }}</b>" data-content="Placa de un vehiculo del Tranportador">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.vehicplaca') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="text" name="VehicPlaca" class="form-control placa" id="VehicPlaca" data-minlength="7" maxlength="7" placeholder="{{ __('adminlte::message.placaplaceholder') }}" value="{{ old('VehicPlaca') }}">
                                                </div>
                                                <div id="AddVehiculoTipo" class="col-md-6 form-group" style="display:none;">
                                                    <label for="VehicTipo" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.vehictipo') }}</b>" data-content="{{ __('adminlte::message.contacvehictipomessage') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.vehictipo') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="text" name="VehicTipo" class="form-control" id="VehicTipo" maxlength="64" value="{{ old('VehicTipo') }}">
                                                </div>
                                                <div id="AddVehiculoCapacidad" class="col-md-6 form-group" style="display:none;">
                                                    <label for="VehicCapacidad" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.vehiccapacidad') }}</b>" data-content="{{ __('adminlte::message.contacvehiccapacidadmessage') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.vehiccapacidad') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="text" name="VehicCapacidad" class="form-control numberKg" id="VehicCapacidad">
                                                </div>
                                            </div>
                                        </div>
                                        <div id="step-2">
                                            <div id="form-step-1" role="form" data-toggle="validator">
                                                <div class="col-md-9">
                                                    <h2>{{ __('adminlte::message.sclientsede') }}</h2>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="sedeinputname">{{ __('adminlte::message.sclientnamesede') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control" id="sedeinputname" name="SedeName" data-maxlength="128" value="{{ old('SedeName') }}" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="sedeinputemail">{{ __('adminlte::message.email') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="email" class="form-control" id="sedeinputemail" placeholder="{{ __('adminlte::message.emailplaceholder') }}" name="SedeEmail" maxlength="128" value="{{ old('SedeEmail') }}" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="departamento">{{ __('adminlte::message.departamento') }}</label><small class="help-block with-errors">*</small>
                                                    <select class="form-control select" id="departamento" name="departamento" required data-dependent="FK_SedeMun">
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
                                                <div class="col-md-6 form-group">
                                                    <label for="sedeinputcelular">{{ __('adminlte::message.mobile') }}</label><small class="help-block with-errors">*</small>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">(+57)</span>
                                                        <input type="text" class="form-control mobile" id="sedeinputcelular" placeholder="{{ __('adminlte::message.mobileplaceholder') }}" name="SedeCelular" data-minlength="12" value="{{ old('SedeCelular') }}">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="sedeinputaddress">{{ __('adminlte::message.address') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control" id="sedeinputaddress" name="SedeAddress"  maxlength="128" required value="{{ old('SedeAddress') }}" placeholder="{{ __('adminlte::message.addressplaceholder') }}">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="sedeinputphone1">{{ __('adminlte::message.phone') }}</label><small class="help-block with-errors"></small>
                                                    <input type="text" class="form-control phone tel" id="sedeinputphone1" name="SedePhone1" data-minlength="11" placeholder="{{ __('adminlte::message.phoneplaceholder') }}" value="{{ old('SedePhone1') }}">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="sedeinputext1">{{ __('adminlte::message.ext') }}</label><small class="help-block with-errors"></small>
                                                    <input type="text" disabled class="form-control extension ext" id="sedeinputext1" name="SedeExt1" data-minlength="2" data-maxlength="5" value="{{ old('SedeExt1') }}">
                                                </div>
                                                <div id="telefono2" class="col-md-6 form-group" style="display: none;">
                                                    <label for="sedeinputphone2">{{ __('adminlte::message.phone') }} 2</label><small class="help-block with-errors"></small>
                                                    <input type="tel" class="form-control phone tel2" id="sedeinputphone2" name="SedePhone2" data-minlength="11" placeholder="{{ __('adminlte::message.phoneplaceholder') }}" data-maxlength="11" value="{{ old('SedePhone2') }}">
                                                </div>
                                                <div id="extension2" class="col-md-6 form-group" style="display: none;">
                                                    <label for="sedeinputext2">{{ __('adminlte::message.ext') }} 2</label><small class="help-block with-errors"></small>
                                                    <input type="text" class="form-control extension ext2" id="sedeinputext2" name="SedeExt2" data-minlength="2" maxlength="5" disabled value="{{ old('SedeExt2') }}">
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
                    </form>
                </div>
			</div>
		</div>
	</div>
</div>
@section('NewScript')
@if(old('VehicTipo') !== null)
	<script>
		$(document).ready(function(){
			Vehiculo();
		});
	</script>
@endif
@if(old('CliCategoria') == __('adminlte::message.clientproveedor'))
	<script>
		$(document).ready(function(){
			showTipoProveedor();
		});
	</script>
@endif
<script>
	function showTipoProveedor() {
		document.getElementById('TipoProveedorDiv').style.display = 'block';
		$('#CliTipoProveedor').prop('required', true);
	}
	function hideTipoProveedor() {
		document.getElementById('TipoProveedorDiv').style.display = 'none';
		$('#CliTipoProveedor').prop('required', false);
		$('#CliTipoProveedor').val('');
	}
	function toggleFields() {
		var categoria = document.getElementById('categoria').value;
		if (categoria == '{{ __('adminlte::message.clientproveedor') }}') {
			showTipoProveedor();
			NoAddVehiculo();
		} else if (categoria == '{{ __('adminlte::message.clienttransportador') }}') {
			hideTipoProveedor();
			AddVehiculo();
		} else {
			hideTipoProveedor();
			NoAddVehiculo();
		}
	}
</script>
@endsection
@endsection