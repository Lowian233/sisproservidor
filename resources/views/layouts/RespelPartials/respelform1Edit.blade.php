<div id="Respels">
	<div id="Residuo">
		{{-- <div id="form-step-0" role="form" data-toggle="validator"> --}}
			<div class="col-md-6 form-group has-feedback">
				<label>{{ __('adminlte::message.name') }}</label>
				<input maxlength="128" name="RespelName" type="text" class="form-control" placeholder="Nombre del Residuo" required value="{{$Respels->RespelName}}">
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.respeldescriptittle') }}" data-content="{{ __('adminlte::LangRespel.respeldescriptinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.descripcion') }}</label>
				<input required maxlength="512" name="RespelDescrip" type="text" class="form-control" placeholder="Descripcion del Residuo" value="{{$Respels->RespelDescrip}}">
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label>{{ __('adminlte::LangRespel.estadofisico') }}</label>
				<select name="RespelEstado" class="form-control" required>
					<option value="">{{ __('adminlte::LangRespel.select') }}</option>
					<option {{ ($Respels->RespelEstado === __('adminlte::LangRespel.estadofisico1') ? "selected" : "" )}} value="{{ __('adminlte::LangRespel.estadofisico1') }}">{{ __('adminlte::LangRespel.estadofisico1') }}</option>
					<option {{ ($Respels->RespelEstado === __('adminlte::LangRespel.estadofisico2') ? "selected" : "" )}} value="{{ __('adminlte::LangRespel.estadofisico2') }}">{{ __('adminlte::LangRespel.estadofisico2') }}</option>
					<option {{ ($Respels->RespelEstado === __('adminlte::LangRespel.estadofisico4') ? "selected" : "" )}} value="{{ __('adminlte::LangRespel.estadofisico4') }}">{{ __('adminlte::LangRespel.estadofisico4') }} (lodos y similares)</option>
					<option {{ ($Respels->RespelEstado === __('adminlte::LangRespel.estadofisico3') ? "selected" : "" )}} value="{{ __('adminlte::LangRespel.estadofisico3') }}">{{ __('adminlte::LangRespel.estadofisico3') }}</option>
					<option {{ ($Respels->RespelEstado === __('adminlte::LangRespel.estadofisico5') ? "selected" : "" )}} value="{{ __('adminlte::LangRespel.estadofisico5') }}">{{ __('adminlte::LangRespel.estadofisico5') }}</option>
				</select>
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label>{{ __('adminlte::LangRespel.danger') }}</label>
				<select id="selectDanger0" name="RespelIgrosidad" class="form-control" required>
					<option value="">{{ __('adminlte::LangRespel.select')}}</option>

					<option value = "No peligroso" {{ ($Respels->RespelIgrosidad === 'No peligroso' ? 'selected' : '' )}}>
						{{ __('adminlte::LangRespel.danger1') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger2')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger2') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger2') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger3')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger3') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger3') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger4')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger4') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger4') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger5')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger5') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger5') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger6')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger6') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger6') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger7')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger7') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger7') }}
					</option>

					<option value = "{{ __('adminlte::LangRespel.danger8')}}" {{ ($Respels->RespelIgrosidad === __('adminlte::LangRespel.danger8') ? 'selected' : '') }}>
						{{ __('adminlte::LangRespel.danger8') }}
					</option>

				</select>
			</div>
			@if($Respels->RespelIgrosidad !== 'No peligroso')
			<div class="col-md-6 form-group has-feedback" style="max-height: 2em; text-align: center;" id="danger0">
				<label>{{ __('adminlte::LangRespel.tipoclasificacion') }}</label><br>
				@if(is_null($Respels->ARespelClasf4741))
				<a class="btn btn-success" id="ClasifY0" onclick="AgregarY(0)">Y</a>
				<a class="btn btn-default" id="ClasifA0" onclick="AgregarA(0)">A</a>
				@else
				<a class="btn btn-default" id="ClasifY0" onclick="AgregarY(0)">Y</a>
				<a class="btn btn-success" id="ClasifA0" onclick="AgregarA(0)">A</a>
				@endif
			</div>
			<div class="col-md-6 form-group has-feedback" id="Clasif0">
				@if(is_null($Respels->ARespelClasf4741))
					@include('layouts.RespelPartials.layoutsRes.ClasificacionYEdit')
				@else
					@include('layouts.RespelPartials.layoutsRes.ClasificacionAEdit')
				@endif
			</div>
			@else
			<div class="col-md-6 form-group has-feedback" style="max-height: 2em; text-align: center;" id="danger0" hidden="">
				<label>{{ __('adminlte::LangRespel.tipoclasificacion') }}</label><br>
				<a class="btn btn-success"  id="ClasifY0" onclick="AgregarY(0)">Y</a>
				<a class="btn btn-default"  id="ClasifA0" onclick="AgregarA(0)">A</a>
			</div>
			<div class="col-md-6 form-group has-feedback" id="Clasif0" hidden="">
			</div>
			@endif
			{{-- input de la hoja de seguridad --}}
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.hojadeseguridad') }}</b>" data-content="{{ __('adminlte::LangRespel.hojapopoverinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.hojadeseguridad') }}</label>
				<small class="help-block with-errors">*</small>
				@if($Respels->RespelHojaSeguridad !== 'RespelHojaDefault.pdf')
				<div class="input-group">
					<input id="hoja0" name="RespelHojaSeguridad" type="file" data-filesize="10240" class="form-control" data-accept="pdf" accept=".pdf">
					<div class="input-group-btn">
						<a method='get' href='/img/HojaSeguridad/{{$Respels->RespelHojaSeguridad}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
					</div>
				</div>
				@else
					@if($Respels->RespelIgrosidad !== 'No peligroso')
					<div class="input-group">
						<input required id="hoja0" name="RespelHojaSeguridad" type="file" data-filesize="10240" class="form-control" data-accept="pdf" accept=".pdf">
						<div class="input-group-btn">
							<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
						</div>
					</div>
					@else
					<div class="input-group">
						<input id="hoja0" name="RespelHojaSeguridad" type="file" data-filesize="10240" class="form-control" data-accept="pdf" accept=".pdf">
						<div class="input-group-btn">
							<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
						</div>
					</div>
					@endif
				@endif
			</div>
			{{-- input de la tarjeta de emergencia --}}
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.tarjetaemergencia') }}</b>" data-content="{{ __('adminlte::LangRespel.tarjetapopoverinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.tarjetaemergencia') }}</label>
				<small class="help-block with-errors">*</small>
				@if($Respels->RespelTarj!=='RespelTarjetaDefault.pdf')
				<div class="input-group">
					<input name="RespelTarj" type="file" data-filesize="5120" class="form-control" data-accept="pdf" accept=".pdf">
					<div class="input-group-btn">
						<a method='get' href='/img/TarjetaEmergencia/{{$Respels->RespelTarj}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
					</div>
				</div>
				@else
				<div class="input-group">
					<input required name="RespelTarj" type="file" data-filesize="5120" class="form-control" data-accept="pdf" accept=".pdf">
					<div class="input-group-btn">
						<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
					</div>
				</div>	
				@endif
			</div>
			{{-- input de la foto del residuo --}}
			<div class="col-md-6 form-group has-feedback">
				<label style="margin-bottom: 3px;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.foto') }}</b>" data-content="{{ __('adminlte::LangRespel.fotopopoverinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.fotolabel') }}</label>
				<small class="help-block with-errors"></small>
				{{-- <input id="foto0" name="RespelFoto" type="file" class="form-control" accept=".jpg,.png" data-filesize="2048" data-filetype="png">
				<span class="form-control-feedback fa fa-camera" style="margin-right: 1.8em;" aria-hidden="true"><span> --}}
				@if($Respels->RespelFoto!=='RespelFotoDefault.png')
				<div class="input-group">
					<input id="foto0" name="RespelFoto" type="file" class="form-control" data-accept="jpg, jpeg, png" accept=".jpg,.jpeg,.png" data-filesize="5120" data-filetype="png">
					<div class="input-group-btn">
						<a method='get' href='/img/fotoRespelCreate/{{$Respels->RespelFoto}}' target='_blank' class='btn btn-success'><i class='fas fa-image fa-lg'></i></a>
					</div>
				</div>
				@else
				<div class="input-group">
					<input id="foto0" name="RespelFoto" type="file" class="form-control" data-accept="jpg, jpeg, png" accept=".jpg,.jpeg,.png" data-filesize="5120" data-filetype="png">
					<div class="input-group-btn">
						<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
					</div>
				</div>
				@endif
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.resolucion1tittle') }}" data-content="{{ __('adminlte::LangRespel.resolucion1descrip') }}">{{ __('adminlte::LangRespel.controlx') }}
					<a href="{{ __('adminlte::LangRespel.resolucion1link') }}" target="_blank">{{ __('adminlte::LangRespel.resolucion1') }}<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i></a>
				</label>
				<select id="selectControl0" name="SustanciaControlada" class="form-control" required>
					<option value="">{{ __('adminlte::LangRespel.select') }}</option>
					<option value="0" {{ ($Respels->SustanciaControlada === 0 ? 'selected' : '') }}>{{ __('adminlte::LangRespel.no') }}</option>
					<option value="1" {{ ($Respels->SustanciaControlada === 1 ? 'selected' : '') }}>{{ __('adminlte::LangRespel.yes') }}</option>
				</select>
			</div>
			<div class="col-md-6 form-group has-feedback" id="sustanciaFormtype0" style="text-align: center;" {{$Respels->SustanciaControlada === 1 ? '' : 'hidden'}}>
				<label style="margin-bottom: 0">Tipo de sustancia</label><br>
				<a class="btn btn-{{$Respels->SustanciaControladaTipo === 0 ? 'success' : 'default'}}" id="Controlada0" onclick="AgregarControlada(0)"> Controlada</a>
				<a class="btn btn-{{$Respels->SustanciaControladaTipo === 1 ? 'success' : 'default'}}" id="Masivo0" onclick="AgregarMasivo(0)">Uso masivo</a>
			</div>
			<div class="col-md-6 form-group has-feedback" id="sustanciaFormName0" {{$Respels->SustanciaControlada === 1 ? '' : 'hidden'}}>
				@if($Respels->SustanciaControladaTipo === 0)
					@include('layouts.RespelPartials.layoutsRes.ControladaEditName')
				@else
					@include('layouts.RespelPartials.layoutsRes.MasivoEditName')
				@endif
			</div>
			<div class="col-md-6 form-group has-feedback" id="sustanciaFormDoc0" {{$Respels->SustanciaControlada === 1 ? '' : 'hidden'}}>
				@if($Respels->SustanciaControladaTipo === 0)
					@include('layouts.RespelPartials.layoutsRes.ControladaEditDoc')
				@else
					@include('layouts.RespelPartials.layoutsRes.MasivoEditDoc')
				@endif
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.AceiteUsado') }}">
					{{ __('adminlte::LangRespel.AceiteUsado') }}
				</label>
				<small class="help-block with-errors">*</small>
				<select id="ControlSelect0" name="AceiteUsado" class="form-control" required>
					<option value="">{{ __('adminlte::LangRespel.select') }}</option>
					<option value="0" {{ ($Respels->AceiteUsado === 0 ? 'selected' : '') }}>{{ __('adminlte::LangRespel.no') }}</option>
					<option value="1" {{ ($Respels->AceiteUsado === 1 ? 'selected' : '') }}>{{ __('adminlte::LangRespel.yes') }}</option>
				</select>
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>tratamiento<b>" data-content="Elija el tratamiento para su residuo según lo que se acordó con el representante comercial de PROSARC S.A. ESP">
					<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Tratamiento
				</label>
				<small class="help-block with-errors">*</small>
				<select id="selectTratamiento" name="RespelTratamiento" class="form-control" required>
					<option value="">Seleccione un Tratamiento</option>
					@foreach ($tratamientos as $tratamiento)
						@php
							$nombreGestor = ($tratamiento->ID_Trat == 1) ? 'PROSARC' : ($tratamiento->CliShortname ?? $tratamiento->CliName);
						@endphp
						<option value="{{$tratamiento->ID_Trat}}">{{$tratamiento->TratName}} - {{$nombreGestor}}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 form-group has-feedback">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.aceptaciontittlepopover') }}" data-content="{{ __('adminlte::LangRespel.aceptacioninfopopover') }}">
					<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.aceptacionlabel') }}
				</label>
				<select id="selectDdeclaracion0" name="RespelDeclaracion" class="form-control" required>
					<option value="" selected>{{ __('adminlte::LangRespel.select')}}</option>
					<option value="1">{{ __('adminlte::LangRespel.yes') }}</option>
				</select>
			</div>
			{{--
		</div> --}}
	</div>
</div>
<script>
var contador = 1;

$(document).ready(function() {
	// Event listener para el select de peligrosidad
	$('#selectDanger0').on('change', function() {
		var value = $(this).val();
		if (value === 'No peligroso') {
			setNoDanger(0);
		} else if (value !== '') {
			setDanger(0);
		}
	});
	
	// Event listener para el select de sustancia controlada
	$('#selectControl0').on('change', function() {
		var value = $(this).val();
		if (value === '0') {
			setNoControlada(0);
		} else if (value === '1') {
			setControlada(0);
		}
	});
	
	@if(!is_null($Respels->SustanciaControladaNombre))
			setControlada(0);
			@if($Respels->SustanciaControladaTipo == 0)
				AgregarControlada(0);
			@else
				AgregarMasivo(0);
			@endif
	@endif
});

function attachPopover() {
	$('[data-toggle="popover"]').popover({
		html: true,
		trigger: 'hover',
		placement: 'auto',
	});
}
function setDanger(id) {
	AgregarY(id);
	$("#danger" + id).removeAttr("hidden");
	$("#Clasif" + id).removeAttr("hidden");
	$("#myform").validator('destroy');
	$("#hoja" + id).prop('required', true);
	$("#myform").validator('update');
	attachPopover();
}

function setNoDanger(id) {
	$("#danger" + id).attr("hidden", true);
	$("#Clasif" + id).attr("hidden", true);
	$("#Clasif" + id+" > select").prop('required', false);
	$("#hoja" + id).prop('required', false);
	$("#myform").validator('destroy');
	$("#myform").validator('update');
}

function setControlada(id) {
	AgregarControlada(id);
	$("#sustanciaFormtype" + id).removeAttr('hidden');
	$("#sustanciaFormName" + id).removeAttr('hidden');
	$("#sustanciaFormFile" + id).prop('required', true);
	$("#sustanciaFormDoc" + id).removeAttr('hidden');
	$("#sustanciaFormName" + id + " > select").prop('required', true);
	$("#myform").validator('update');
	attachPopover();
}

function setNoControlada(id) {
	$("#sustanciaFormtype" + id).attr("hidden", true);
	$("#sustanciaFormName" + id).attr("hidden", true);
	$("#sustanciaFormFile" + id).prop('required', false);
	$("#sustanciaFormDoc" + id).attr("hidden", true);
	$("#sustanciaFormName" + id + " > select").prop('required', false);
	$("#myform").validator('update');
}


function AgregarY(id) {
	var ClasifY = `@include('layouts.RespelPartials.layoutsRes.ClasificacionYEdit')`;
	$("#ClasifY" + id).removeClass("btn-default");
	$("#ClasifY" + id).addClass("btn-success");
	$("#ClasifA" + id).removeClass("btn-success");
	$("#ClasifA" + id).addClass("btn-default");
	$("#Clasif" + id).empty();
	$("#Clasif" + id).append(ClasifY);
	$("#myform").validator('update');
	attachPopover();
	Selects();
}

function AgregarA(id) {
	var ClasifA = `@include('layouts.RespelPartials.layoutsRes.ClasificacionAEdit')`;
	$("#ClasifA" + id).removeClass("btn-default");
	$("#ClasifA" + id).addClass("btn-success");
	$("#ClasifY" + id).removeClass("btn-success");
	$("#ClasifY" + id).addClass("btn-default");
	$("#Clasif" + id).empty();
	$("#Clasif" + id).append(ClasifA);
	$("#myform").validator('update');
	attachPopover();
	Selects();
}

function AgregarControlada(id) {
	var ControladaName = `@include('layouts.RespelPartials.layoutsRes.ControladaEditName')`;
	var ControladaDoc = `@include('layouts.RespelPartials.layoutsRes.ControladaEditDoc')`;
	$("#Controlada" + id).removeClass("btn-default");
	$("#Controlada" + id).addClass("btn-success");
	$("#Masivo" + id).removeClass("btn-success");
	$("#Masivo" + id).addClass("btn-default");
	$("#sustanciaFormDoc" + id).empty();
	$("#sustanciaFormDoc" + id).append(ControladaDoc);
	$("#sustanciaFormName" + id).empty();
	$("#sustanciaFormName" + id).append(ControladaName);
	$("#myform").validator('update');
	attachPopover();
	Selects();
}

function AgregarMasivo(id) {
	var MasivoName = `@include('layouts.RespelPartials.layoutsRes.MasivoEditName')`;
	var MasivoDoc = `@include('layouts.RespelPartials.layoutsRes.MasivoEditDoc')`;
	$("#Masivo" + id).removeClass("btn-default");
	$("#Masivo" + id).addClass("btn-success");
	$("#Controlada" + id).removeClass("btn-success");
	$("#Controlada" + id).addClass("btn-default");
	$("#sustanciaFormDoc" + id).empty();
	$("#sustanciaFormDoc" + id).append(MasivoDoc);
	$("#sustanciaFormName" + id).empty();
	$("#sustanciaFormName" + id).append(MasivoName);
	$("#myform").validator('update');
	attachPopover();
	Selects();
}

</script>