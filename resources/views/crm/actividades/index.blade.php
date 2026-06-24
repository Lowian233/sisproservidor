@extends('layouts.app')

@section('htmlheader_title')
Actividades - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #43e97b, #38f9d7); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-tasks"></i> Actividades
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-calendar"></i> Gestión de Actividades
                    </h3>
                    <div class="box-tools pull-right">
                        @if($soloClientesNuevos ?? false)
                            <a href="{{ route('crm.actividades.index') }}" class="btn btn-default btn-sm">
                                <i class="fas fa-users"></i> Ver todas
                            </a>
                        @else
                            <a href="{{ route('crm.actividades.index', ['cliente_nuevo' => 1]) }}" class="btn btn-default btn-sm">
                                <i class="fas fa-user-plus"></i> Solo clientes nuevos
                            </a>
                        @endif
                        <a href="{{ route('crm.actividades.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> Nueva Actividad
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    @if($soloClientesNuevos ?? false)
                        <div class="alert alert-info" style="margin-bottom: 15px;">
                            <i class="fas fa-user-plus"></i>
                            Mostrando solo actividades de <strong>clientes nuevos</strong> (creados en {{ now()->translatedFormat('F Y') }}).
                            <a href="{{ route('crm.actividades.index') }}" class="alert-link">Ver todas las actividades</a>
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <label>Estado</label>
                            <select class="form-control" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Completada">Completada</option>
                                <option value="Cancelada">Cancelada</option>
                                <option value="En Progreso">En Progreso</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Tipo</label>
                            <select class="form-control" id="filtroTipo">
                                <option value="">Todos</option>
                                <option value="Llamada">Llamada</option>
                                <option value="Visita">Visita</option>
                                <option value="Email">Email</option>
                                <option value="Tarea">Tarea</option>
                                <option value="Reunión">Reunión</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Fecha</label>
                            <input type="date" class="form-control" id="filtroFecha">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" onclick="aplicarFiltros()">
                                <i class="fa fa-filter"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>

                    <!-- Lista de Actividades -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>Cliente</th>
                                    <th>Fecha Programada</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actividades as $actividad)
                                    <tr>
                                        <td>
                                            @switch($actividad->ActTipo)
                                                @case('Llamada')
                                                    <i class="fas fa-phone text-primary fa-lg"></i>
                                                    @break
                                                @case('Visita')
                                                    <i class="fas fa-map-marker-alt text-success fa-lg"></i>
                                                    @break
                                                @case('Email')
                                                    <i class="fas fa-envelope text-info fa-lg"></i>
                                                    @break
                                                @case('Reunión')
                                                    <i class="fas fa-users text-warning fa-lg"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-tasks text-secondary fa-lg"></i>
                                            @endswitch
                                            <br>
                                            <small>{{ $actividad->ActTipo }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $actividad->ActTitulo }}</strong>
                                            @if($actividad->ActDescripcion)
                                                <br>
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($actividad->ActDescripcion, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $actividad->cliente->CliShortname ?? $actividad->cliente->CliName }}
                                        </td>
                                        <td>
                                            <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($actividad->ActFechaProgramada)->format('d/m/Y') }}
                                            <br>
                                            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($actividad->ActFechaProgramada)->format('H:i') }}
                                        </td>
                                        <td>
                                            @if($actividad->ActEstado == 'Pendiente')
                                                <span class="label label-warning">{{ $actividad->ActEstado }}</span>
                                            @elseif($actividad->ActEstado == 'Completada')
                                                <span class="label label-success">{{ $actividad->ActEstado }}</span>
                                            @elseif($actividad->ActEstado == 'Cancelada')
                                                <span class="label label-danger">{{ $actividad->ActEstado }}</span>
                                            @else
                                                <span class="label label-info">{{ $actividad->ActEstado }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('crm.actividades.edit', $actividad->ID_Actividad) }}" class="btn btn-xs btn-primary" title="Editar actividad">
                                                <i class="fa fa-edit"></i> Editar
                                            </a>
                                            @if($actividad->ActResultado)
                                                <button type="button" class="btn btn-xs btn-info" data-toggle="tooltip" title="{{ $actividad->ActResultado }}">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="padding: 40px;">
                                            <i class="fas fa-calendar-times fa-3x text-muted"></i>
                                            <p style="margin-top: 15px; color: #999;">No hay actividades registradas</p>
                                            <a href="{{ route('crm.actividades.create') }}" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Crear Primera Actividad
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($actividades->hasPages())
                        <div class="text-center">
                            {{ $actividades->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function aplicarFiltros() {
    var estado = $('#filtroEstado').val();
    var tipo = $('#filtroTipo').val();
    var fecha = $('#filtroFecha').val();
    var clienteNuevo = {{ ($soloClientesNuevos ?? false) ? '1' : '0' }};

    var url = '{{ route("crm.actividades.index") }}?';
    if(estado) url += 'estado=' + estado + '&';
    if(tipo) url += 'tipo=' + tipo + '&';
    if(fecha) url += 'fecha=' + fecha + '&';
    if(clienteNuevo) url += 'cliente_nuevo=1&';

    window.location.href = url;
}

$(document).ready(function() {
    // Inicializar tooltips de Bootstrap si existen
    if(typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
});
</script>
@endpush
@endsection





