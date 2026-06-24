@extends('layouts.cotizaciones')

@section('htmlheader_title')
    Cotizaciones de servicio express
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 160, 100), rgb(252, 98, 98)); padding-right: 30vw; position: relative; overflow: hidden; color: #fff; display: block; padding-left: 15px;">
    Cotizaciones de servicio express
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="padding: 6px 10px; font-size:12px;">
            <button type="button" class="close" data-dismiss="alert" style="font-size:14px;">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-filter" style="color:#666;"></i> Filtro de busqueda</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('cotizacion-expres.index') }}">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Nombre" value="{{ request('nombre') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Fecha desde</label>
                            <div class="input-group">
                                <input type="text" name="fecha_desde" id="fecha_desde"
                                       class="form-control datepicker"
                                       placeholder="{{ now()->format('d/m/Y') }}"
                                       value="{{ request('fecha_desde') }}" autocomplete="off">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Fecha hasta</label>
                            <div class="input-group">
                                <input type="text" name="fecha_hasta" id="fecha_hasta"
                                       class="form-control datepicker"
                                       placeholder="{{ now()->format('d/m/Y') }}"
                                       value="{{ request('fecha_hasta') }}" autocomplete="off">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Servicio</label>
                            <input type="text" name="servicio" class="form-control"
                                   placeholder="Servicio" value="{{ request('servicio') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="">Seleccione...</option>
                                <option value="Pagado" {{ request('estado') == 'Pagado' ? 'selected' : '' }}>Pagado</option>
                                <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Cancelado" {{ request('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 text-right" style="padding-top: 19px;">
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="fa fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('cotizacion-expres.index') }}" class="btn btn-default btn-sm">
                            Limpiar
                        </a>
                        <a href="{{ route('cotizacion-expres.excel') }}" class="btn btn-success btn-sm" id="btn-descargar-reporte">
                            <i class="fa fa-download"></i> Descargar Reporte
                        </a>
                        <button type="button" class="btn btn-warning btn-sm" id="btn-enviar-reporte" style="margin-left:5px;">
                            <i class="fa fa-whatsapp"></i> Enviar por WhatsApp
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="box box-info" style="border-top:3px solid #0066cc;">
        <div style="padding:15px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="margin:0; font-size:16px; font-weight:600; color:#000;">
                <i class="fas fa-user" style="color:#333; margin-right:8px;"></i> Solicitudes
            </h3>
            {{-- <a href="{{ route('cotizacion-expres.create') }}" class="btn btn-success btn-sm" style="padding:8px 15px;">
                <i class="fa fa-plus"></i> Crear
            </a> --}}
        </div>
        <div class="box-body no-padding">
            <div>
                <table class="table table-hover" style="margin-bottom:0; font-size:13px; border-collapse:collapse;">
                    <thead style="background-color:#f4f4f4;">
                        <tr style="height:48px;">
                            <th style="width:70px; padding:12px; vertical-align:middle; text-align:center;">No. Sol.</th>
                            <th style="padding:12px; vertical-align:middle;">Empresa</th>
                            <th style="padding:12px; vertical-align:middle;">Teléfono</th>
                            <th style="padding:12px; vertical-align:middle;">Tipo de residuo</th>
                            <th style="padding:12px; vertical-align:middle;">Localidad</th>
                            <th style="padding:12px; vertical-align:middle;">Sede recolección</th>
                            <th style="padding:12px; vertical-align:middle;">Peso</th>
                            <th style="padding:12px; vertical-align:middle; text-align:right;">Precio</th>
                            <th style="padding:12px; vertical-align:middle; text-align:center;">Req. Contrato Comercial</th>
                            <th style="padding:12px; vertical-align:middle; text-align:center;">Estado</th>
                            <th style="width:90px; padding:12px; vertical-align:middle; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes as $item)
                        @php
                            $badgeColor = match($item->estado ?? '') {
                                'Pagado'     => '#00a65a',
                                'Cancelado'  => '#dd4b39',
                                'En proceso' => '#0066cc',
                                default      => '#f39c12',
                            };
                        @endphp
                        <tr style="border-bottom: 1px solid #f0f0f0; height:48px;">
                            <td style="padding:12px; vertical-align:middle; text-align:center; font-weight:600; color:#0066cc;">
                                #{{ $item->idSolicitud }}
                            </td>
                            <td style="padding:12px; vertical-align:middle;">{{ $item->nombreEmpresa ?? '-' }}</td>
                            <td style="padding:12px; vertical-align:middle;">{{ $item->telefono ?? '-' }}</td>
                            <td style="padding:12px; vertical-align:middle; font-size:12px;">{{ $item->tiposResiduo ?? '-' }}</td>
                            <td style="padding:12px; vertical-align:middle;">{{ $item->localidad ?? '-' }}</td>
                            <td style="padding:12px; vertical-align:middle;">
                                @if($item->sede)
                                    <span style="background:#e8f4fd; color:#0066cc; padding:3px 8px; border-radius:10px; font-size:11px;">
                                        <i class="fa fa-map-marker"></i> {{ $item->sede }}
                                    </span>
                                @else
                                    <span style="color:#999; font-size:11px; font-style:italic;">Sin sede definida</span>
                                    @if($item->direccion)
                                        <div style="font-size:11px; color:#666; margin-top:2px;">
                                            <i class="fa fa-map-marker" style="color:#aaa;"></i> {{ $item->direccion }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td style="padding:12px; vertical-align:middle;">{{ $item->peso ?? '-' }}</td>
                            <td style="padding:12px; vertical-align:middle; text-align:right;">
                                @if($item->precio && $item->precio !== 'Precio no definido')
                                    ${{ number_format((float)$item->precio, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="padding:12px; text-align:center; vertical-align:middle;">
                                @if($item->RequiereContrato === 'Si')
                                    <span style="background:#00a65a; color:#fff; padding:3px 8px; border-radius:10px; font-size:11px;">Si</span>
                                @elseif($item->RequiereContrato === 'No')
                                    <span style="color:#dd4b39; font-size:12px;">No</span>
                                @else
                                    <span style="color:#aaa; font-size:12px;">Desconocido</span>
                                @endif
                            </td>
                            <td style="padding:12px; text-align:center; vertical-align:middle;">
                                <span style="background:{{ $badgeColor }}; color:#fff; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600; display:inline-block; min-width:75px;">
                                    {{ $item->estado ?? 'Pendiente' }}
                                </span>
                            </td>
                            <td style="padding:12px; text-align:center; vertical-align:middle; white-space:nowrap;">
                                @php
                                    if ($item->idCliente) {
                                        $slugShow = \Illuminate\Support\Str::slug($item->nombreEmpresa ?? $item->nit ?? 'cliente') . '-' . $item->idCliente;
                                        $urlShow = route('cotizacion-expres.show', $slugShow) . '?solicitud=' . $item->idSolicitud;
                                    } else {
                                        $urlShow = route('cotizacion-expres.show', 'solicitud-' . $item->idSolicitud);
                                    }
                                @endphp
                                <a href="{{ $urlShow }}"
                                   class="btn btn-info btn-xs" style="padding:4px 8px;" title="Ver detalle">
                                    <i class="fa fa-search"></i>
                                </a>
                                @if($item->idCliente)
                                @php
                                    $slugDoc = \Illuminate\Support\Str::slug($item->nombreEmpresa ?? 'cliente') . '-' . $item->idCliente;
                                    $totalDocs = $documentosPorCliente[$item->idCliente] ?? 0;
                                @endphp
                                @if($totalDocs > 0)
                                <a href="{{ route('cotizacion-expres.historial-documentos', $slugDoc) }}"
                                   class="btn btn-warning btn-xs" style="padding:4px 8px; margin-left:3px;" title="Ver {{ $totalDocs }} documento(s)">
                                    <i class="fa fa-folder-open"></i>
                                    <span class="badge" style="background:#fff; color:#f39c12; margin-left:2px;">{{ $totalDocs }}</span>
                                </a>
                                @else
                                <span class="btn btn-default btn-xs disabled" style="padding:4px 8px; margin-left:3px; opacity:0.5; cursor:not-allowed;" title="Sin documentos cargados">
                                    <i class="fa fa-folder"></i>
                                </span>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted" style="padding: 30px;">
                                <i class="fas fa-inbox fa-2x"></i><br>
                                No hay solicitudes registradas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            @if($solicitudes->hasPages())
            <div style="padding:15px; border-top: 1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:12px; color:#666;">
                    Mostrando {{ $solicitudes->firstItem() }}–{{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }} solicitudes
                </div>
                <div>
                    {{ $solicitudes->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<style>
    /* Ocultar botones de DataTables que no queremos */
    .dt-buttons,
    .buttons-colvis,
    .buttons-excel,
    button.dt-button {
        display: none !important;
    }

    /* Ocultar paginación por defecto de DataTables */
    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }

    /* Quitar borde azul al hacer click en botones */
    button:focus,
    input:focus,
    a:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>
<script>
$(document).ready(function () {
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }


    // Ocultar botones extra que aparezcan dinámicamente
    setTimeout(function() {
        $('button:contains("Columnas"), button:contains("Excel")').hide();
    }, 500);

    var mensajeDefault = function() {
        var fecha = new Date().toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });
        return '¡Hola! 👋 Te enviamos el reporte de cotizaciones Express de *PROSARC S.A. ESP* correspondiente al ' + fecha + '.\n\nPor favor encuéntras adjunto el archivo Excel con el detalle de las solicitudes.\n\n_Prosarc – Protección, Servicios Ambientales, Respel de Colombia S.A. ESP_';
    };

    // Modal enviar reporte — restaurar mensaje al abrir
    $('#btn-enviar-reporte').on('click', function() {
        $('#mensaje-whatsapp').val(mensajeDefault());
        $('#modal-enviar-reporte').modal('show');
    });

    $('#btn-agregar-numero').on('click', function() {
        var html = '<div class="input-group" style="margin-bottom:8px;">' +
            '<input type="text" class="form-control numero-input" placeholder="573001234567">' +
            '<span class="input-group-btn">' +
            '<button class="btn btn-danger btn-sm btn-eliminar-numero" type="button"><i class="fa fa-times"></i></button>' +
            '</span></div>';
        $('#lista-numeros').append(html);
    });

    $(document).on('click', '.btn-eliminar-numero', function() {
        $(this).closest('.input-group').remove();
    });

    $('#btn-confirmar-envio').on('click', function() {
        var numeros = [];
        $('.numero-input').each(function() {
            var v = $(this).val().trim().replace(/\D/g, '');
            if (v) numeros.push(v);
        });

        if (numeros.length === 0) {
            alert('Ingresa al menos un número.');
            return;
        }

        var mensaje = encodeURIComponent($('#mensaje-whatsapp').val().trim() || mensajeDefault());

        // Descargar el Excel primero
        window.location.href = '{{ route("cotizacion-expres.excel") }}';

        // Abrir WhatsApp Web para cada número con un pequeño delay
        setTimeout(function() {
            numeros.forEach(function(numero, i) {
                setTimeout(function() {
                    window.open('https://wa.me/' + numero + '?text=' + mensaje, '_blank');
                }, i * 800);
            });
        }, 1000);

        $('#modal-enviar-reporte').modal('hide');
    });
});
</script>

{{-- Modal enviar reporte --}}
<div class="modal fade" id="modal-enviar-reporte" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#25D366; padding:14px 18px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1; font-size:20px;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" style="color:#fff; font-weight:600; font-size:15px;">
                    <i class="fa fa-whatsapp"></i> Enviar Reporte por WhatsApp
                </h4>
            </div>
            <div class="modal-body" style="padding:18px;">

                {{-- Mensaje editable --}}
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:600; color:#166534; margin-bottom:4px; display:block;">
                        <i class="fa fa-pencil"></i> Mensaje (editable):
                    </label>
                    <textarea id="mensaje-whatsapp" rows="5" class="form-control" style="font-size:12px; line-height:1.5; resize:vertical; border-color:#bbf7d0; background:#f0fdf4; color:#166534;"></textarea>
                </div>

                <p style="font-size:13px; color:#555; margin-bottom:10px;">
                    Números destino (con código de país, sin +):
                </p>

                <div id="lista-numeros">
                    <div class="input-group" style="margin-bottom:8px;">
                        <input type="text" class="form-control numero-input" placeholder="573001234567">
                        <span class="input-group-btn">
                            <button class="btn btn-success btn-sm" type="button" id="btn-agregar-numero" title="Agregar número">
                                <i class="fa fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <p style="font-size:11px; color:#999; margin-top:10px; margin-bottom:0;">
                    <i class="fa fa-info-circle"></i>
                    Se descargará el Excel y se abrirá WhatsApp Web por cada número. Solo debes dar clic en <strong>Enviar</strong> en cada chat.
                </p>
            </div>
            <div class="modal-footer" style="background:#f9f9f9;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-envio" style="background:#25D366; border-color:#25D366;">
                    <i class="fa fa-whatsapp"></i> Descargar y Abrir WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
