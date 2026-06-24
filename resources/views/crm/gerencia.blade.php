@extends('layouts.app')

@section('htmlheader_title')
Vista de Gerencia - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #667eea 0%, #764ba2 100%); padding-right:30vw; position:relative; overflow:hidden; color: white;">
    <i class="fas fa-chart-pie"></i> Vista de Gerencia Comercial
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <!-- Estadísticas Generales del Equipo -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua-gradient">
                <div class="inner">
                    <h3>{{ number_format($statsGenerales['totalComerciales']) }}</h3>
                    <p>Comerciales Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('crm.gerencia') }}#equipo-comercial" class="small-box-footer">
                    Equipo Comercial <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green-gradient">
                <div class="inner">
                    <h3>{{ number_format($statsGenerales['totalClientes']) }}</h3>
                    <p>Total Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('crm.clientes.index') }}" class="small-box-footer">
                    Ver clientes <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow-gradient">
                <div class="inner">
                    <h3>${{ number_format($statsGenerales['valorTotalPipeline'], 0, ',', '.') }}</h3>
                    <p>Valor Total Pipeline</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('crm.oportunidades.index') }}" class="small-box-footer">
                    Ver pipeline <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red-gradient">
                <div class="inner">
                    <h3>${{ number_format($statsGenerales['valorEsperado'], 0, ',', '.') }}</h3>
                    <p>Valor Esperado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('crm.oportunidades.index') }}" class="small-box-footer">
                    Ver detalles <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Bloque: Clientes Nuevos y Procedencia (creación) -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-user-plus"></i> Clientes Nuevos y Procedencia
                    </h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('crm.gerencia.clientes-nuevos-mes') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-list"></i> Ver por mes / año
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-3 col-xs-6">
                            <a href="{{ route('crm.gerencia.clientes-nuevos-mes', ['mes' => now()->month, 'anio' => now()->year]) }}" style="text-decoration: none; color: inherit; display: block;">
                                <div class="info-box" style="cursor: pointer; transition: box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.boxShadow='0 1px 1px rgba(0,0,0,0.1)'">
                                    <span class="info-box-icon bg-green"><i class="fas fa-user-plus"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Clientes Nuevos (Este Mes)</span>
                                        <span class="info-box-number">{{ number_format($statsGenerales['clientesNuevosMes']) }}</span>
                                        <small class="text-muted"><i class="fa fa-arrow-right"></i> Ver detalle</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-xs-6">
                            <a href="{{ route('crm.gerencia.clientes-nuevos-mes', ['todos' => true, 'anio' => now()->year]) }}" style="text-decoration: none; color: inherit; display: block;">
                                <div class="info-box" style="cursor: pointer; transition: box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.boxShadow='0 1px 1px rgba(0,0,0,0.1)'">
                                    <span class="info-box-icon bg-teal"><i class="fas fa-user-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Clientes Nuevos (Este Año)</span>
                                        <span class="info-box-number">{{ number_format($statsGenerales['clientesNuevosAnio']) }}</span>
                                        <small class="text-muted"><i class="fa fa-arrow-right"></i> Ver detalle</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @if(isset($clientesNuevosPorProcedencia) && $clientesNuevosPorProcedencia->count() > 0)
                            @foreach($clientesNuevosPorProcedencia as $procedencia)
                                <div class="col-md-3 col-sm-6 col-lg-2">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-blue">
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
                                        <div class="info-box-content">
                                            <span class="info-box-text" style="font-size: 11px;">{{ $procedencia->CliProcedencia ?? 'Sin procedencia' }}</span>
                                            <span class="info-box-number">{{ number_format($procedencia->total) }}</span>
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

    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fas fa-lightbulb"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Oportunidades Activas</span>
                    <span class="info-box-number">{{ number_format($statsGenerales['totalOportunidades']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fas fa-calendar-day"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Actividades Hoy</span>
                    <span class="info-box-number">{{ number_format($statsGenerales['actividadesHoy']) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-xs-6">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cotizaciones Pendientes</span>
                    <span class="info-box-number">{{ number_format($statsGenerales['cotizacionesPendientes']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rendimiento por Comercial -->
    <div class="row" id="equipo-comercial">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-user-chart"></i> Rendimiento por Comercial
                    </h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Comercial</th>
                                    <th class="text-center">Total Clientes</th>
                                    <th class="text-center">Nuevos (Mes)</th>
                                    <th class="text-center">Nuevos (Año)</th>
                                    <th class="text-center">Oportunidades</th>
                                    <th class="text-right">Valor Pipeline</th>
                                    <th class="text-right">Valor Esperado</th>
                                    <th class="text-center">Actividades Hoy</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statsPorComercial as $stats)
                                    @php
                                        $comercial = $stats['comercial'];
                                        $nombreCompleto = trim($comercial->PersFirstName . ' ' . $comercial->PersSecondName . ' ' . $comercial->PersLastName);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $nombreCompleto }}</strong>
                                            @if($comercial->PersEmail)
                                                <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $comercial->PersEmail }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-blue">{{ $stats['totalClientes'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($stats['clientesNuevosMes'] > 0)
                                                <span class="badge bg-green" title="Clientes nuevos este mes">{{ $stats['clientesNuevosMes'] }}</span>
                                            @else
                                                <span class="badge bg-gray">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($stats['clientesNuevosAnio'] > 0)
                                                <span class="badge bg-teal" title="Clientes nuevos este año">{{ $stats['clientesNuevosAnio'] }}</span>
                                            @else
                                                <span class="badge bg-gray">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-green">{{ $stats['oportunidadesActivas'] }}</span>
                                        </td>
                                        <td class="text-right">
                                            <strong>${{ number_format($stats['valorPipeline'], 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-right">
                                            <strong style="color: #28a745;">${{ number_format($stats['valorEsperado'], 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-yellow">{{ $stats['actividadesHoy'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('crm.clientes.index') }}?comercial={{ $comercial->ID_Pers }}" 
                                               class="btn btn-xs btn-info" 
                                               title="Ver clientes">
                                                <i class="fa fa-users"></i>
                                            </a>
                                            <a href="{{ route('crm.oportunidades.index') }}?comercial={{ $comercial->ID_Pers }}" 
                                               class="btn btn-xs btn-success" 
                                               title="Ver oportunidades">
                                                <i class="fa fa-lightbulb"></i>
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

    <div class="row">
        <!-- Actividades del Día del Equipo -->
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-calendar-day"></i> Actividades de Hoy - Equipo
                    </h3>
                </div>
                <div class="box-body" style="max-height: 400px; overflow-y: auto;">
                    @if($actividadesHoy->count() > 0)
                        @foreach($actividadesHoy as $actividad)
                            <div class="activity-item" style="padding: 10px; border-bottom: 1px solid #eee;">
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
                                        <p style="margin: 3px 0; color: #999; font-size: 11px;">
                                            <i class="fas fa-user"></i> {{ trim($actividad->comercial->PersFirstName . ' ' . $actividad->comercial->PersSecondName . ' ' . $actividad->comercial->PersLastName) }}
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
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Próximas Actividades del Equipo -->
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-calendar-week"></i> Próximas Actividades - Equipo
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
                                            <i class="fas fa-building"></i> {{ $actividad->cliente->CliShortname ?? $actividad->cliente->CliName }}
                                        </p>
                                        <p style="margin: 3px 0; color: #999; font-size: 10px;">
                                            <i class="fas fa-user"></i> {{ trim($actividad->comercial->PersFirstName . ' ' . $actividad->comercial->PersSecondName . ' ' . $actividad->comercial->PersLastName) }}
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

    <!-- Pipeline de Oportunidades del Equipo -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-project-diagram"></i> Pipeline de Oportunidades - Equipo
                    </h3>
                </div>
                <div class="box-body">
                    <div class="row">
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
                                        @if(isset($oportunidadesPorEtapa[$etapa]) && $oportunidadesPorEtapa[$etapa]->count() > 0)
                                            @foreach($oportunidadesPorEtapa[$etapa] as $oportunidad)
                                                <div class="opportunity-card" style="background: #f9f9f9; padding: 10px; margin-bottom: 10px; border-left: 3px solid {{ $colorEtapa }};">
                                                    <h5 style="margin: 0 0 5px 0; font-size: 13px; font-weight: bold;">
                                                        {{ $oportunidad->OportTitulo }}
                                                    </h5>
                                                    <p style="margin: 3px 0; color: #666; font-size: 11px;">
                                                        <i class="fas fa-building"></i> {{ $oportunidad->cliente->CliShortname ?? $oportunidad->cliente->CliName }}
                                                    </p>
                                                    <p style="margin: 3px 0; color: #999; font-size: 10px;">
                                                        <i class="fas fa-user"></i> {{ trim($oportunidad->comercial->PersFirstName . ' ' . $oportunidad->comercial->PersSecondName . ' ' . $oportunidad->comercial->PersLastName) }}
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

    <!-- Cotizaciones Pendientes del Equipo -->
    @if($cotizacionesPendientes->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-file-invoice-dollar"></i> Cotizaciones Pendientes - Equipo
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

    .info-box {
        display: block;
        min-height: 90px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 2px;
        margin-bottom: 15px;
    }

    .info-box-icon {
        border-top-left-radius: 2px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 2px;
        display: block;
        float: left;
        height: 90px;
        width: 90px;
        text-align: center;
        font-size: 45px;
        line-height: 90px;
        background: rgba(0,0,0,0.2);
    }

    .info-box-content {
        padding: 5px 10px;
        margin-left: 90px;
    }

    .info-box-text {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 13px;
    }

    .info-box-number {
        display: block;
        font-weight: bold;
        font-size: 18px;
    }

    .bg-green { background-color: #00a65a !important; }
    .bg-teal { background-color: #39cccc !important; }
    .bg-gray { background-color: #d2d6de !important; }
</style>
@endpush
@endsection

