{{--@extends('errors::illustrated-layout')--}}

@section('code', '503')
@section('title', __('Servicio no disponible'))

@section('image')
<div style="background-image: url({{ asset('/svg/mantenimiento.png') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection
@if($exception->getMessage())
	@section('message', 'Lo sentimos, estamos realizando algunas tareas de mantenimiento en este momento. Estaremos de vuelta en breve.')
@else
	@section('message', 'Lo sentimos, estamos realizando algunas tareas de mantenimiento en este momento. Estaremos de vuelta en breve.')
@endif
