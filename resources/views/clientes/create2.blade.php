@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.clientcliente') }}
@endsection
@section('contentheader_title')
{{ __('adminlte::message.clientcliente') }}
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
                    <form role="form" action="/clientes" method="POST" enctype="multipart/form-data" data-toggle="validator">
                        {{csrf_field()}}
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
                                        <li><a href="#step-3"><b>{{ __('adminlte::message.Paso 3') }}</b><br /><small>{{ __('adminlte::message.clientpers') }}</small></a></li>
                                    </ul>
                                    <div class="row">
                                        <div id="step-1" class="tab-pane step-content">
                                            <div id="form-step-0" role="form" data-toggle="validator">
                                                <div class="col-md-6 form-group ">
                                                    <label for="ClienteInputNit">{{ __('adminlte::message.clientNIT') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" name="CliNit" class="form-control nit" id="ClienteInputNit" data-minlength="13" data-maxlength="13" placeholder="{{ __('adminlte::message.clientNITplacehoder') }}" value="{{ old('CliNit') }}" required>
                                                </div>
                                                {{-- <div class="col-md-6 form-group">
                                                    <label for="ClienteInputNombre" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientnombrecorto') }}</b>" data-content="{{ __('adminlte::message.contacclientnombrecortomessage') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientnombrecorto') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="text" name="CliShortname" class="form-control" id="ClienteInputNombre" maxlength="100" required value="{{ old('CliShortname') }}">
                                                </div> --}}
                                                <div class="col-md-6 form-group">
                                                    <label for="ClienteInputRazon">{{ __('adminlte::message.clirazonsoc') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" name="CliName" class="form-control" id="ClienteInputRazon"  maxlength="100" required value="{{ old('CliName') }}">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="CliProcedencia">Procedencia del Cliente</label><small class="help-block with-errors">*</small>
                                                    <select class="form-control select" id="CliProcedencia" name="CliProcedencia" required>
                                                        <option value="">{{ __('adminlte::message.select') }}</option>
                                                        <option value="Visita" {{ old('CliProcedencia') == 'Visita' ? 'selected' : '' }}>Visita</option>
                                                        <option value="Llamada" {{ old('CliProcedencia') == 'Llamada' ? 'selected' : '' }}>Llamada</option>
                                                        <option value="Contacto en frío" {{ old('CliProcedencia') == 'Contacto en frío' ? 'selected' : '' }}>Contacto en frío</option>
                                                        <option value="Redes Sociales" {{ old('CliProcedencia') == 'Redes Sociales' ? 'selected' : '' }}>Redes Sociales</option>
                                                        <option value="Referido" {{ old('CliProcedencia') == 'Referido' ? 'selected' : '' }}>Referido</option>
                                                        <option value="Otro" {{ old('CliProcedencia') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                                    </select>
                                                </div>
                                                {{-- <div class="col-md-6 form-group">
                                                    <label for="CliRut" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientrut') }}</b>" data-content="{{ __('adminlte::message.clientrut-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientrut') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="file" name="CliRut" class="form-control" id="CliRut" accept=".pdf" data-accept="pdf" data-filesize="5120" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="CliCamaraComercio" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcamaracomercio') }}</b>" data-content="{{ __('adminlte::message.clientcamaracomercio-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientcamaracomercio') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="file" name="CliCamaraComercio" class="form-control" id="CliCamaraComercio" accept=".pdf" data-accept="pdf" data-filesize="5120" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="CliRepresentanteLegal" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientlegalrepresentative') }}</b>" data-content="{{ __('adminlte::message.clientlegalrepresentative-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientlegalrepresentative') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="file" name="CliRepresentanteLegal" class="form-control" id="CliRepresentanteLegal" accept=".pdf" data-accept="pdf" data-filesize="5120" required>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="CliCertificaionBancaria" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientbankcertification') }}</b>" data-content="{{ __('adminlte::message.clientbankcertification-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientbankcertification') }}
                                                    </label>
                                                    <small class="help-block with-errors"></small>
                                                    <input type="file" name="CliCertificaionBancaria" class="form-control" id="CliCertificaionBancaria" accept=".pdf" data-accept="pdf" data-filesize="5120">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="CliCertificaionComercial" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcommercialcertification') }}</b>" data-content="{{ __('adminlte::message.clientcommercialcertification-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientcommercialcertification') }}
                                                    </label>
                                                    <small class="help-block with-errors"></small>
                                                    <input type="file" name="CliCertificaionComercial" class="form-control" id="CliCertificaionComercial" accept=".pdf" data-accept="pdf" data-filesize="5120">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="CliCertificaionComercial2" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.clientcommercialcertification') }} 2</b>" data-content="{{ __('adminlte::message.clientcommercialcertification-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.clientcommercialcertification') }} 2
                                                    </label>
                                                    <small class="help-block with-errors"></small>
                                                    <input type="file" name="CliCertificaionComercial2" class="form-control" id="CliCertificaionComercial2" accept=".pdf" data-accept="pdf" data-filesize="5120">
                                                </div> --}}
                                            </div>
                                        </div>
                                        <div id="step-2" class="">
                                            <div id="form-step-1" role="form" data-toggle="validator">
                                                <div class="col-md-9">
                                                    <h2>{{ __('adminlte::message.sclititleh2') }}</h2>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="sedeinputname">{{ __('adminlte::message.sclientnamesede') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control" id="sedeinputname" name="SedeName" maxlength="128" value="{{ old('SedeName') }}" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="sedeinputemail">Correo electrónico de la Sede</label><small class="help-block with-errors">*</small>
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
                                                    <small class="help-block with-errors">*</small>
                                                    <select class="form-control select" id="municipio" name="FK_SedeMun" required>
                                                        @if (isset($Municipios))
                                                            @foreach ($Municipios as $Municipio)
                                                                <option value="{{$Municipio->ID_Mun}}" {{ old('FK_SedeMun') == $Municipio->ID_Mun ? 'selected' : '' }}>{{$Municipio->MunName}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="sedeinputcelular">{{ __('adminlte::message.mobile') }}</label><small class="help-block with-errors">*</small>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">(+57)</span>
                                                        <input type="text" class="form-control mobile" id="sedeinputcelular" placeholder="{{ __('adminlte::message.mobileplaceholder') }}" name="SedeCelular" data-minlength="12" value="{{ old('SedeCelular') }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="sedeinputaddress">{{ __('adminlte::message.address') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control" id="sedeinputaddress" name="SedeAddress" placeholder="{{ __('adminlte::message.addressplaceholder') }}" minlength="5" maxlength="128" required value="{{ old('SedeAddress') }}">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label for="sedeinputphone1">{{ __('adminlte::message.phone') }}</label><small class="help-block with-errors"></small>
                                                    <input type="text" class="form-control phone tel" id="sedeinputphone1" name="SedePhone1" placeholder="{{ __('adminlte::message.phoneplaceholder') }}" data-minlength="11" value="{{ old('SedePhone1') }}">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                        <label for="sedeinputext1">{{ __('adminlte::message.ext') }}</label><small class="help-block with-errors"></small>
                                                    <input type="text" disabled class="form-control extension ext" id="sedeinputext1" name="SedeExt1" data-minlength="2" data-maxlength="5" value="{{ old('SedeExt1') }}">
                                                </div>
                                                <div id="telefono2" class="col-md-6 form-group" style="display: none;">
                                                    <label for="sedeinputphone2">{{ __('adminlte::message.phone') }} 2</label><small class="help-block with-errors"></small>
                                                    <input type="tel" class="form-control phone tel2" id="sedeinputphone2" name="SedePhone2" placeholder="{{ __('adminlte::message.phoneplaceholder') }}" data-minlength="11"  data-maxlength="11" value="{{ old('SedePhone2') }}">
                                                </div>
                                                <div id="extension2" class="col-md-6 form-group" style="display: none;">
                                                    <label for="sedeinputext2">{{ __('adminlte::message.ext') }} 2</label><small class="help-block with-errors"></small>
                                                    <input type="text" class="form-control extension ext2" id="sedeinputext2" name="SedeExt2" data-minlength="2" maxlength="5" disabled value="{{ old('SedeExt2') }}">
                                                </div>
                                                <div class="col-md-12" id="tel" style="display:flex; justify-content:center">
                                                    <a onclick="Tel()"class="btn btn-info">{{ __('adminlte::message.scliotrotelefono') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="step-3">
                                            <div id="form-step-2" role="form" data-toggle="validator">
                                                <div class="col-md-9">
                                                    <h2>{{ __('adminlte::message.personaltitleh2') }}</h2>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="AreaName" data-placement="auto" data-html="true" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.areaname') }}</b>" data-content="{{ __('adminlte::message.clientarea-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.areaname') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control inputText" id="AreaName" name="AreaName" maxlength="128" required value="{{ old('AreaName') }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="AreaName" data-placement="auto" data-html="true" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.cargoname') }}</b>" data-content="{{ __('adminlte::message.clientcargo-info') }}">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        {{ __('adminlte::message.cargoname') }}
                                                    </label>
                                                    <small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control inputText" id="CargName" name="CargName" maxlength="128" required value="{{ old('CargName') }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersDocType">{{ __('adminlte::message.persdoctype') }}</label><small class="help-block with-errors">*</small>
                                                    <select class="form-control select" id="PersDocType" name="PersDocType" required>
                                                        <option value="">{{ __('adminlte::message.select') }}</option>
                                                        <option value="CC" {{ old('PersDocType') == 'CC' ? 'selected' : '' }}>{{ __('adminlte::message.persdoctypecc') }}</option>      
                                                        <option value="CE" {{ old('PersDocType') == 'CE' ? 'selected' : '' }}>{{ __('adminlte::message.persdoctypece') }}</option>
                                                        <option value="RUT" {{ old('PersDocType') == 'RUT' ? 'selected' : '' }}>{{ __('adminlte::message.persdoctyperut') }}</option> 
                                                        <option value="NIT" {{ old('PersDocType') == 'NIT' ? 'selected' : '' }}>{{ __('adminlte::message.persdoctypenit') }}</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersDocNumber">{{ __('adminlte::message.persdocument') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control document" id="PersDocNumber" data-minlength="6" name="PersDocNumber" maxlength="15" required value="{{ old('PersDocNumber') }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersFirstName">{{ __('adminlte::message.persfirstname') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control nombres" id="PersFirstName" name="PersFirstName" maxlength="25" required value="{{ old('PersFirstName') }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersSecondName">{{ __('adminlte::message.perssecondtname') }}</label><small class="help-block with-errors"></small>
                                                    <input type="text" class="form-control nombres" id="PersSecondName" name="PersSecondName" maxlength="25" value="{{ old('PersSecondName') }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersLastName">{{ __('adminlte::message.perslastname') }}</label><small class="help-block with-errors">*</small>
                                                    <input type="text" class="form-control inputText" id="PersLastName" name="PersLastName" maxlength="64" required value="{{ old('PersLastName') }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersEmail" data-placement="auto" data-html="true" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Correo electrónico de la Persona de Contacto</b>" data-content="Este dato es importante para que la persona de contacto pueda recibir las novedades con relacion a sus residuos y solicitudes de servicio... <br>Si la persona de contacto no cuenta con una dirección de correo electrónico también puede escribir acá el correo electrónico con el que se registro al sistema <b>SisPro</b>.">
                                                        <i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>
                                                        Correo electrónico del Contacto
                                                    </label><small class="help-block with-errors">*</small>
                                                    <input type="email" class="form-control" id="PersEmail" name="PersEmail" maxlength="255" required value="{{ old('PersEmail') }}" placeholder="{{ __('adminlte::message.emailplaceholder') }}">
                                                    
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="PersCellphone">{{ __('adminlte::message.mobile') }}</label><small class="help-block with-errors">*</small>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">(+57)</span>
                                                        <input type="text" class="form-control mobile" id="PersCellphone" name="PersCellphone" placeholder="{{ __('adminlte::message.mobileplaceholder') }}" data-minlength="12"  maxlength="12" value="{{ old('PersCellphone') }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(!isset($comercialAsignado) || $comercialAsignado === null)
                                            <div class="form-group col-md-6">
                                                <label for="CliComercial" data-placement="auto" data-html="true" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Correo electrónico de la Persona de Contacto</b>" data-content="Este dato es importante para que la persona de contacto pueda recibir las novedades con relacion a sus residuos y solicitudes de servicio... <br>Si la persona de contacto no cuenta con una dirección de correo electrónico también puede escribir acá el correo electrónico con el que se registro al sistema <b>SisPro</b>.">
                                                    Comercial Asignado
                                                </label><small class="help-block with-errors">*</small>
                                                <select class="form-control select" id="CliComercial" name="CliComercial" required>
                                                    <option value="">{{ __('adminlte::message.select') }}</option>
                                                    @foreach ($comerciales as $comercial)    
                                                        <option value="{{$comercial->ID_Pers}}" 
                                                            {{ (old('CliComercial') == $comercial->ID_Pers || (isset($comercialAsignado) && $comercialAsignado == $comercial->ID_Pers)) ? 'selected' : '' }}>
                                                            {{ $comercial->PersFirstName }} {{$comercial->PersSecondName}} {{$comercial->PersLastName}}
                                                        </option>      
                                                    @endforeach
                                                </select>
                                            </div>
                                            @else
                                            {{-- Si es una comercial, ocultar el campo y asignar automáticamente --}}
                                            <input type="hidden" name="CliComercial" value="{{ $comercialAsignado }}">
                                            @endif
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
@endsection