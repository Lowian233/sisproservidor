@component('mail::message')
# Contratos cercanos a vencerse

Los siguientes contratos están apunto de ser vencidos.<br><br><br>

@component('mail::table')
    | Clientes | Fecha de Vencimiento |
    | ---------- | ---------- |
    @foreach ($contratos as $contrato)
        | {{$contrato->CliShortname}} | {{$contrato->ContraVigencia}} |
    @endforeach
@endcomponent
<br><br>
{{ __('Si desea ver más detalles diríjase a aplicación de SisPRO en el apartado de "Contratos".')}}

@endcomponent