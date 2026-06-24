
@php
    if ($opcion['ofertado'] == 1||$opcion['en_uso'] == 1) {
        $OpcionOfertada = 1;
    }else{
        $OpcionOfertada = 0;
    }
@endphp

{{-- ingreso de inputs para el pretratamiento --}}
@php
	$tratamientoNombre = data_get($opcion, 'tratamientos.0.TratName', 'Sin tratamiento');
	$pretratamientosTratamiento = collect(data_get($opcion, 'tratamientos.0.pretratamientos', []));
	$pretratamientosSeleccionados = collect($opcion->pretratamientosSelected ?? []);
	$pretratamientosDisponibles = $pretratamientosTratamiento
		->merge($pretratamientosSeleccionados)
		->unique('ID_PreTrat')
		->values();
	$pretratamientosSeleccionadosIds = $pretratamientosSeleccionados->pluck('ID_PreTrat')->toArray();
@endphp
<div id="pretratamiento{{$contadorphp}}Container" class="panel panel-default" style="display: inline-block; overflow: hidden; width:100%; background-color:#FAFAFF;">
	{{-- <hr class="col-md-10 col-md-offset-1 align-self-center"  id="pretratsparator{{$contadorphp}}" /> --}}
	<div style="padding: 0.25em; background-color: #222d32; color: #b8c7ce" class="panel-heading">
	  <h5 class="panel-title">Tratamiento:<b style="color: #E8E8E8" id="pretratamiento{{$contadorphp}}TratName"> {{$tratamientoNombre}}</b>{{-- 	<small>Subtext for header</small> --}}</h5>
	</div>
	<div class="col-md-12" style="margin-bottom: 0.25em;">
	    <label for="pretratamiento{{$contadorphp}}">Pretratamiento</label>
	    @php
            $puedeEditar = isset($esDireccionTecnica) && $esDireccionTecnica || in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones);
        @endphp
	    <select {{$puedeEditar ? '' : 'disabled' }} multiple="multiple" class="form-control" id="pretratamiento{{$contadorphp}}" name="Opcion[{{$contadorphp}}][Pretratamientos][]">
	    	@foreach($pretratamientosDisponibles as $pretratamiento)
				<option {{ in_array($pretratamiento->ID_PreTrat, $pretratamientosSeleccionadosIds) ? 'selected' : '' }} value="{{$pretratamiento->ID_PreTrat}}">{{$pretratamiento->PreTratName}}</option>
	    	@endforeach
	    </select>
	    @foreach($pretratamientosDisponibles as $pretratamiento)
			@if(in_array($pretratamiento->ID_PreTrat, $pretratamientosSeleccionadosIds))
	    		 @if(in_array(Auth::user()->UsRol, Permisos::ComercialYJefeComercial)||in_array(Auth::user()->UsRol2, Permisos::ComercialYJefeComercial)||$OpcionOfertada==1)
	    		     <input hidden name="Opcion[{{$contadorphp}}][Pretratamientos][]" value="{{$pretratamiento->ID_PreTrat}}">
	    		 @endif
	    	@endif
	    @endforeach
	</div>
</div>
{{-- fin de ingreso de inputs para el pretratamiento --}}
