{{-- @php
    $url = url("/solicitud-servicio/{$SolicitudServicio->SolSerSlug}");
    $nameButton = 'Ver Solicitud de Servicio';
@endphp --}}
@component('mail::message')
# Solicitud de Servicio N° {{$SolicitudServicio->ID_SolSer}}

@php
$clienteNombre = data_get($SolicitudServicio, 'cliente.CliName', 'Cliente');
$nombreContacto = trim(data_get($SolicitudServicio, 'personalcliente.PersFirstName', '').' '.data_get($SolicitudServicio, 'personalcliente.PersLastName', ''));
$emailContacto = data_get($SolicitudServicio, 'personalcliente.PersEmail', 'No registrado');
$celContacto = data_get($SolicitudServicio, 'personalcliente.PersCellphone', 'No registrado');
$text = "ha sido modificada por el cliente ".$clienteNombre." para añadir los residuos faltantes, nuestra área logística estará revisando las cantidades correspondiente para ingresarlas como recibidas en la aplicación SisPRO";
@endphp

En estos momentos la Solicitud de Servicio N° {{$SolicitudServicio->ID_SolSer}} {{$text}}.<br>

# Observaciones del Cliente:

<p style="background-color:#f0f3f8;"><i>{!!nl2br($SolicitudServicio->SolSerDescript)!!}</i></p>

{{__("Puede comunicarse con:")}}<br>

***{{__("Nombre: ")}}***{{$nombreContacto !== '' ? $nombreContacto : 'No registrado'}}<br>

***{{__("E-mail: ")}}***{{$emailContacto}}<br>

***{{__("N° Celular: ")}}***{{$celContacto}}<br>

@component('mail::button', ['url' => url('/solicitud-servicio', [$SolicitudServicio->SolSerSlug])])
{{-- {{$nameButton}} --}}
Ver Solicitud
@endcomponent

@php
$end = 'Click en el botón para más detalles.';
@endphp

{{$end}}

@endcomponent
