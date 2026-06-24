@extends('layouts.app')

@section('htmlheader_title')
Nueva Actividad - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #43e97b, #38f9d7); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-plus-circle"></i> Nueva Actividad
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
                        <i class="fas fa-calendar-plus"></i> Crear Nueva Actividad
                    </h3>
                </div>
                <form action="{{ route('crm.actividades.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Tipo de cliente</label>
                                    <div>
                                        <label class="radio-inline">
                                            <input type="radio" name="tipo_cliente" value="existente" checked> Cliente existente
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="tipo_cliente" value="nuevo"> Cliente nuevo (solo nombre, completar después)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Actividad <span class="text-danger">*</span></label>
                                    <select name="ActTipo" class="form-control select-native" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Llamada">Llamada</option>
                                        <option value="Visita">Visita</option>
                                        <option value="Email">Email</option>
                                        <option value="Tarea">Tarea</option>
                                        <option value="Reunión">Reunión</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="wrap-select-cliente">
                                <div class="form-group">
                                    <label>Cliente <span class="text-danger">*</span></label>
                                    <select name="FK_ActCliente" id="FK_ActCliente" class="form-control select2">
                                        <option value="">Seleccione un cliente...</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->ID_Cli }}" {{ request('cliente') == $cliente->ID_Cli ? 'selected' : '' }}>
                                                {{ $cliente->CliShortname ?? $cliente->CliName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="bloque-cliente-nuevo" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre del cliente nuevo <span class="text-danger">*</span></label>
                                        <input type="text" name="cliente_nuevo_nombre" id="cliente_nuevo_nombre" class="form-control" placeholder="Ej: Empresa XYZ S.A.S." maxlength="255">
                                        <small class="text-muted">Los datos completos se pueden agregar después desde la ficha del cliente.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Estado del contacto <span class="text-danger">*</span></label>
                                        <select name="cliente_nuevo_estado" id="cliente_nuevo_estado" class="form-control select-native">
                                            <option value="">Seleccione...</option>
                                            <option value="Prospecto">Prospecto</option>
                                            <option value="Activo">Activo</option>
                                            <option value="Reactivación">Reactivación</option>
                                        </select>
                                        <small class="text-muted">Prospecto = posible cliente; Activo = en proceso; Reactivación = cliente que retoma relación.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Título <span class="text-danger">*</span></label>
                            <input type="text" name="ActTitulo" class="form-control" placeholder="Ej: Seguimiento cotización #123" required>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="ActDescripcion" class="form-control" rows="3" placeholder="Detalles adicionales de la actividad..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha y Hora Programada <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ActFechaProgramada" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cotización Relacionada (Opcional)</label>
                                    <select name="FK_ActCotizacion" class="form-control select2">
                                        <option value="">Ninguna</option>
                                        @foreach($cotizaciones as $cotizacion)
                                            <option value="{{ $cotizacion->id_cotizacion }}">
                                                #{{ $cotizacion->id_cotizacion }} - {{ $cotizacion->Razon_Social }} (${{ number_format($cotizacion->Total, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('crm.actividades.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success pull-right">
                            <i class="fa fa-save"></i> Guardar Actividad
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
    // Toggle cliente existente / cliente nuevo
    var $wrapSelect = $('#wrap-select-cliente');
    var $nuevo = $('#bloque-cliente-nuevo');
    var $selectCliente = $('#FK_ActCliente');
    var $nombreNuevo = $('#cliente_nuevo_nombre');

    $('input[name="tipo_cliente"]').on('change', function() {
        var esNuevo = $(this).val() === 'nuevo';
        $wrapSelect.toggle(!esNuevo);
        $nuevo.toggle(esNuevo);
        $selectCliente.prop('required', !esNuevo);
        $nombreNuevo.prop('required', esNuevo);
        if (esNuevo) $selectCliente.val('');
        else $nombreNuevo.val('');
    });

    $('form').on('submit', function() {
        if ($('input[name="tipo_cliente"]:checked').val() === 'nuevo') {
            $(this).append('<input type="hidden" name="cliente_nuevo" value="1">');
        }
    });

    // Establecer fecha/hora por defecto (ahora + 1 hora)
    var now = new Date();
    now.setHours(now.getHours() + 1);
    var datetime = now.toISOString().slice(0, 16);
    $('input[name="ActFechaProgramada"]').val(datetime);

    // Inicializar Select2 si está disponible
    if ($.fn.select2) {
        $('.select2').select2();
    }
});
</script>
@endpush
@endsection