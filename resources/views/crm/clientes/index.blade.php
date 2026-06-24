@extends('layouts.app')

@section('htmlheader_title')
{{ isset($comercialFiltrado) ? 'Clientes de ' . trim($comercialFiltrado->PersFirstName . ' ' . $comercialFiltrado->PersLastName) : (isset($verTodosEquipo) && $verTodosEquipo ? 'Todos los Clientes del Equipo' : 'Mis Clientes') }} - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #667eea, #764ba2); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-users"></i> {{ isset($comercialFiltrado) ? 'Clientes de ' . trim($comercialFiltrado->PersFirstName . ' ' . $comercialFiltrado->PersLastName) : (isset($verTodosEquipo) && $verTodosEquipo ? 'Todos los Clientes del Equipo' : 'Mis Clientes') }}
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
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-users"></i> Lista de Clientes
                    </h3>
                    <div class="box-tools pull-right">
                        @if(isset($comercialFiltrado) || (isset($verTodosEquipo) && $verTodosEquipo))
                            <a href="{{ route('crm.gerencia') }}#equipo-comercial" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Volver a Equipo Comercial
                            </a>
                        @endif
                        <div class="btn-group" style="margin-right: 5px;">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-sort"></i> Ordenar <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                @php $qp = $queryParams ?? []; @endphp
                                <li><a href="{{ route('crm.clientes.index', array_merge($qp, ['orden' => 'nombre'])) }}"><i class="fas fa-sort-alpha-down"></i> Por nombre</a></li>
                                <li><a href="{{ route('crm.clientes.index', array_merge($qp, ['orden' => 'ultima_reciente'])) }}"><i class="fas fa-clock"></i> Última solicitud más reciente</a></li>
                                <li><a href="{{ route('crm.clientes.index', array_merge($qp, ['orden' => 'ultima_antigua'])) }}"><i class="fas fa-history"></i> Última solicitud más antigua</a></li>
                                <li><a href="{{ route('crm.clientes.index', array_merge($qp, ['orden' => 'sin_solicitudes'])) }}"><i class="fas fa-exclamation-triangle"></i> Sin solicitudes primero</a></li>
                            </ul>
                        </div>
                        @if(($totalDesactivados ?? 0) > 0)
                            @if($verDesactivados ?? false)
                                @php $paramsSinDesactivados = array_filter($queryParams ?? [], fn($v,$k) => $k !== 'desactivados', ARRAY_FILTER_USE_BOTH); @endphp
                                <a href="{{ route('crm.clientes.index', $paramsSinDesactivados) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye-slash"></i> Ocultar desactivados
                                </a>
                            @else
                                <a href="{{ route('crm.clientes.index', array_merge($queryParams ?? [], ['desactivados' => '1'])) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-eye"></i> Ver desactivados ({{ $totalDesactivados ?? 0 }})
                                </a>
                            @endif
                        @endif
                        <a href="{{ route('clientes.create') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Crear Cliente
                        </a>
                        <div class="has-feedback" style="margin-left: 10px; display: inline-block;">
                            <input type="text" class="form-control input-sm" placeholder="Buscar cliente..." id="searchClientes">
                            <span class="fa fa-search form-control-feedback"></span>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    @if(isset($comercialFiltrado))
                    <div class="alert alert-info" style="margin-bottom: 15px;">
                        <i class="fas fa-user-tie"></i>
                        Viendo los clientes asignados a <strong>{{ trim($comercialFiltrado->PersFirstName . ' ' . $comercialFiltrado->PersLastName) }}</strong>.
                    </div>
                    @elseif(isset($verTodosEquipo) && $verTodosEquipo)
                    <div class="alert alert-info" style="margin-bottom: 15px;">
                        <i class="fas fa-users"></i>
                        Viendo <strong>todos los clientes del equipo comercial</strong>. Para ver solo los de un comercial, usa el enlace "Equipo Comercial" y luego "Ver clientes" del comercial deseado.
                    </div>
                    @endif

                    @php
                        $qp = $queryParams ?? [];
                        $qpTodos = array_diff_key($qp, array_flip(['ver']));
                        $qpNuevos = array_merge($qp, ['ver' => 'nuevos']);
                    @endphp
                    <!-- Pestañas: Todos | Clientes nuevos (badges = solo ACTIVOS) -->
                    <ul class="nav nav-tabs" style="margin-bottom: 20px;">
                        <li class="{{ !$verNuevos ? 'active' : '' }}">
                            <a href="{{ route('crm.clientes.index', array_filter($qpTodos)) }}">
                                <i class="fas fa-users"></i> {{ isset($comercialFiltrado) ? 'Todos los clientes' : ((isset($verTodosEquipo) && $verTodosEquipo) ? 'Todos los clientes del equipo' : 'Todos mis clientes') }}
                                <span class="badge" title="Solo activos">{{ $totalActivos ?? $clientes->count() }}</span>
                                @if(($totalDesactivados ?? 0) > 0)
                                    <small class="text-muted">({{ $totalDesactivados }} desactiv.)</small>
                                @endif
                            </a>
                        </li>
                        <li class="{{ $verNuevos ? 'active' : '' }}">
                            <a href="{{ route('crm.clientes.index', array_filter($qpNuevos)) }}">
                                <i class="fas fa-user-plus"></i> Clientes nuevos (este mes)
                                <span class="badge" style="background-color: #00a65a;">{{ $clientesNuevosActivos ?? $clientesNuevos->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    @if($verNuevos)
                    <div class="alert alert-info" style="margin-bottom: 15px;">
                        <i class="fas fa-info-circle"></i>
                        Clientes creados en {{ now()->translatedFormat('F Y') }}.
                        @if(!isset($comercialFiltrado))
                        <a href="{{ route('crm.mis-clientes-nuevos') }}" class="alert-link">Ver por mes / año y más opciones</a>
                        @else
                        <a href="{{ route('crm.gerencia.clientes-nuevos-mes') }}" class="alert-link">Ver clientes nuevos por mes / año (Gerencia)</a>
                        @endif
                    </div>
                    @endif

                    <div class="row" id="clientesContainer">
                        @foreach($verNuevos ? $clientesNuevos : $clientes as $cliente)
                            @php $estaDesactivado = ($cliente->CliActivo ?? 1) == 0; @endphp
                            <div class="col-md-4 cliente-card" data-cliente="{{ strtolower($cliente->CliName) }}">
                                <div class="box box-widget widget-user-2 {{ $estaDesactivado ? 'box-default' : '' }}" style="cursor: pointer; {{ $estaDesactivado ? 'opacity: 0.85;' : '' }}" data-href="{{ route('crm.clientes.show', $cliente->CliSlug) }}">
                                    <div class="widget-user-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                        <div class="widget-user-image">
                                            <span class="fa-stack fa-2x">
                                                <i class="fa fa-circle fa-stack-2x" style="color: rgba(255,255,255,0.3);"></i>
                                                <i class="fa fa-building fa-stack-1x"></i>
                                            </span>
                                        </div>
                                        <h3 class="widget-user-username" style="margin: 0; font-size: 16px;">
                                            {{ $cliente->CliShortname ?? $cliente->CliName }}
                                            @if($estaDesactivado)
                                                <span class="label label-warning" style="font-size: 10px;">Desactivado</span>
                                            @endif
                                        </h3>
                                        <h5 class="widget-user-desc" style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.9;">
                                            {{ $cliente->CliName }}
                                        </h5>
                                    </div>
                                    <div class="box-footer no-padding">
                                        <ul class="nav nav-stacked">
                                            @php
                                                $ultimaSol = $cliente->ultimaSolicitud;
                                                $fechaUltimaSol = $ultimaSol ? \Carbon\Carbon::parse($ultimaSol->created_at) : null;
                                                $mesesSinActividad = $fechaUltimaSol ? now()->diffInMonths($fechaUltimaSol) : null;
                                                $alerta6Meses = $mesesSinActividad !== null && $mesesSinActividad >= 6;
                                            @endphp
                                            @if($ultimaSol)
                                            <li>
                                                <a href="{{ route('solicitud-servicio.show', $ultimaSol->SolSerSlug) }}" onclick="event.stopPropagation();">
                                                    <i class="fas fa-file-invoice {{ $alerta6Meses ? 'text-danger' : 'text-muted' }}"></i> Última solicitud
                                                    <span class="pull-right {{ $alerta6Meses ? 'text-danger font-weight-bold' : '' }}" title="{{ $fechaUltimaSol->format('d/m/Y H:i') }}">
                                                        {{ $fechaUltimaSol->diffForHumans() }}
                                                    </span>
                                                </a>
                                            </li>
                                            @else
                                            <li>
                                                <a href="#" style="color: #d9534f;">
                                                    <i class="fas fa-file-invoice text-danger"></i> Última solicitud
                                                    <span class="pull-right text-danger font-weight-bold">Sin solicitudes</span>
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <a href="#">
                                                    <i class="fas fa-map-marker-alt text-primary"></i> Sedes
                                                    <span class="pull-right badge bg-blue">{{ $cliente->sedes->count() }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('crm.clientes.show', $cliente->CliSlug) }}">
                                                    <i class="fas fa-eye text-info"></i> Ver Detalles
                                                    <span class="pull-right"><i class="fa fa-arrow-right"></i></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('solicitud-servicio.create') }}?ID_Cli={{ $cliente->ID_Cli }}" onclick="event.stopPropagation();">
                                                    <i class="fas fa-file-invoice text-success"></i> Crear Solicitud
                                                    <span class="pull-right"><i class="fa fa-plus"></i></span>
                                                </a>
                                            </li>
                                            <li style="border-top: 1px solid #eee;">
                                                @if($estaDesactivado)
                                                    <form action="{{ url('crm/clientes/' . $cliente->CliSlug . '/toggle-activo') }}" method="post" style="margin: 0;" onclick="event.stopPropagation();">
                                                        @csrf
                                                        <input type="hidden" name="activo" value="1">
                                                        @foreach($queryParams ?? [] as $k => $v)
                                                            @if($v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                                                        @endforeach
                                                        <button type="submit" class="btn btn-success btn-xs btn-block">
                                                            <i class="fas fa-check-circle"></i> Activar (volverá a contar)
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ url('crm/clientes/' . $cliente->CliSlug . '/toggle-activo') }}" method="post" style="margin: 0;" onclick="event.stopPropagation();">
                                                        @csrf
                                                        <input type="hidden" name="activo" value="0">
                                                        @foreach($queryParams ?? [] as $k => $v)
                                                            @if($v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                                                        @endforeach
                                                        <button type="submit" class="btn btn-warning btn-xs btn-block" onclick="return confirm('¿Desactivar este cliente? Ya no contará en el total hasta que lo reactive.');">
                                                            <i class="fas fa-pause-circle"></i> Desactivar
                                                        </button>
                                                    </form>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if(($verNuevos ? $clientesNuevos->count() : $clientes->count()) == 0)
                        <div class="text-center" style="padding: 60px;">
                            @if($verNuevos)
                                <i class="fas fa-user-plus fa-4x text-muted"></i>
                                <h3 style="color: #999; margin-top: 20px;">No tienes clientes nuevos este mes</h3>
                                <p style="color: #999;">Los clientes que crees en {{ now()->translatedFormat('F Y') }} aparecerán aquí.</p>
                                <a href="{{ route('crm.mis-clientes-nuevos') }}" class="btn btn-success"><i class="fas fa-list"></i> Ver clientes nuevos por mes/año</a>
                            @else
                                <i class="fas fa-users fa-4x text-muted"></i>
                                <h3 style="color: #999; margin-top: 20px;">No tienes clientes asignados</h3>
                                <p style="color: #999;">Contacta al administrador para asignarte clientes</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#searchClientes').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.cliente-card').filter(function() {
            $(this).toggle($(this).data('cliente').indexOf(value) > -1);
        });
    });
    
    // Manejar click en las tarjetas de cliente
    $('.widget-user-2[data-href]').on('click', function() {
        window.location.href = $(this).data('href');
    });
});
</script>
@endpush
@endsection