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
Se ha solicitado **auditoría virtual** para este servicio.
@elseif ($tipoAud === 'Presencial')
Se ha solicitado **auditoría presencial** para este servicio.
@else
El cliente ha indicado requerimientos de **auditoría** en esta solicitud de servicio.
@endif

Le informamos que el cliente **{{ $SolicitudServicio['cliente']->CliName ?? 'Cliente' }}** presentó una solicitud que requiere seguimiento de auditoría. Para más detalle, contacte al solicitante.

<p style="background-color:#f0f3f8;"><i>{!! nl2br($SolicitudServicio->SolSerDescript ?? '') !!}</i></p>

@component('mail::button', ['url' => url('/solicitud-servicio', [$SolicitudServicio->SolSerSlug])])
Ver solicitud
@endcomponent

@endcomponent
