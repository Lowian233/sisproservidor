@component('mail::message')
# Solicitud de Servicio N° {{$solExpress->ID_SolSer}}

Se ha creado una nueva Solicitud de Servicio por medio del chatbot.
<br>
# Observaciones:

<p style="background-color:#f0f3f8;"><i>{!!nl2br($solExpress->SolSerDescript)!!}</i></p>

@component('mail::button', ['url' => url('/serviciosexpress', [$solExpress->SolSerSlug])])
Ver Solicitud de Servicio
@endcomponent

Saludos
@endcomponent
