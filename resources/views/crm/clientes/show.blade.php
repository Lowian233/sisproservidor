@extends('layouts.app')

@section('htmlheader_title')
{{ $cliente->CliName }} - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #667eea, #764ba2); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-building"></i> {{ $cliente->CliShortname ?? $cliente->CliName }}
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <!-- Informaci??n del Cliente -->
        <div class="col-md-4">
            <div class="box {{ ($cliente->CliActivo ?? 1) == 0 ? 'box-default' : 'box-primary' }}">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-info-circle"></i> Informaci??n del Cliente
                        @if(($cliente->CliActivo ?? 1) == 0)
                            <span class="label label-warning">Desactivado</span>
                        @endif
                    </h3>
                    <div class="box-tools pull-right">
                        @if(($cliente->CliActivo ?? 1) == 0)
                            <form action="{{ route('crm.clientes.toggle-activo', $cliente->CliSlug) }}" method="post" style="display: inline;">
                                @csrf
                                <input type="hidden" name="activo" value="1">
                                <input type="hidden" name="redirect" value="show">
                                <button type="submit" class="btn btn-sm btn-success" title="Activar cliente (volver?? a contar en totales)">
                                    <i class="fas fa-check-circle"></i> Activar
                                </button>
                            </form>
                        @else
                            <form action="{{ route('crm.clientes.toggle-activo', $cliente->CliSlug) }}" method="post" style="display: inline;" onsubmit="return confirm('?0?7Desactivar este cliente? Ya no contar?? en el total hasta que lo reactive.');">
                                @csrf
                                <input type="hidden" name="activo" value="0">
                                <input type="hidden" name="redirect" value="show">
                                <button type="submit" class="btn btn-sm btn-warning" title="Desactivar (no contar?? en totales)">
                                    <i class="fas fa-pause-circle"></i> Desactivar
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('solicitud-servicio.create') }}?ID_Cli={{ $cliente->ID_Cli }}" class="btn btn-sm btn-success" title="Crear solicitud de servicio">
                            <i class="fas fa-file-invoice"></i> Crear Solicitud
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <dl>
                        <dt><i class="fas fa-building"></i> Nombre Completo</dt>
                        <dd>{{ $cliente->CliName }}</dd>
                        
                        <dt><i class="fas fa-id-card"></i> NIT</dt>
                        <dd>{{ $cliente->CliNit }}</dd>
                        
                        <dt><i class="fas fa-map-marker-alt"></i> Sedes</dt>
                        <dd>
                            @if($cliente->sedes->count() > 0)
                                <ul style="padding-left: 20px; margin: 5px 0;">
                                    @foreach($cliente->sedes->take(3) as $sede)
                                        <li>{{ $sede->SedeName }}</li>
                                    @endforeach
                                    @if($cliente->sedes->count() > 3)
                                        <li><em>+{{ $cliente->sedes->count() - 3 }} m??s</em></li>
                                    @endif
                                </ul>
                            @else
                                <span class="text-muted">Sin sedes registradas</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Actividades Recientes -->
        <div class="col-md-8">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-history"></i> Actividades Recientes
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('crm.actividades.create') }}?cliente={{ $cliente->ID_Cli }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> Nueva Actividad
                        </a>
                    </div>
                </div>
                <div class="box-body" style="max-height: 400px; overflow-y: auto;">
                    @if($actividades->count() > 0)
                        @foreach($actividades as $actividad)
                            <div class="timeline-item" style="margin-bottom: 20px; padding: 10px; border-left: 3px solid #3c8dbc;">
                                <div class="row">
                                    <div class="col-md-1 text-center">
                                        @switch($actividad->ActTipo)
                                            @case('Llamada')
                                                <i class="fas fa-phone fa-lg text-primary"></i>
                                                @break
                                            @case('Visita')
                                                <i class="fas fa-map-marker-alt fa-lg text-success"></i>
                                                @break
                                            @case('Email')
                                                <i class="fas fa-envelope fa-lg text-info"></i>
                                                @break
                                            @default
                                                <i class="fas fa-tasks fa-lg text-warning"></i>
                                        @endswitch
                                    </div>
                                    <div class="col-md-9">
                                        <h4 style="margin: 0; font-size: 14px;">
                                            {{ $actividad->ActTitulo }}
                                        </h4>
                                        @if($actividad->ActDescripcion)
                                            <p style="margin: 5px 0; color: #666; font-size: 12px;">
                                                {{ \Illuminate\Support\Str::limit($actividad->ActDescripcion, 100) }}
                                            </p>
                                        @endif
                                        <small style="color: #999;">
                                            <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($actividad->ActFechaProgramada)->format('d/m/Y H:i') }}
                                            @if($actividad->ActResultado)
                                                | <i class="fas fa-check-circle text-success"></i> {{ $actividad->ActResultado }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <span class="label label-{{ $actividad->ActEstado == 'Completada' ? 'success' : ($actividad->ActEstado == 'Pendiente' ? 'warning' : 'default') }}">
                                            {{ $actividad->ActEstado }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="padding: 40px;">
                            <i class="fas fa-history fa-3x text-muted"></i>
                            <p style="margin-top: 15px; color: #999;">No hay actividades registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Oportunidades y Cotizaciones -->
    <div class="row">
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-lightbulb"></i> Oportunidades
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('crm.oportunidades.create') }}?cliente={{ $cliente->ID_Cli }}" class="btn btn-sm btn-info">
                            <i class="fa fa-plus"></i> Nueva
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    @if($oportunidades->count() > 0)
                        @foreach($oportunidades as $oportunidad)
                            @php
                                $probabilidad = intval($oportunidad->OportProbabilidad ?? 0);
                            @endphp
                            <div class="opportunity-item" style="padding: 15px; margin-bottom: 10px; background: #f9f9f9; border-left: 4px solid #17a2b8;">
                                <h4 style="margin: 0 0 10px 0; font-size: 15px;">
                                    {{ $oportunidad->OportTitulo }}
                                </h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p style="margin: 5px 0; color: #28a745; font-weight: bold;">
                                            ${{ number_format($oportunidad->OportValorEstimado, 0, ',', '.') }}
                                        </p>
                                        <span class="label label-info">{{ $oportunidad->OportEtapa }}</span>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="progress" style="height: 8px; margin: 5px 0;">
                                            <div class="progress-bar" role="progressbar" data-width="{{ $probabilidad }}" style="width: 0%;"></div>
                                        </div>
                                        <small>{{ $probabilidad }}% probabilidad</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="padding: 30px;">
                            <i class="fas fa-lightbulb fa-2x text-muted"></i>
                            <p style="margin-top: 10px; color: #999;">No hay oportunidades</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-file-invoice-dollar"></i> Cotizaciones
                    </h3>
                </div>
                <div class="box-body">
                    @if($cotizaciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acci??n</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cotizaciones as $cotizacion)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($cotizacion->FechaCotizacion)->format('d/m/Y') }}</td>
                                            <td>${{ number_format($cotizacion->Total, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="label label-{{ $cotizacion->CoStatus == 'Aceptado' ? 'success' : ($cotizacion->CoStatus == 'Pendiente' ? 'warning' : 'danger') }}">
                                                    {{ $cotizacion->CoStatus }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('cotizacion.show', $cotizacion->id_cotizacion) }}" class="btn btn-xs btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center" style="padding: 30px;">
                            <i class="fas fa-file-invoice-dollar fa-2x text-muted"></i>
                            <p style="margin-top: 10px; color: #999;">No hay cotizaciones</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.progress-bar[data-width]').each(function() {
            var width = $(this).data('width');
            $(this).css('width', width + '%');
        });
    });
</script>
@endsection
