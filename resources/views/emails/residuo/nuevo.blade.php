{{-- @php
    // $url = url("/respels/{$respel->RespelSlug}");
    // $nameButton = 'Ver Residuo';
@endphp --}}
@component('mail::message')
# Nuevo residuo registrado

@php
    $respelObj = is_array($respel) && isset($respel['respel']) ? $respel['respel'] : $respel;
    $usuarioCreador = is_array($respel) ? ($respel['usuario_creador'] ?? 'Usuario del sistema') : ($respel['usuario_creador'] ?? 'Usuario del sistema');
    $clienteData = is_array($respel) ? ($respel['cliente'] ?? null) : ($respel['cliente'] ?? null);
@endphp

El residuo <b>{{$respelObj->RespelName}}</b> ha sido registrado por <b>{{$usuarioCreador}}</b> para el Cliente <b>{{$clienteData->CliName ?? 'N/A'}} -> {{$clienteData->SedeName ?? 'N/A'}}</b> y debería ser revisado por el área de Operaciones para validar el tratamiento elegido.<br><br>

Por favor revise la información  del residuo, usando el siguiente botón<br><br>

@component('mail::button', ['url' => url('/respels', [$respelObj->RespelSlug])])
{{-- {{$nameButton}} --}}
Ver Residuo
@endcomponent

{{--Si tiene alguna duda comuníquese con el asesor comercial {{$respel['comercial']->PersFirstName.' '.$respel['comercial']->PersLastName}}. al teléfono {{$respel['comercial']->PersCellphone}} o con el personal del cliente usando los siguientes datos:<br>
<ul>
    <li>Nombre: {{$respel['personalcliente']->PersFirstName.' '.$respel['personalcliente']->PersLastName}} </li>
    <li>teléfono: {{$respel['personalcliente']->PersCellphone}}</li>
    <li>correo: {{$respel['personalcliente']->PersEmail}}</li>
</ul>--}}
Saludos, Prosarc S.A. ESP.
{{-- @component('mail::subcopy')
@lang(
    "Si tiene problemas para hacer clic en el botón \":actionText\", copie y pegue la siguiente URL \nen su navegador web: [:actionURL](:actionURL)",
    [
        'actionText' => $nameButton,
        'actionURL' => $url,
    ]
)
@endcomponent --}}
@endcomponent