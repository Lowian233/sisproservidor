@component('mail::message')
# Alerta de Servicio sin Programar {{ $service->ID_SolSer }}
Prosarc S.A. ESP informa que el servicio del cliente {{ $service->cliente->CliName }}, fue creado el {{ $service->created_at->format('d/m/Y') }} y no cuenta aún con una programación.<br>
<br>
<p style="background-color:#f80606;color:whitesmoke;"><i>Programe cuanto antes el servicio con Id {{ $service->ID_SolSer }}</i></p>
<p>Revisar y programar cuanto antes.</p>
@component('mail::button', ['url' => url('/solicitud-servicio', [$service->SolSerSlug])])
Ver Solicitud de Servicio
@endcomponent
Saludos,  
@endcomponent