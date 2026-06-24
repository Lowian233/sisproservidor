@extends('layouts.app')

@section('htmlheader_title')
	{{ __('adminlte::message.home') }}
@endsection
@section('title')
	{{ __('adminlte::message.home') }}
@endsection
@section('contentheader_title')
<div class="text-center"><h4> Bienvenid@ {{ Auth::user()->name }}</h4></div>
@endsection
@section('contentheader_description')
    @if(Auth::check() && !in_array(Auth::user()->UsRol, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']) && !in_array(Auth::user()->UsRol2, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']))
        @component('layouts.partials.modalemergente')
        @endcomponent
    @endif
    @if(Auth::check() && Auth::user()->UsRol === 'Cliente')
        @include('layouts.partials.modal-calificaciones')
    @endif
@endsection

@section('main-content')
<!-- Sección de Notificación - Imagen visible en el home (no se muestra para comerciales) -->
@if(Auth::check() && !in_array(Auth::user()->UsRol, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']) && !in_array(Auth::user()->UsRol2, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']))
<div class="row" style="margin-bottom: 20px;">
    <div class="col-xs-12">
        <div class="box box-info" style="border-top: 3px solid #00C851;">
            <div class="box-body text-center" style="padding: 15px;">
                <!-- Cambia la ruta de la imagen según la notificación que quieras mostrar -->
                
                <img src="{{ asset('/img/calificacion.png') }}" 
                     alt="Notificación" 
                     class="img-responsive" 
                     style="max-width: 80%; height: auto; margin: 0 auto; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>
@endif

@switch(Auth::user()->UsRol)
    @case('JefeLogistica')
		{{-- @include('layouts.homeroles.jefelogistica') --}}
        @break

    @case('AsistenteLogistica')
		{{-- @include('layouts.homeroles.asistentelogistica') --}}
		@break

	@case('Supervisor')
		@include('layouts.homeroles.supervisor')
        @break
    
    @case('Comercial')
    @case('Comercialap')
    @case('Ejecutivo Comercial')
        @include('layouts.homeroles.comercial-calendario')
        @break
    
    @default
        @if(in_array(Auth::user()->UsRol2, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']))
            @include('layouts.homeroles.comercial-calendario')
        @endif
@endswitch
@endsection
<!-- Default box -->
{{-- <div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Home</h3>
            <div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
				<i class="fa fa-minus"></i></button>
			<button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
				<i class="fa fa-times"></i></button>
		   </div>
	</div>
	<div class="box-body">
		{{ __('adminlte::message.logged') }}. Comienza creandote una aplicacion increible!
	</div>
	<!-- /.box-body -->
</div> --}}
<!-- /.box -->
