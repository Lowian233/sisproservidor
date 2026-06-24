{{-- @php
    $url = route('cotizacion.show', $cotizacion->id_cotizacion);
    $nameButton = 'Ver Cotizacion';
@endphp --}}

@component('mail::message')
# Nueva Cotización Creada - #{{ $cotizacion->id_cotizacion }}

**Hola Coordinador Comercial,**

Se ha creado una nueva cotización con el #{{$cotizacion->id_cotizacion}}. Por favor, revisa y aprueba la cotización a la brevedad.

  Detalles de la Cotización

- Razón Social: {{ $cotizacion->Razon_Social }}
- NIT: {{ $cotizacion->Nit }}

### Próximos Pasos

1. **Revisar la Cotización:** Asegúrate de que todos los detalles sean correctos.
2. **Aprobar o Rechazar:** Cambia el estado de la cotización según corresponda.

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