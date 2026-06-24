@extends('layouts.app')
@section('htmlheader_title')
Añadir Nuevo Residuos
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
	Añadir Nuevo Residuo
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div id="Generador`+contadorGenerador+`" class="box box-success col-md-12">
	<form role="form" id="AñadirNuevoResiduo" action="/solicitud-servicio/{{$SolicitudServicio->SolSerSlug}}/NuevoRespel" method="POST" enctype="multipart/form-data" data-toggle="validator">
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
	<div class="form-group col-md-16">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserselectgener') }}</b>" data-content="{{ __('adminlte::message.solserselectgenerdescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserselectgener') }}</label>
		<small class="help-block with-errors">*</small>		
		<select name="SGenerador" id="SGenerador" class="form-control" required="">
			<option value="">{{ __('adminlte::message.select') }}</option>
			@foreach($Generadors as $SGenerador)
			<option>{{$SGenerador->GenerName}}</option>
			@endforeach
		</select>
		<br>
	</div>
	<div id="residuo" class="">
		<div class="form-group col-md-16">
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Seleccione el residuo a agregar</b>" data-content="Seleccione el residuo a agregar">
				<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Seleccione el residuo a agregar
			</label>
			<small class="help-block with-errors">*</small>
			<select name="residuo-select" id="residuo-select" class="form-control" required="" onchange="mostrarInfoResiduo()">
				<option value="">{{ __('adminlte::message.select') }}</option>
				@foreach($Respels as $Respel)
				<option value="{{ $Respel->ID_Respel }}" 
						data-name="{{ $Respel->RespelName }}" 
						data-description="{{ $Respel->RespelDescrip }}"
						data-yclasif="{{ $Respel->YRespelClasf4741 ?? '' }}" 
						data-aclasif="{{ $Respel->ARespelClasf4741 ?? '' }}"
						data-danger="{{ $Respel->RespelIgrosidad }}"
						data-state="{{ $Respel->RespelEstado }}">
					{{ $Respel->RespelName }}
				</option>
				@endforeach
			</select>	
			<br>
		</div>
	</div>
	
		<!-- Información del residuo seleccionada -->
		<div id="info-residuo">
			<div class="col-md-6 form-group">
				<label>Nombre</label>
				<input type="text" id="respel-name" class="form-control" disabled>
			</div>
			<div class="col-md-6 form-group">
				<label>Descripción</label>
				<input type="text" id="respel-description" class="form-control" disabled>
			</div>
			<div class="col-md-6 form-group">
				<label>Clasificación</label>
				<input type="text" id="respel-clasificacion" class="form-control" disabled>
			</div>
			<div class="col-md-6 form-group">
				<label>Estado</label>
				<input type="text" id="respel-estado" class="form-control" disabled>
			</div>
		</div>

		<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsercantidad') }}</b>" data-content="{{ __('adminlte::message.solsercantidaddescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsercantidad') }}</label>
			<input type="number" step=".1" min="0" class="form-control numberKg" id="SolResCantiUnidad" name="SolResCantiUnidad">
		</div>

		<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserembaja') }}</b>" data-content="{{ __('adminlte::message.solserembajadescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserembaja') }}</label>
			<small class="help-block with-errors">*</small>
			<select name="SolResEmbalaje" id="SolResEmbalaje" class="form-control selectdeembalaje" required="">
				<option value="">{{ __('adminlte::message.select') }}</option>
				<option value="99" data-image="https://picsum.photos/536/354">{{ __('adminlte::message.solserembaja1') }}</option>
				<option value="98">{{ __('adminlte::message.solserembaja2') }}</option>
				<option value="97">{{ __('adminlte::message.solserembaja3') }}</option>
				<option value="96">{{ __('adminlte::message.solserembaja4') }}</option>
				<option value="95">{{ __('adminlte::message.solserembaja5') }}</option>
				<option value="94">{{ __('adminlte::message.solserembaja6') }}</option>
				<option value="93">{{ __('adminlte::message.solserembaja7') }}</option>
				<option value="92">{{ __('adminlte::message.solserembaja8') }}</option>
				<option value="91">{{ __('adminlte::message.solserembaja9') }}</option>
				<option value="90">{{ __('adminlte::message.solserembaja10') }}</option>
				<option value="89">{{ __('adminlte::message.solserembaja11') }}</option>
				<option value="88">{{ __('adminlte::message.solserembaja12') }}</option>
				<option value="87">{{ __('adminlte::message.solserembaja13') }}</option>
				<option value="86">{{ __('adminlte::message.solserembaja14') }}</option>
			</select>
		</div>
			<div class="box-footer">
				<button type="submit" class="btn btn-success pull-right">Agregar</button>
			</div>
		</form>
</div>
@endsection
<script>
	function mostrarInfoResiduo() {
        // Obtener el select y la opción seleccionada
        var select = document.getElementById('residuo-select');
        var selectedOption = select.options[select.selectedIndex];
        
        // Extraer los atributos de la opción seleccionada
        var name = selectedOption.getAttribute('data-name');
        var description = selectedOption.getAttribute('data-description');
        var yClasif = selectedOption.getAttribute('data-yclasif');
        var aClasif = selectedOption.getAttribute('data-aclasif');
        var danger = selectedOption.getAttribute('data-danger');
        var estado = selectedOption.getAttribute('data-state');

        // Actualizar los campos en el div "info-residuo"
        document.getElementById('respel-name').value = name;
        document.getElementById('respel-description').value = description;

        // Clasificación Y o A
        if (danger !== 'No peligroso') {
            if (yClasif) {
                document.getElementById('respel-clasificacion').value = "Clasificación Y: " + yClasif;
            } else if (aClasif) {
                document.getElementById('respel-clasificacion').value = "Clasificación A: " + aClasif;
            } else {
                document.getElementById('respel-clasificacion').value = "N/D";
            }
        } else {
            document.getElementById('respel-clasificacion').value = "N/A";
        }

        // Estado del residuo
        document.getElementById('respel-estado').value = estado;
    }
</script>
