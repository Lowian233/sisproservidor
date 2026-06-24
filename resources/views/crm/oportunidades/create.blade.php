@extends('layouts.app')

@section('htmlheader_title')
Nueva Oportunidad - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #f093fb, #f5576c); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-lightbulb"></i> Nueva Oportunidad
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
                        <i class="fas fa-plus-circle"></i> Crear Nueva Oportunidad
                    </h3>
                </div>
                <form action="{{ route('crm.oportunidades.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label>Título de la Oportunidad <span class="text-danger">*</span></label>
                            <input type="text" name="OportTitulo" class="form-control" placeholder="Ej: Nueva contratación de servicios de residuos" required>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="OportDescripcion" class="form-control" rows="4" placeholder="Describe la oportunidad de negocio..."></textarea>
                        </div>

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
                        <div id="bloque-cliente-existente">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cliente <span class="text-danger">*</span></label>
                                        <select name="FK_OportCliente" id="FK_OportCliente" class="form-control select2">
                                            <option value="">Seleccione un cliente...</option>
                                            @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->ID_Cli }}" {{ request('cliente') == $cliente->ID_Cli ? 'selected' : '' }}>
                                                    {{ $cliente->CliShortname ?? $cliente->CliName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cotización Relacionada (Opcional)</label>
                                        <select name="FK_OportCotizacion" class="form-control select2">
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
                        <div id="bloque-cliente-nuevo" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre del cliente nuevo <span class="text-danger">*</span></label>
                                        <input type="text" name="cliente_nuevo_nombre" id="cliente_nuevo_nombre" class="form-control" placeholder="Ej: Empresa XYZ S.A.S." maxlength="255">
                                        <small class="text-muted">Los datos completos (NIT, sedes, etc.) se pueden agregar después desde la ficha del cliente.</small>
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

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Valor Estimado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" name="OportValorEstimado" class="form-control" min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Probabilidad (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="OportProbabilidad" class="form-control" min="0" max="100" value="50" required>
                                    <div class="progress" style="margin-top: 5px;">
                                        <div class="progress-bar" role="progressbar" id="probabilidadBar" style="width: 50%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Etapa <span class="text-danger">*</span></label>
                                    <select name="OportEtapa" class="form-control select-native" required>
                                        <option value="Prospección">Prospección</option>
                                        <option value="Cotización">Cotización</option>
                                        <option value="Negociación">Negociación</option>
                                        <option value="Cierre">Cierre</option>
                                        <option value="Aprobado">Aprobado</option>
                                        <option value="Rechazado">Rechazado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fecha de Cierre Esperada</label>
                            <input type="date" name="OportFechaCierreEsperada" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Notas Adicionales</label>
                            <textarea name="OportNotas" class="form-control" rows="3" placeholder="Notas internas sobre la oportunidad..."></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('crm.oportunidades.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success pull-right">
                            <i class="fa fa-save"></i> Guardar Oportunidad
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
    var $existente = $('#bloque-cliente-existente');
    var $nuevo = $('#bloque-cliente-nuevo');
    var $selectCliente = $('#FK_OportCliente');
    var $nombreNuevo = $('#cliente_nuevo_nombre');

    $('input[name="tipo_cliente"]').on('change', function() {
        var esNuevo = $(this).val() === 'nuevo';
        $existente.toggle(!esNuevo);
        $nuevo.toggle(esNuevo);
        $selectCliente.prop('required', !esNuevo);
        $nombreNuevo.prop('required', esNuevo);
        if (esNuevo) $selectCliente.val('');
        else $nombreNuevo.val('');
    });

    // Campo oculto para indicar cliente nuevo al backend
    $('form').on('submit', function() {
        if ($('input[name="tipo_cliente"]:checked').val() === 'nuevo') {
            $(this).append('<input type="hidden" name="cliente_nuevo" value="1">');
        }
    });

    // Actualizar barra de probabilidad
    $('input[name="OportProbabilidad"]').on('input', function() {
        var valor = $(this).val();
        $('#probabilidadBar').css('width', valor + '%');
    });

    // Inicializar Select2 si está disponible
    if ($.fn.select2) {
        $('.select2').select2();
    }
});
</script>
@endpush
@endsection