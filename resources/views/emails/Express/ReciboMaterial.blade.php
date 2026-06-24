@component('mail::message')
Recibo de material de la solicitud {{$GenerResiduos->FK_SolSer}}

Saludos Sres {{$cliente->CliName}} <b></b>

Hemos recibido los residuos de la solicitud {{$GenerResiduos->FK_SolSer}} del generador {{$GenerResiduos->GenerName}}  con éxito. 

@component('mail::subcopy')
La información de este mensaje es privilegiada y confidencial.

Este correo electrónico se envió desde una dirección que no acepta correos electrónicos entrantes. Por favor, no responda a este mensaje.

Si tiene alguna pregunta, inquietud o si recibió esta notificación por error comuníquese con: coordinadorse@prosarc.com.co
@endcomponent

@endcomponent