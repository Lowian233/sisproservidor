@extends('layouts.app')

@section('htmlheader_title')
Oportunidades - CRM
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #f093fb, #f5576c); padding-right:30vw; position:relative; overflow:hidden;">
    <i class="fas fa-project-diagram"></i> Pipeline de Oportunidades
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
                        <i class="fas fa-chart-line"></i> Pipeline de Ventas
                    </h3>
                    <div class="box-tools pull-right">
                        @if($soloClientesNuevos ?? false)
                            <a href="{{ route('crm.oportunidades.index') }}" class="btn btn-default btn-sm">
                                <i class="fas fa-users"></i> Ver todas
                            </a>
                        @else
                            <a href="{{ route('crm.oportunidades.index', ['cliente_nuevo' => 1]) }}" class="btn btn-default btn-sm">
                                <i class="fas fa-user-plus"></i> Solo clientes nuevos
                            </a>
                        @endif
                        <a href="{{ route('crm.oportunidades.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> Nueva Oportunidad
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    @if($soloClientesNuevos ?? false)
                        <div class="alert alert-info" style="margin-bottom: 15px;">
                            <i class="fas fa-user-plus"></i>
                            Mostrando solo oportunidades de <strong>clientes nuevos</strong> (creados en {{ now()->translatedFormat('F Y') }}).
                            <a href="{{ route('crm.oportunidades.index') }}" class="alert-link">Ver todas las oportunidades</a>
                        </div>
                    @endif
                    @php
                        $coloresEtapa = [
                            'Prospección' => '#17a2b8',
                            'Cotización' => '#ffc107',
                            'Negociación' => '#fd7e14',
                            'Cierre' => '#28a745',
                            'Aprobado' => '#198754',
                            'Rechazado' => '#dc3545'
                        ];
                    @endphp
                    <!-- Totales del Pipeline por Estado -->
                    @if(isset($totalPipelinePorEtapa) && !empty($totalPipelinePorEtapa))
                    <div class="alert alert-default" style="margin-bottom: 15px; padding: 12px 15px;">
                        <strong><i class="fas fa-dollar-sign"></i> Total Pipeline por estado:</strong>
                        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                            @foreach($etapas ?? ['Prospección', 'Cotización', 'Negociación', 'Cierre', 'Aprobado', 'Rechazado'] as $etapa)
                                @php $total = $totalPipelinePorEtapa[$etapa] ?? 0; $color = $coloresEtapa[$etapa] ?? '#6c757d'; @endphp
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="display: inline-block; width: 10px; height: 10px; background: {{ $color }}; border-radius: 2px;"></span>
                                    <strong>{{ $etapa }}:</strong> ${{ number_format($total, 0, ',', '.') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <!-- Vista de Pipeline (Kanban) -->
                    <div class="row">
                        @php
                            $coloresEtapa = [
                                'Prospección' => '#17a2b8',
                                'Cotización' => '#ffc107',
                                'Negociación' => '#fd7e14',
                                'Cierre' => '#28a745',
                                'Aprobado' => '#198754',
                                'Rechazado' => '#dc3545'
                            ];
                        @endphp
                        @foreach(['Prospección', 'Cotización', 'Negociación', 'Cierre', 'Aprobado', 'Rechazado'] as $etapa)
                            @php
                                $colorEtapa = $coloresEtapa[$etapa] ?? '#6c757d';
                                $oportunidadesEtapa = isset($oportunidadesPorEtapa[$etapa]) ? $oportunidadesPorEtapa[$etapa] : collect();
                                $headingStyle = 'background-color: ' . $colorEtapa . '; color: white;';
                            @endphp
                            <div class="col-md-2">
                                <div class="panel panel-default" style="min-height: 600px;">
                                    <div class="panel-heading" data-color="{{ $colorEtapa }}" style="color: white;">
                                        <h4 style="margin: 0;">
                                            {{ $etapa }}
                                            <span class="badge pull-right" style="background: rgba(255,255,255,0.3);">
                                                {{ $oportunidadesEtapa->count() }}
                                            </span>
                                        </h4>
                                    </div>
                                    <div class="panel-body opportunity-drop-zone" data-etapa="{{ $etapa }}" data-color="{{ $colorEtapa }}" style="max-height: 550px; overflow-y: auto; padding: 10px; min-height: 80px;">
                                        @foreach($oportunidadesEtapa as $oportunidad)
                                            @php
                                                $probabilidad = intval($oportunidad->OportProbabilidad ?? 0);
                                            @endphp
                                            <div class="opportunity-card" draggable="true" data-color="{{ $colorEtapa }}" data-oportunidad-id="{{ $oportunidad->ID_Oportunidad }}" data-etapa="{{ $etapa }}" style="background: white; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: grab;">
                                                <h5 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #333;">
                                                    {{ $oportunidad->OportTitulo }}
                                                </h5>
                                                <p style="margin: 5px 0; color: #666; font-size: 12px;">
                                                    <i class="fas fa-building"></i> {{ $oportunidad->cliente->CliShortname ?? $oportunidad->cliente->CliName }}
                                                </p>
                                                <p style="margin: 5px 0; color: #28a745; font-weight: bold; font-size: 14px;">
                                                    ${{ number_format($oportunidad->OportValorEstimado, 0, ',', '.') }}
                                                </p>
                                                <div class="progress" style="height: 6px; margin: 8px 0; background-color: #e9ecef;">
                                                    <div class="progress-bar" role="progressbar" data-width="{{ $probabilidad }}" data-color="{{ $colorEtapa }}" style="width: 0%;"></div>
                                                </div>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-md-6">
                                                        <small style="color: #999; font-size: 10px;">
                                                            {{ $oportunidad->OportProbabilidad }}% prob.
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        @if($oportunidad->OportFechaCierreEsperada)
                                                            <small style="color: #999; font-size: 10px;">
                                                                <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($oportunidad->OportFechaCierreEsperada)->format('d/m/Y') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($oportunidad->OportDescripcion)
                                                    <p style="margin: 8px 0 0 0; color: #999; font-size: 11px; border-top: 1px solid #eee; padding-top: 8px;">
                                                        {{ \Illuminate\Support\Str::limit($oportunidad->OportDescripcion, 60) }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                        
                                        @if($oportunidadesEtapa->count() == 0)
                                            <div class="text-center" style="padding: 40px; color: #999;">
                                                <i class="fas fa-inbox fa-3x"></i>
                                                <p style="margin-top: 15px;">Sin oportunidades</p>
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
</div>

<!-- Modal Ver Oportunidad -->
<div class="modal fade" id="modalOportunidad" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Detalles de la Oportunidad</h4>
            </div>
            <div class="modal-body" id="modalOportunidadBody">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Aplicar color de fondo a los headings
    $('.panel-heading[data-color]').each(function() {
        var color = $(this).data('color');
        $(this).css('background-color', color);
    });
    
    // Aplicar borde izquierdo a las tarjetas
    $('.opportunity-card[data-color]').each(function() {
        var color = $(this).data('color');
        $(this).css('border-left', '4px solid ' + color);
    });
    
    // Aplicar ancho y color a las barras de progreso
    $('.progress-bar[data-width]').each(function() {
        var width = $(this).data('width');
        var color = $(this).data('color');
        $(this).css({
            'width': width + '%',
            'background-color': color
        });
    });
    
    // Clic en tarjeta: abrir detalle (no si acabamos de arrastrar)
    $('.opportunity-card[data-oportunidad-id]').on('click', function(e) {
        if (window._oportunidadRecienArrastrada) return;
        var id = $(this).data('oportunidad-id');
        verOportunidad(id);
    });

    // --- Arrastrar y soltar entre columnas ---
    var dragCard = null;
    var dragEtapa = null;

    $('.opportunity-card[draggable="true"]').on('dragstart', function(e) {
        dragCard = $(this);
        dragEtapa = $(this).data('etapa');
        e.originalEvent.dataTransfer.setData('text/plain', $(this).data('oportunidad-id'));
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        $(this).css('opacity', '0.6');
    }).on('dragend', function() {
        $(this).css('opacity', '1');
        dragCard = null;
    });

    $('.opportunity-drop-zone').on('dragover', function(e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        $(this).addClass('drop-zone-over');
    }).on('dragleave', function() {
        $(this).removeClass('drop-zone-over');
    }).on('drop', function(e) {
        e.preventDefault();
        var dropZone = $(this);
        dropZone.removeClass('drop-zone-over');
        var id = e.originalEvent.dataTransfer.getData('text/plain');
        if (!id) return;
        var nuevaEtapa = dropZone.data('etapa');
        if (nuevaEtapa === dragEtapa) return;

        var card = $('.opportunity-card[data-oportunidad-id="' + id + '"]').first();
        var color = dropZone.data('color');
        var url = '{{ url("crm/oportunidades") }}/' + id + '/etapa';
        var token = '{{ csrf_token() }}';

        $.post(url, { etapa: nuevaEtapa, _token: token })
            .done(function() {
                card.attr('data-etapa', nuevaEtapa).attr('data-color', color);
                card.find('.progress-bar').css({ 'width': card.find('.progress-bar').data('width') + '%', 'background-color': color });
                card.css('border-left-color', color);
                dropZone.append(card);
                window._oportunidadRecienArrastrada = true;
                setTimeout(function() { window._oportunidadRecienArrastrada = false; }, 300);
            })
            .fail(function() {
                alert('No se pudo cambiar la etapa. Intenta de nuevo.');
            });
    });
});

function verOportunidad(id) {
    $('#modalOportunidadBody').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando detalles...</p>');
    $('#modalOportunidad').modal('show');
    var url = '{{ url("crm/oportunidades") }}/' + id + '/detalle';
    $.get(url)
        .done(function(html) {
            $('#modalOportunidadBody').html(html);
        })
        .fail(function(xhr) {
            var msg = xhr.status === 404 ? 'Oportunidad no encontrada.' : 'No se pudieron cargar los detalles.';
            $('#modalOportunidadBody').html('<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + msg + '</p>');
        });
}
</script>
@endpush

@push('styles')
<style>
    .opportunity-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .opportunity-card:active { cursor: grabbing; }
    .opportunity-drop-zone.drop-zone-over {
        background-color: rgba(0,0,0,0.04);
        outline: 2px dashed #999;
    }
</style>
@endpush
@endsection