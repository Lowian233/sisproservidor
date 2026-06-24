@component('mail::message')
Recibo de material de la solicitud {{$GenerResiduos->FK_SolSer}}

Saludos Sres {{$GenerResiduos->CliName}} <b></b>

Hemos recibido los residuos de la solicitud {{$GenerResiduos->FK_SolSer}} del generador {{$GenerResiduos->GenerName}}  con éxito. 

### Conciliación
Si **no está de acuerdo** con la información del documento adjunto, por favor comuníquese con el área de **Conciliaciones** al correo `conciliaciones@prosarc.com.co` en un plazo **no mayor a 3 días hábiles**.

Tenga en cuenta que, si no se recibe respuesta dentro de este plazo, el área de Conciliaciones realizará la conciliación directa del servicio.

@component('mail::subcopy')
La información de este mensaje es privilegiada y confidencial.

Este correo electrónico se envió desde una dirección que no acepta correos electrónicos entrantes. Por favor, no responda a este mensaje.

Si tiene alguna pregunta, inquietud o si recibió esta notificación por error comuníquese con: directoracomercial@prosarc.com.co

@endcomponent