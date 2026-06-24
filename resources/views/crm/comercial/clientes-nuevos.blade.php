@extends('layouts.app')

@section('htmlheader_title')
Mis Clientes Nuevos - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #00C851, #007E33); padding-right:30vw; position:relative; overflow:hidden; color: white;">
    <i class="fas fa-user-plus"></i> {{ $stats['tituloVista'] }}
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <!-- Navegación de Mes -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if(!$stats['verTodos'])
                                <div class="btn-group" role="group">
                                    <a href="{{ route('crm.mis-clientes-nuevos', ['mes' => $stats['mesAnterior']['mes'], 'anio' => $stats['mesAnterior']['anio']]) }}" 
                                       class="btn btn-default">
                                        <i class="fa fa-chevron-left"></i> Mes Anterior
                                    </a>
                                    <button type="button" class="btn btn-primary" disabled style="min-width: 200px;">
                                        <i class="fas fa-calendar-alt"></i> {{ $stats['mesActual'] }}
                                    </button>
                                    @if($stats['mesSiguiente']['mes'] <= now()->month && $stats['mesSiguiente']['anio'] <= now()->year)
                                        <a href="{{ route('crm.mis-clientes-nuevos', ['mes' => $stats['mesSiguiente']['mes'], 'anio' => $stats['mesSiguiente']['anio']]) }}" 
                                           class="btn btn-default">
                                            Mes Siguiente <i class="fa fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default" disabled>
                                            Mes Siguiente <i class="fa fa-chevron-right"></i>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary" disabled style="min-width: 200px;">
                                        <i class="fas fa-calendar-alt"></i> Todos mis Clientes de {{ $stats['anioActual'] }}
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 text-right">
                            @if(!$stats['verTodos'])
                                <a href="{{ route('crm.mis-clientes-nuevos', ['todos' => true, 'anio' => $stats['anioActual']]) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-list"></i> Ver Todos del Año {{ $stats['anioActual'] }}
                                </a>
                            @else
                                <a href="{{ route('crm.mis-clientes-nuevos', ['mes' => now()->month, 'anio' => now()->year]) }}" 
                                   class="btn btn-info">
                                    <i class="fas fa-calendar"></i> Ver Mes Actual
                                </a>
                            @endif
                            <a href="{{ route('crm.dashboard') }}" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-4 col-xs-6">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fas fa-user-plus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">
                        @if($stats['verTodos'])
                            Mis Clientes Nuevos ({{ $stats['anioActual'] }})
                        @else
                            Mis Clientes Nuevos ({{ $stats['mesActual'] }})
                        @endif
                    </span>
                    <span class="info-box-number">{{ number_format($stats['totalClientesNuevos']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xs-6">
            <div class="info-box">
                <span class="info-box-icon bg-teal"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Mis Clientes Nuevos ({{ $stats['anioActual'] }})</span>
                    <span class="info-box-number">{{ number_format($stats['clientesNuevosAnio']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xs-6">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Período Visualizado</span>
                    <span class="info-box-number" style="font-size: 16px;">
                        @if($stats['verTodos'])
                            Año {{ $stats['anioActual'] }}
                        @else
                            {{ $stats['mesActual'] }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Clientes Nuevos -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-list"></i> 
                        @if($stats['verTodos'])
                            Lista de Todos mis Clientes Nuevos del Año {{ $stats['anioActual'] }}
                        @else
                            Lista de mis Clientes Nuevos de {{ $stats['mesActual'] }}
                        @endif
                    </h3>
                </div>
                <div class="box-body">
                    @if($clientesNuevos->count() > 0)
                        <div class="table-responsive">
                            <table id="clientesNuevosTable" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha de Creación</th>
                                        <th>NIT</th>
                                        <th>Razón Social</th>
                                        <th>Nombre Corto</th>
                                        <th>Procedencia</th>
                                        <th>Categoría</th>
                                        <th>Sedes</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clientesNuevos as $cliente)
                                        <tr>
                                            <td>
                                                <i class="far fa-calendar text-primary"></i>
                                                {{ \Carbon\Carbon::parse($cliente->created_at)->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">
                                                    <i class="far fa-clock"></i>
                                                    {{ \Carbon\Carbon::parse($cliente->created_at)->format('H:i') }}
                                                </small>
                                            </td>
                                            <td>{{ $cliente->CliNit }}</td>
                                            <td>
                                                <strong>{{ $cliente->CliName }}</strong>
                                            </td>
                                            <td>{{ $cliente->CliShortname }}</td>
                                            <td>
                                                @if($cliente->CliProcedencia)
                                                    @if($cliente->CliProcedencia == 'Visita')
                                                        <span class="label label-success">
                                                            <i class="fas fa-map-marker-alt"></i> {{ $cliente->CliProcedencia }}
                                                        </span>
                                                    @elseif($cliente->CliProcedencia == 'Llamada')
                                                        <span class="label label-primary">
                                                            <i class="fas fa-phone"></i> {{ $cliente->CliProcedencia }}
                                                        </span>
                                                    @elseif($cliente->CliProcedencia == 'Contacto en frío')
                                                        <span class="label label-info">
                                                            <i class="fas fa-snowflake"></i> {{ $cliente->CliProcedencia }}
                                                        </span>
                                                    @elseif($cliente->CliProcedencia == 'Campaña Redes Sociales')
                                                        <span class="label label-warning">
                                                            <i class="fas fa-share-alt"></i> {{ $cliente->CliProcedencia }}
                                                        </span>
                                                    @elseif($cliente->CliProcedencia == 'Referido')
                                                        <span class="label" style="background-color: #605ca8;">
                                                            <i class="fas fa-user-friends"></i> {{ $cliente->CliProcedencia }}
                                                        </span>
                                                    @else
                                                        <span class="label label-default">
                                                            <i class="fas fa-question-circle"></i> {{ $cliente->CliProcedencia }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin procedencia</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="label label-info">{{ $cliente->CliCategoria }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-blue">{{ $cliente->sedes->count() }}</span>
                                            </td>
                                            <td>
                                                @if($cliente->CliStatus == 'Autorizado')
                                                    <span class="label label-success">{{ $cliente->CliStatus }}</span>
                                                @else
                                                    <span class="label label-warning">{{ $cliente->CliStatus }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('crm.clientes.show', $cliente->CliSlug) }}" 
                                                   class="btn btn-xs btn-info" 
                                                   title="Ver detalles">
                                                    <i class="fa fa-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('cliente-show', $cliente->CliSlug) }}" 
                                                   class="btn btn-xs btn-primary" 
                                                   title="Ver completo">
                                                    <i class="fa fa-external-link-alt"></i> Completo
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center" style="padding: 60px;">
                            <i class="fas fa-user-plus fa-4x text-muted"></i>
                            <h3 style="color: #999; margin-top: 20px;">
                                @if($stats['verTodos'])
                                    No tienes clientes nuevos en el año {{ $stats['anioActual'] }}
                                @else
                                    No tienes clientes nuevos en {{ $stats['mesActual'] }}
                                @endif
                            </h3>
                            <p style="color: #999;">Tus clientes nuevos aparecerán aquí</p>
                            @if(!$stats['verTodos'])
                                <a href="{{ route('crm.mis-clientes-nuevos', ['todos' => true, 'anio' => $stats['anioActual']]) }}" 
                                   class="btn btn-success" style="margin-right: 10px;">
                                    <i class="fas fa-list"></i> Ver Todos del Año {{ $stats['anioActual'] }}
                                </a>
                            @endif
                            <a href="{{ route('crm.dashboard') }}" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $hayClientesParaDataTable = $clientesNuevos->count() > 0;
@endphp

@push('styles')
<style>
    .bg-green { background-color: #00a65a !important; }
    .bg-teal { background-color: #39cccc !important; }
    .bg-blue { background-color: #3c8dbc !important; }
    
    #clientesNuevosTable tbody tr:hover {
        background-color: #f5f5f5;
        cursor: pointer;
    }
</style>
@endpush

@if($hayClientesParaDataTable)
@push('scripts')
<!-- DataTable initialization script -->
<script type="text/javascript">
(function() {
    'use strict';
    $(document).ready(function() {
        $('#clientesNuevosTable').DataTable({
            'paging': true,
            'lengthChange': true,
            'searching': true,
            'ordering': true,
            'info': true,
            'autoWidth': false,
            'language': {
                'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            'order': [[0, 'desc']],
            'pageLength': 25
        });
    });
})();
</script>
@endpush
@endif
@endsection

