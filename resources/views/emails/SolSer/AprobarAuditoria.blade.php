{{-- Notificación interna: solicitud con auditoría --}}
@component('mail::message')
# Solicitud de Servicio N° {{ $SolicitudServicio->ID_SolSer }}

@php
    $tipoAud = $SolicitudServicio->SolResAuditoriaTipo ?? '';
    if ($tipoAud === 99 || $tipoAud === '99') { $tipoAud = 'Virtual'; }
    elseif ($tipoAud === 98 || $tipoAud === '98') { $tipoAud = 'Presencial'; }
    elseif ($tipoAud === 97 || $tipoAud === '97') { $tipoAud = 'No Auditable'; }
@endphp

@if ($tipoAud === 'Virtual')
Se ha aprobado la **auditoría virtual** para este servicio.
@elseif ($tipoAud === 'Presencial')
Se ha aprobado la **auditoría presencial** para este servicio.

@endif

Le informamos que la solicitud del cliente **{{ $SolicitudServicio['cliente']->CliName ?? 'Cliente' }}** quedo aprobada para:

<p style="background-color:#f0f3f8;"><i>{!! nl2br($SolicitudServicio->SolSerDescript ?? '') !!}</i></p>

@component('mail::button', ['url' => url('/solicitud-servicio', [$SolicitudServicio->SolSerSlug])])
Ver solicitud
@endcomponent

@endcomponent
