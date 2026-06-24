{{-- @php
    // $url = url("/respels/{$respel->RespelSlug}");
    // $nameButton = 'Ver Residuo';
@endphp --}}
@component('mail::message')
# Nuevo Residuo Creado: {{$respel->RespelName}}

Se ha creado un nuevo residuo llamado **{{$respel->RespelName}}**. Este residuo está pendiente de aprobación.

Por favor, revise los detalles del residuo usando el siguiente botón:

@component('mail::button', ['url' => url('/respels', [$respel->RespelSlug])])
Ver Residuo
@endcomponent

Si tiene alguna duda, no olvide comunicarse con el area comercial.<br>
Saludos, Prosarc S.A. ESP.

{{-- @component('mail::subcopy')
@lang(
    "Si tiene problemas para hacer clic en el botón \":actionText\", copie y pegue la siguiente URL \nen su navegador web: [:actionURL](:actionURL)",
    [
        'actionText' => 'Ver Residuo',
        'actionURL' => url('publicrespel.show', [$respel->RespelSlug]),
    ]
)
@endcomponent --}}
@endcomponent