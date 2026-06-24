{{-- @php
    // $url = url("/respels/{$respel->RespelSlug}");
    // $nameButton = 'Ver Residuo';
@endphp --}}
@component('mail::message')
# Residuo Aprobado: {{ $respelName }}

El residuo **{{ $respelName }}** ha sido aprobado.

Ahora puede proceder con la cotización para este residuo. Por favor, revise los detalles del residuo usando el siguiente botón:

@component('mail::button', ['url' => url('/respelpublic', [$respelSlug])])
Ver Residuo
@endcomponent

Si tiene alguna duda, no olvide comunicarse con dirección técnica.<br>
Saludos, Prosarc S.A. ESP.

{{-- @component('mail::subcopy')
@lang(
    "Si tiene problemas para hacer clic en el botón \":actionText\", copie y pegue la siguiente URL en su navegador web: [:actionURL](:actionURL)",
    [
        'actionText' => 'Ver Residuo',
        'actionURL' => url('/respelpublic', [$respelSlug]),
    ]
)
@endcomponent --}}
@endcomponent