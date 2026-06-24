{{-- @php
    $url = route('cotizacion.show', $cotizacion->id_cotizacion);
    $nameButton = 'Ver Cotizacion';
@endphp --}}

@component('mail::message')
# Cotización Aprobada - ID #{{ $cotizacion->id_cotizacion }}

                           ##**Hola**

Nos complace informarte que tu cotización con el ID **#{{ $cotizacion->id_cotizacion }}** ha sido **aprobada** por el Coordinador Comercial.

### Detalles de la Cotización

- **Razón Social:** {{ $cotizacion->Razon_Social }}
- **NIT:** {{ $cotizacion->Nit }}


### Próximos Pasos

Puedes proceder a descargar y enviar la cotización aprobada al cliente. Asegúrate de revisar todos los detalles antes de la comunicación final.

@component('mail::button', ['url' => $urlCotizacion])
Revisar Cotización
@endcomponent

{{-- @component('mail::subcopy')
@lang(
    "Si tiene problemas para hacer clic en el botón \":actionText\", copie y pegue la siguiente URL \nen su navegador web: [:actionURL](:actionURL)",
    [
        'actionText' => $nameButton,
        'actionURL' => $url,
    ]
)
@endcomponent --}}


Si tienes alguna duda o necesitas asistencia adicional, no dudes en contactarte con el equipo de soporte.

**Saludos cordiales,**  
**Prosarc S.A. ESP.**

@endcomponent