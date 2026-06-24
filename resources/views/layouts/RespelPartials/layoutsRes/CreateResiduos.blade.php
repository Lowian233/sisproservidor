<div id="Residuo`+contador+`">
	<div class="col-md-12">
		<hr style="height:3px; border:none; color:rgb(60,90,180); background-color:rgb(60,90,180);">
	</div>
	<div class="col-md-12">
		<label class="btn-box-tool" onclick="EliminarRes(`+contador+`)" style="float: right; color: red; margin-top: 0; font-size: 1.3em; cursor:pointer;" title="Eliminar">
			<i class="fas fa-trash-alt"></i>
		</label>
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label>{{ __('adminlte::message.name') }}</label>
		<small class="help-block with-errors">*</small>
		<input maxlength="128" name="RespelName[]" type="text" class="form-control" placeholder="Nombre del Residuo" required value="">
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.respeldescriptittle') }}" data-content="{{ __('adminlte::LangRespel.respeldescriptinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.descripcion') }}</label>
		<small class="help-block with-errors">*</small>
		<input required maxlength="512" name="RespelDescrip[]" type="text" class="form-control" placeholder="Descripcion del Residuo">
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label>{{ __('adminlte::LangRespel.estadofisico') }}</label>
		<small class="help-block with-errors">*</small>
		<select name="RespelEstado[]" class="form-control" required>
			<option value="">{{ __('adminlte::LangRespel.select') }}</option>
			<option value="{{ __('adminlte::LangRespel.estadofisico1') }}">{{ __('adminlte::LangRespel.estadofisico1') }}</option>
			<option value="{{ __('adminlte::LangRespel.estadofisico2') }}">{{ __('adminlte::LangRespel.estadofisico2') }}</option>
			<option value="{{ __('adminlte::LangRespel.estadofisico3') }}">{{ __('adminlte::LangRespel.estadofisico3') }}</option>
			<option value="{{ __('adminlte::LangRespel.estadofisico4') }}">{{ __('adminlte::LangRespel.estadofisico4') }}</option>
		</select>
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label>{{ __('adminlte::LangRespel.danger') }}</label>
		<small class="help-block with-errors">*</small>
		<select id="selectDanger`+contador+`" name="RespelIgrosidad[]" class="form-control" required>
			<option value="">{{ __('adminlte::LangRespel.select') }}</option>
			<option onclick="setNoDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger1') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger2') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger3') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger4') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger5') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger6') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger7') }}</option>
			<option onclick="setDanger(`+contador+`)">{{ __('adminlte::LangRespel.danger8') }}</option>
		</select>
	</div>
	<div class="col-md-6 form-group has-feedback" style="max-height: 2em; text-align: center;" id="danger`+contador+`" hidden="">
		<label>Tipo de clasificación</label><br>
		<a class="btn btn-success"  id="ClasifY`+contador+`" onclick="AgregarY(`+contador+`)">Y</a>
		<a class="btn btn-default"  id="ClasifA`+contador+`" onclick="AgregarA(`+contador+`)">A</a>
	</div>
	<div class="col-md-6 form-group has-feedback" id="Clasif`+contador+`" hidden="">
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.hojadeseguridad') }}</b>" data-content="{{ __('adminlte::LangRespel.hojapopoverinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.hojadeseguridad') }}</label>
		<small class="help-block with-errors">*</small>
		<input required id="hoja`+contador+`" name="RespelHojaSeguridad[]" type="file" data-filesize="10240" class="form-control" accept=".pdf">
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.tarjetaemergencia') }}</b>" data-content="{{ __('adminlte::LangRespel.tarjetapopoverinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.tarjetaemergencia') }}</label>
		<input id="tarj`+contador+`" name="RespelTarj[]" type="file" data-filesize="5120" class="form-control" accept=".pdf">
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label style="margin-bottom: 3px;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.foto') }}</b>" data-content="{{ __('adminlte::LangRespel.fotopopoverinfo') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.fotolabel') }}</label>
		<small class="help-block with-errors"></small>
		<input id="foto`+contador+`" name="RespelFoto[]" type="file" class="form-control" accept=".jpg,.png" data-filesize="5120" data-filetype="png">
		<span class="form-control-feedback fa fa-camera" style="margin-right: 1.8em;" aria-hidden="true"><span>
		{{-- <span class="far fa-building fa-fw form-control-feedback fa-pull-left" style="margin-right: 1.8em;" aria-hidden="true"></span> --}}
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.resolucion1tittle') }}" data-content="{{ __('adminlte::LangRespel.resolucion1descrip') }}">{{ __('adminlte::LangRespel.controlx') }}
					<a href="{{ __('adminlte::LangRespel.resolucion1link') }}" target="_blank">{{ __('adminlte::LangRespel.resolucion1') }}<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i></a>
				</label>
				<small class="help-block with-errors">*</small>
		<select id="selectControl`+contador+`" name="SustanciaControlada[]" class="form-control" required>
			<option value="">{{ __('adminlte::LangRespel.select') }}</option>
			<option value="0" onclick="setNoControlada(`+contador+`)">{{ __('adminlte::LangRespel.no') }}</option>
			<option value="1" onclick="setControlada(`+contador+`)">{{ __('adminlte::LangRespel.yes') }}</option>
		</select>
	</div>
	<div class="col-md-6 form-group has-feedback" id="sustanciaFormtype`+contador+`" style="text-align: center;" hidden="">
		<label style="margin-bottom: 0">Tipo de sustancia</label><br>
		<a class="btn btn-success" id="Controlada`+contador+`" onclick="AgregarControlada(`+contador+`)"> Controlada</a>
		<a class="btn btn-default" id="Masivo`+contador+`" onclick="AgregarMasivo(`+contador+`)">Uso masivo</a>
	</div>
	<div class="col-md-6 form-group has-feedback" id="sustanciaFormName`+contador+`" hidden="">
	</div>
	<div class="col-md-6 form-group has-feedback" id="sustanciaFormDoc`+contador+`" hidden="">
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="{{ __('adminlte::LangRespel.AceiteUsado') }}">
			{{ __('adminlte::LangRespel.AceiteUsado') }}
		</label>
		<small class="help-block with-errors">*</small>
		<select id="ControlSelect`+contador+`" name="AceiteUsado[]" class="form-control" required> 
			<option value="">{{ __('adminlte::LangRespel.select') }}</option>
			<option value="0" onclick="setNoAceiteUsado(`+contador+`)">{{ __('adminlte::LangRespel.no') }}</option>
			<option value="1" onclick="setAceiteUsado(`+contador+`)">{{ __('adminlte::LangRespel.yes') }}</option>
		</select>
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>tratamiento<b>" data-content="Elija el tratamiento para su residuo según lo que se acordó con el representante comercial de PROSARC S.A. ESP">
			<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Tratamiento
		</label>
		<small class="help-block with-errors">*</small>
	      <select id="selectTratamiento`+contador+`" name="RespelTratamiento[]" class="form-control select-tratamiento-dinamico" required>
			<option value="">Seleccione un Tratamiento</option>
		@foreach ($tratamientos as $tratamiento)
				@php
					$nombreGestor = ($tratamiento->ID_Trat == 1) ? 'PROSARC' : ($tratamiento->CliShortname ?? $tratamiento->CliName);
				@endphp
				<option value="{{$tratamiento->ID_Trat}}" @if(isset($termodestruccionId) && $tratamiento->ID_Trat == $termodestruccionId) selected @endif>
					{{$tratamiento->TratName}} - {{$nombreGestor}}
				</option>
			@endforeach
		</select>
	</div>
	<div class="col-md-6 form-group has-feedback">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="<b>{{ __('adminlte::LangRespel.aceptaciontittlepopover') }}</b>" data-content="{{ __('adminlte::LangRespel.aceptacioninfopopover') }}">
			<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::LangRespel.aceptacionlabel') }}
		</label>
		<small class="help-block with-errors">*</small>
		<select id="selectDdeclaracion`+contador+`" name="RespelDeclaracion[]" class="form-control" required>
			<option value="" selected>{{ __('adminlte::LangRespel.select')}}</option>
			<option value="1">{{ __('adminlte::LangRespel.yes') }}</option>
		</select>
	</div>
	<div id="NodangerNoclasf`+contador+`">
	</div>
	<div id="Nocontrol`+contador+`">
	</div>
</div>
