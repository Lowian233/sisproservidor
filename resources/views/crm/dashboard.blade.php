@extends('layouts.app')

@section('htmlheader_title')
Dashboard CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #00C851, #007E33); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-chart-line"></i> Dashboard CRM
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <!-- Estadísticas Rápidas -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua-gradient">
                <div class="inner">
                    <h3>{{ number_format($stats['totalClientes']) }}</h3>
                    <p>Mis Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('crm.clientes.index') }}" class="small-box-footer">
                    Ver todos <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green-gradient">
                <div class="inner">
                    <h3>{{ number_format($stats['oportunidadesActivas']) }}</h3>
                    <p>Oportunidades Activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <a href="{{ route('crm.oportunidades.index') }}" class="small-box-footer">
                    Ver pipeline <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow-gradient">
                <div class="inner">
                    <h3>${{ number_format($stats['valorTotalPipeline'], 0, ',', '.') }}</h3>
                    <p>Valor Total Pipeline</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('crm.oportunidades.index') }}" class="small-box-footer">
                    Ver detalles <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red-gradient">
                <div class="inner">
                    <h3>{{ number_format($stats['actividadesHoy']) }}</h3>
                    <p>Actividades Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <a href="{{ route('crm.actividades.index') }}" class="small-box-footer">
                    Ver agenda <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Clientes Nuevos del Mes -->
    @if(isset($stats['clientesNuevosMes']) && $stats['clientesNuevosMes'] > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-user-plus"></i> Mis Clientes Nuevos Este Mes
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('crm.mis-clientes-nuevos') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-list"></i> Ver Todos mis Clientes Nuevos
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-xs-6">
                            <div class="info-box" style="min-height: 60px; padding: 5px;">
                                <span class="info-box-icon bg-green" style="width: 45px; height: 45px; line-height: 45px; font-size: 18px; margin-right: 5px; float: left;"><i class="fas fa-users"></i></span>
                                <div class="info-box-content" style="padding-left: 0; margin-left: 50px;">
                                    <span class="info-box-text" style="font-size: 10px; margin-bottom: 2px; display: block; line-height: 1.2;">Total Nuevos</span>
                                    <span class="info-box-number" style="font-size: 16px; margin-top: 0; line-height: 1.2;">{{ number_format($stats['clientesNuevosMes']) }}</span>
                                </div>
                            </div>
                        </div>
                        @if(isset($clientesNuevosMes) && $clientesNuevosMes->count() > 0)
                            @foreach($clientesNuevosMes as $procedencia)
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="info-box" style="min-height: 60px; padding: 5px;">
                                        <span class="info-box-icon bg-blue" style="width: 45px; height: 45px; line-height: 45px; font-size: 16px; margin-right: 5px; float: left;">
                                            @if($procedencia->CliProcedencia == 'Visita')
                                                <i class="fas fa-map-marker-alt"></i>
                                            @elseif($procedencia->CliProcedencia == 'Llamada')
                                                <i class="fas fa-phone"></i>
                                            @elseif($procedencia->CliProcedencia == 'Contacto en frío')
                                                <i class="fas fa-snowflake"></i>
                                            @elseif($procedencia->CliProcedencia == 'Campaña Redes Sociales')
                                                <i class="fas fa-share-alt"></i>
                                            @elseif($procedencia->CliProcedencia == 'Referido')
                                                <i class="fas fa-user-friends"></i>
                                            @else
                                                <i class="fas fa-question-circle"></i>
                                            @endif
                                        </span>
                                        <div class="info-box-content" style="padding-left: 0; margin-left: 50px;">
                                            <span class="info-box-text" style="font-size: 10px; margin-bottom: 2px; display: block; line-height: 1.2;">{{ $procedencia->CliProcedencia ?? 'Sin procedencia' }}</span>
                                            <span class="info-box-number" style="font-size: 16px; margin-top: 0; line-height: 1.2;">{{ number_format($procedencia->total) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Actividades del Día -->
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-calendar-day"></i> Actividades de Hoy
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('crm.actividades.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> Nueva Actividad
                        </a>
                    </div>
                </div>
                <div class="box-body" style="max-height: 400px; overflow-y: auto;">
                    @if($actividadesHoy->count() > 0)
                        @foreach($actividadesHoy as $actividad)
                            <div class="activity-item" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;" onclick="window.location='{{ route('crm.actividades.index') }}'">
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        @switch($actividad->ActTipo)
                                            @case('Llamada')
                                                <i class="fas fa-phone fa-2x text-primary"></i>
                                                @break
                                            @case('Visita')
                                                <i class="fas fa-map-marker-alt fa-2x text-success"></i>
                                                @break
                                            @case('Email')
                                                <i class="fas fa-envelope fa-2x text-info"></i>
                                                @break
                                            @case('Reunión')
                                                <i class="fas fa-users fa-2x text-warning"></i>
                                                @break
                                            @default
                                                <i class="fas fa-tasks fa-2x text-secondary"></i>
                                        @endswitch
                                    </div>
                                    <div class="col-md-8">
                                        <h4 style="margin: 0; font-size: 14px; font-weight: bold;">
                                            {{ $actividad->ActTitulo }}
                                        </h4>
                                        <p style="margin: 5px 0; color: #666; font-size: 12px;">
                                            <i class="fas fa-building"></i> {{ $actividad->cliente->CliShortname ?? $actividad->cliente->CliName }}
                                        </p>
                                        <p style="margin: 0; color: #999; font-size: 11px;">
                                            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($actividad->ActFechaProgramada)->format('H:i') }}
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        @if($actividad->ActEstado == 'Pendiente')
                                            <span class="label label-warning">Pendiente</span>
                                        @elseif($actividad->ActEstado == 'Completada')
                                            <span class="label label-success">Completada</span>
                                        @else
                                            <span class="label label-default">{{ $actividad->ActEstado }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="padding: 40px;">
                            <i class="fas fa-calendar-times fa-3x text-muted"></i>
                            <p style="margin-top: 15px; color: #999;">No hay actividades programadas para hoy</p>
                            <a href="{{ route('crm.actividades.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Crear Actividad
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Próximas Actividades -->
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-calendar-week"></i> Próximas Actividades
                    </h3>
                </div>
                <div class="box-body" style="max-height: 400px; overflow-y: auto;">
                    @if($actividadesPendientes->count() > 0)
                        @foreach($actividadesPendientes as $actividad)
                            <div class="activity-item" style="padding: 10px; border-bottom: 1px solid #eee;">
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        @switch($actividad->ActTipo)
                                            @case('Llamada')
                                                <i class="fas fa-phone fa-lg text-primary"></i>
                                                @break
                                            @case('Visita')
                                                <i class="fas fa-map-marker-alt fa-lg text-success"></i>
                                                @break
                                            @default
                                                <i class="fas fa-tasks fa-lg text-info"></i>
                                        @endswitch
                                    </div>
                                    <div class="col-md-10">
                                        <h4 style="margin: 0; font-size: 13px;">
                                            {{ $actividad->ActTitulo }}
                                        </h4>
                                        <p style="margin: 3px 0; color: #666; font-size: 11px;">
                                            {{ $actividad->cliente->CliShortname ?? $actividad->cliente->CliName }}
                                        </p>
                                        <p style="margin: 0; color: #999; font-size: 10px;">
                                            <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($actividad->ActFechaProgramada)->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center" style="padding: 40px;">
                            <i class="fas fa-check-circle fa-3x text-success"></i>
                            <p style="margin-top: 15px; color: #999;">No hay actividades pendientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline de Oportunidades -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-project-diagram"></i> Pipeline de Oportunidades
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('crm.oportunidades.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> Nueva Oportunidad
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row"></div>
                        @foreach(['Prospección', 'Cotización', 'Negociación', 'Cierre', 'Aprobado', 'Rechazado'] as $etapa)
                            @php
                                $coloresEtapa = [
                                    'Prospección' => '#17a2b8',
                                    'Cotización' => '#ffc107',
                                    'Negociación' => '#fd7e14',
                                    'Cierre' => '#28a745',
                                    'Aprobado' => '#198754',
                                    'Rechazado' => '#dc3545'
                                ];
                                $colorEtapa = $coloresEtapa[$etapa] ?? '#6c757d';
                            @endphp
                            <div class="col-md-2">
                                <div class="panel panel-default">
                                    <div class="panel-heading" style="background-color: {{ $colorEtapa }}; color: white;">
                                        <h4 style="margin: 0;">
                                            {{ $etapa }}
                                            <span class="badge pull-right">{{ isset($oportunidadesPorEtapa[$etapa]) ? $oportunidadesPorEtapa[$etapa]->count() : 0 }}</span>
                                        </h4>
                                    </div>
                                    <div class="panel-body" style="min-height: 300px; max-height: 500px; overflow-y: auto;">
                                        @php
                                            $coloresEtapa = [
                                                'Prospección' => '#17a2b8',
                                                'Cotización' => '#ffc107',
                                                'Negociación' => '#fd7e14',
                                                'Cierre' => '#28a745',
                                                'Aprobado' => '#198754',
                                                'Rechazado' => '#dc3545'
                                            ];
                                            $colorEtapa = $coloresEtapa[$etapa] ?? '#6c757d';
                                        @endphp
                                        @if(isset($oportunidadesPorEtapa[$etapa]) && $oportunidadesPorEtapa[$etapa]->count() > 0)
                                            @foreach($oportunidadesPorEtapa[$etapa] as $oportunidad)
                                                @php
                                                    $urlOportunidades = route('crm.oportunidades.index');
                                                @endphp
                                                <div class="opportunity-card" style="background: #f9f9f9; padding: 10px; margin-bottom: 10px; border-left: 3px solid {{ $colorEtapa }}; cursor: pointer;" onclick="window.location='{{ $urlOportunidades }}'">
                                                    <h5 style="margin: 0 0 5px 0; font-size: 13px; font-weight: bold;">
                                                        {{ $oportunidad->OportTitulo }}
                                                    </h5>
                                                    <p style="margin: 3px 0; color: #666; font-size: 11px;">
                                                        <i class="fas fa-building"></i> {{ $oportunidad->cliente->CliShortname ?? $oportunidad->cliente->CliName }}
                                                    </p>
                                                    <p style="margin: 3px 0; color: #28a745; font-weight: bold; font-size: 12px;">
                                                        ${{ number_format($oportunidad->OportValorEstimado, 0, ',', '.') }}
                                                    </p>
                                                    <div class="progress" style="height: 5px; margin: 5px 0;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $oportunidad->OportProbabilidad }}%; background-color: {{ $colorEtapa }};"></div>
                                                    </div>
                                                    <small style="color: #999; font-size: 10px;">
                                                        {{ $oportunidad->OportProbabilidad }}% probabilidad
                                                    </small>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center" style="padding: 20px; color: #999;">
                                                <i class="fas fa-inbox fa-2x"></i>
                                                <p style="margin-top: 10px;">Sin oportunidades</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cotizaciones Pendientes -->
    @if($cotizacionesPendientes->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-file-invoice-dollar"></i> Cotizaciones Pendientes
                    </h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cotizacionesPendientes as $cotizacion)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($cotizacion->FechaCotizacion)->format('d/m/Y') }}</td>
                                        <td>{{ $cotizacion->Razon_Social }}</td>
                                        <td>${{ number_format($cotizacion->Total, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="label label-warning">{{ $cotizacion->CoStatus }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('cotizacion.show', $cotizacion->id_cotizacion) }}" class="btn btn-xs btn-info">
                                                <i class="fa fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .bg-aqua-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
    .bg-green-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
    .bg-yellow-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; }
    .bg-red-gradient { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important; }
    
    .activity-item:hover {
        background-color: #f5f5f5;
        transition: background-color 0.3s;
    }
    
    .opportunity-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s;
    }
</style>
@endpush
@endsection


