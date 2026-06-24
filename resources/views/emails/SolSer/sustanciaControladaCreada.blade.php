@component('mail::message')
# Nueva Solicitud de Servicio N° {{$SolicitudServicio->ID_SolSer}} con Sustancias Controladas

Se ha creado una nueva solicitud de servicio que contiene las siguientes sustancias controladas:

@component('mail::table')
| Residuo | Tipo | Nombre |
| :------------- | :------------: | --------: |
@foreach ($SolicitudServicio->SolicitudResiduo as $value)
@if ($value->requerimiento->respel->SustanciaControlada == 1)
| {{$value->requerimiento->respel->RespelName}} | {{$value->requerimiento->respel->SustanciaControladaTipo == 1 ? 'Uso Masivo' : 'Controlada'}} | {{$value->requerimiento->respel->SustanciaControladaNombre}} |
@endif
@endforeach
@endcomponent

Para detalles adicionales comuníquese con la persona de contacto con los siguientes datos:<br>
<ul>
    <li>Nombre: {{$email->PersFirstName.' '.$email->PersLastName}} </li>
    <li>Teléfono: {{$email->PersCellphone}}</li>
    <li>Correo: {{$email->PersEmail}}</li>
</ul>
<br>
# Observaciones:

<p style="background-color:#f0f3f8;"><i>{!!nl2br($SolicitudServicio->SolSerDescript)!!}</i></p>

@component('mail::button', ['url' => url('/solicitud-servicio/'.$SolicitudServicio->SolSerSlug)])
Ver Solicitud de Servicio
@endcomponent

Saludos,
{{ config('app.name') }}
@endcomponent 