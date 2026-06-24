@component('mail::message')
# ¡Califica nuestro Servicio!

Estimado/a **{{ $clienteNombre }}**,

Hemos completado el servicio de recolección para la **Solicitud #{{ $servicio->ID_SolSer }}**.

Tu opinión es muy importante para nosotros. Por favor, tómate un momento para calificar nuestro servicio.

@component('mail::button', ['url' => $urlCalificacion])
Calificar Servicio
@endcomponent

Gracias por confiar en Prosarc S.A. ESP

Saludos cordiales,<br>
<strong>Equipo Prosarc</strong>

@component('mail::subcopy')
La información de este mensaje es privilegiada y confidencial.

Este correo electrónico se envió desde una dirección que no acepta correos electrónicos entrantes. Por favor, no responda a este mensaje.

Si tiene alguna pregunta, inquietud o si recibió esta notificación por error comuníquese con: coordinadorse@prosarc.com.co
@endcomponent

@endcomponent
