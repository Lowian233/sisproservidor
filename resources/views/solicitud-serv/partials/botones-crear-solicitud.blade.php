@php
$usuario = Auth::user();
$puedeRectificarCliente = in_array($usuario->UsRol, Permisos::COMERCIALES, true)
    || in_array($usuario->UsRol2, Permisos::COMERCIALES, true)
    || in_array($usuario->UsRol, Permisos::COMERCIALEINGRURNO, true)
    || in_array($usuario->UsRol2, Permisos::COMERCIALEINGRURNO, true);
$puedeSolicitarPlanta = (in_array($usuario->UsRol, Permisos::PUEDE_SOLICITAR_PLANTA, true)
    || in_array($usuario->UsRol2, Permisos::PUEDE_SOLICITAR_PLANTA, true))
    && !$puedeRectificarCliente;
@endphp
@if($puedeRectificarCliente)
<a href="{{ url('solicitud-servicio/createit') }}" class="btn btn-primary pull-right">{{ __('adminlte::message.create') }}</a>
@endif
@if(($mostrarCreatePlanta ?? true) && $puedeSolicitarPlanta)
<a href="{{ url('solicitud-servicio/create') }}" class="btn btn-primary pull-right"@if(!empty($margenPlanta)) style="margin-right: 5px;"@endif>{{ __('adminlte::message.create') }}</a>
@endif
