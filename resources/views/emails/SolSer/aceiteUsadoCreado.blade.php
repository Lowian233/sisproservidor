@component('mail::message')

# Nueva Solicitud de Servicio N° {{$SolicitudServicio->ID_SolSer}} con Aceites Usados

Se ha creado una nueva solicitud de servicio que contiene los siguientes aceites usados:

@component('mail::table')
| Residuo | Tipo |
| :------------- | :------------: |
@foreach ($SolicitudServicio->SolicitudResiduo as $value)
@if ($value->requerimiento->respel->AceiteUsado == 1)
| {{$value->requerimiento->respel->RespelName}} | Aceite Usado |
@endif
@endforeach
@endcomponent

Para detalles adicionales comuníquese con la persona de contacto con los siguientes datos:<br>
<ul>
    <li>Nombre: {{$email->PersFirstName.' '.$email->PersLastName}} </li>
    <li>Teléfono: {{$email->PersCellphone}}</li>
    <li>Correo: {{$email->PersEmail}}</li>
    <li>Cliente: {{ $SolicitudServicio->cliente->CliName }} ({{ $SolicitudServicio->cliente->CliComercial ?? '' }})</li>
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