<div class="row">
    <div class="col-md-12">
        <h4 style="margin-top: 0;">{{ $oportunidad->OportTitulo ?? '—' }}</h4>
        @if(!empty($oportunidad->OportDescripcion))
            <p class="text-muted">{{ $oportunidad->OportDescripcion }}</p>
        @endif
    </div>
</div>
<table class="table table-condensed table-bordered">
    <tbody>
        <tr>
            <th style="width: 180px;">Cliente</th>
            <td>{{ optional($oportunidad->cliente)->CliShortname ?? optional($oportunidad->cliente)->CliName ?? '—' }}</td>
        </tr>
        <tr>
            <th>Etapa</th>
            <td><span class="label label-info">{{ $oportunidad->OportEtapa ?? '—' }}</span></td>
        </tr>
        <tr>
            <th>Valor estimado</th>
            <td><strong>${{ number_format($oportunidad->OportValorEstimado ?? 0, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <th>Probabilidad</th>
            <td>{{ $oportunidad->OportProbabilidad ?? 0 }}%</td>
        </tr>
        @if($oportunidad->OportFechaCierreEsperada)
        <tr>
            <th>Fecha cierre esperada</th>
            <td>{{ \Carbon\Carbon::parse($oportunidad->OportFechaCierreEsperada)->format('d/m/Y') }}</td>
        </tr>
        @endif
        @if($oportunidad->cotizacion)
        <tr>
            <th>Cotización relacionada</th>
            <td>#{{ $oportunidad->cotizacion->id_cotizacion }} — {{ $oportunidad->cotizacion->Razon_Social ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($oportunidad->comercial)
        <tr>
            <th>Comercial</th>
            <td>{{ trim(optional($oportunidad->comercial)->PersFirstName . ' ' . optional($oportunidad->comercial)->PersLastName) ?: '—' }}</td>
        </tr>
        @endif
        @if(!empty($oportunidad->OportNotas))
        <tr>
            <th>Notas</th>
            <td>{{ $oportunidad->OportNotas }}</td>
        </tr>
        @endif
    </tbody>
</table>
<p style="margin-top: 15px;">
    <a href="{{ route('crm.oportunidades.edit', $oportunidad->ID_Oportunidad) }}" class="btn btn-primary">
        <i class="fa fa-edit"></i> Editar oportunidad
    </a>
</p>
