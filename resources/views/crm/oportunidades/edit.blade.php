@extends('layouts.app')

@section('htmlheader_title')
Editar Oportunidad - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #f093fb, #f5576c); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-edit"></i> Editar Oportunidad
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
                        <i class="fas fa-edit"></i> Editar Oportunidad
                    </h3>
                </div>
                <form action="{{ route('crm.oportunidades.update', $oportunidad->ID_Oportunidad) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group">
                            <label>Título de la Oportunidad <span class="text-danger">*</span></label>
                            <input type="text" name="OportTitulo" class="form-control" value="{{ old('OportTitulo', $oportunidad->OportTitulo) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="OportDescripcion" class="form-control" rows="4">{{ old('OportDescripcion', $oportunidad->OportDescripcion) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cliente <span class="text-danger">*</span></label>
                                    <select name="FK_OportCliente" class="form-control select2" required>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->ID_Cli }}" {{ old('FK_OportCliente', $oportunidad->FK_OportCliente) == $cliente->ID_Cli ? 'selected' : '' }}>
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
                                            <option value="{{ $cotizacion->id_cotizacion }}" {{ old('FK_OportCotizacion', $oportunidad->FK_OportCotizacion) == $cotizacion->id_cotizacion ? 'selected' : '' }}>
                                                #{{ $cotizacion->id_cotizacion }} - {{ $cotizacion->Razon_Social }} (${{ number_format($cotizacion->Total ?? 0, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Valor Estimado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" name="OportValorEstimado" class="form-control" min="0" step="0.01" value="{{ old('OportValorEstimado', $oportunidad->OportValorEstimado) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Probabilidad (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="OportProbabilidad" class="form-control" min="0" max="100" value="{{ old('OportProbabilidad', $oportunidad->OportProbabilidad) }}" required>
                                    <div class="progress" style="margin-top: 5px;">
                                        <div class="progress-bar" role="progressbar" id="probabilidadBar" style="width: {{ $oportunidad->OportProbabilidad }}%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Etapa <span class="text-danger">*</span></label>
                                    <select name="OportEtapa" class="form-control select-native" required>
                                        @foreach(\App\CrmOportunidadV2::ETAPAS as $etapa)
                                            <option value="{{ $etapa }}" {{ old('OportEtapa', $oportunidad->OportEtapa) == $etapa ? 'selected' : '' }}>{{ $etapa }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fecha de Cierre Esperada</label>
                            <input type="date" name="OportFechaCierreEsperada" class="form-control" value="{{ old('OportFechaCierreEsperada', $oportunidad->OportFechaCierreEsperada ? \Carbon\Carbon::parse($oportunidad->OportFechaCierreEsperada)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="form-group">
                            <label>Notas Adicionales</label>
                            <textarea name="OportNotas" class="form-control" rows="3">{{ old('OportNotas', $oportunidad->OportNotas) }}</textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('crm.oportunidades.index') }}" class="btn btn-default">
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
    $('input[name="OportProbabilidad"]').on('input', function() {
        $('#probabilidadBar').css('width', $(this).val() + '%');
    });
    if ($.fn.select2) { $('.select2').select2(); }
});
</script>
@endpush
@endsection