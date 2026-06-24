@extends('layouts.app')

@section('htmlheader_title')
Editar Actividad - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #43e97b, #38f9d7); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-edit"></i> Editar Actividad
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-edit"></i> Editar Actividad
                    </h3>
                </div>
                <form action="{{ route('crm.actividades.update', $actividad->ID_Actividad) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Actividad <span class="text-danger">*</span></label>
                                    <select name="ActTipo" class="form-control select-native" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Llamada" {{ $actividad->ActTipo == 'Llamada' ? 'selected' : '' }}>Llamada</option>
                                        <option value="Visita" {{ $actividad->ActTipo == 'Visita' ? 'selected' : '' }}>Visita</option>
                                        <option value="Email" {{ $actividad->ActTipo == 'Email' ? 'selected' : '' }}>Email</option>
                                        <option value="Tarea" {{ $actividad->ActTipo == 'Tarea' ? 'selected' : '' }}>Tarea</option>
                                        <option value="Reunión" {{ $actividad->ActTipo == 'Reunión' ? 'selected' : '' }}>Reunión</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estado <span class="text-danger">*</span></label>
                                    <select name="ActEstado" class="form-control select-native" required>
                                        <option value="Pendiente" {{ $actividad->ActEstado == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="En Progreso" {{ $actividad->ActEstado == 'En Progreso' ? 'selected' : '' }}>En Progreso</option>
                                        <option value="Completada" {{ $actividad->ActEstado == 'Completada' ? 'selected' : '' }}>Completada</option>
                                        <option value="Cancelada" {{ $actividad->ActEstado == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente <span class="text-danger">*</span></label>
                                    <select name="FK_ActCliente" class="form-control select2" required>
                                        <option value="">Seleccione un cliente...</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->ID_Cli }}" {{ $actividad->FK_ActCliente == $cliente->ID_Cli ? 'selected' : '' }}>
                                                {{ $cliente->CliShortname ?? $cliente->CliName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cotización Relacionada (Opcional)</label>
                                    <select name="FK_ActCotizacion" class="form-control select2">
                                        <option value="">Ninguna</option>
                                        @foreach($cotizaciones as $cotizacion)
                                            <option value="{{ $cotizacion->id_cotizacion }}" {{ $actividad->FK_ActCotizacion == $cotizacion->id_cotizacion ? 'selected' : '' }}>
                                                #{{ $cotizacion->id_cotizacion }} - {{ $cotizacion->Razon_Social }} (${{ number_format($cotizacion->Total, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Título <span class="text-danger">*</span></label>
                            <input type="text" name="ActTitulo" class="form-control" value="{{ old('ActTitulo', $actividad->ActTitulo) }}" placeholder="Ej: Seguimiento cotización #123" required>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="ActDescripcion" class="form-control" rows="3" placeholder="Detalles adicionales de la actividad...">{{ old('ActDescripcion', $actividad->ActDescripcion) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha y Hora Programada <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ActFechaProgramada" class="form-control" value="{{ old('ActFechaProgramada', \Carbon\Carbon::parse($actividad->ActFechaProgramada)->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            @if($actividad->ActFechaCompletada)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha y Hora Completada</label>
                                    <input type="datetime-local" class="form-control" value="{{ \Carbon\Carbon::parse($actividad->ActFechaCompletada)->format('Y-m-d\TH:i') }}" disabled>
                                    <small class="text-muted">Se estableció automáticamente al marcar como completada</small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Resultado / Notas</label>
                            <textarea name="ActResultado" class="form-control" rows="4" placeholder="Describe el resultado de la actividad, notas importantes, próximos pasos...">{{ old('ActResultado', $actividad->ActResultado) }}</textarea>
                            <small class="text-muted">Este campo es especialmente útil cuando marcas la actividad como completada</small>
                        </div>

                        <!-- Información adicional -->
                        <div class="alert alert-info">
                            <h4 style="margin-top: 0;"><i class="icon fa fa-info"></i> Información de la Actividad</h4>
                            <p><strong>Cliente:</strong> {{ $actividad->cliente->CliShortname ?? $actividad->cliente->CliName }}</p>
                            <p><strong>Creada:</strong> {{ \Carbon\Carbon::parse($actividad->created_at)->format('d/m/Y H:i') }}</p>
                            @if($actividad->updated_at != $actividad->created_at)
                                <p><strong>Última actualización:</strong> {{ \Carbon\Carbon::parse($actividad->updated_at)->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('crm.actividades.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success pull-right">
                            <i class="fa fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar Select2 si está disponible
    if ($.fn.select2) {
        $('.select2').select2();
    }
    
    // Si se selecciona "Completada", mostrar mensaje sobre fecha de completada
    $('select[name="ActEstado"]').on('change', function() {
        if ($(this).val() === 'Completada') {
            // La fecha se establecerá automáticamente en el servidor
        }
    });
});
</script>
@endpush
@endsection


