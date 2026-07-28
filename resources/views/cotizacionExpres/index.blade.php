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
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Desde</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                   value="{{ request('fecha_desde') }}">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                   value="{{ request('fecha_hasta') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Nombre" value="{{ request('nombre') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Servicio</label>
                            <input type="text" name="servicio" class="form-control"
                                   placeholder="Ej: Quimicos, Biosanitarios..." value="{{ request('servicio') }}">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="Pagado" {{ request('estado') == 'Pagado' ? 'selected' : '' }}>Pagado</option>
                                <option value="En proceso" {{ request('estado') == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                                <option value="Rechazado" {{ request('estado') == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Cancelado" {{ request('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-sm-12 text-right" style="padding-top: 4px;">
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="fa fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('cotizacion-expres.index') }}" class="btn btn-default btn-sm">
                            Limpiar
                        </a>
                        <a href="{{ route('cotizacion-expres.excel', request()->except('page')) }}" class="btn btn-success btn-sm" id="btn-descargar-reporte">
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
            <button type="button" id="btnEliminarSeleccion" class="btn btn-danger btn-sm" style="padding:6px 14px; display:none;" onclick="eliminarSeleccionados()">
                <i class="fa fa-trash"></i> Eliminar seleccionados (<span id="countSeleccion">0</span>)
            </button>
        </div>
        <div class="box-body no-padding">
            <div>
                <table class="table table-hover" style="margin-bottom:0; font-size:13px; border-collapse:collapse;">
                    <thead style="background-color:#f4f4f4;">
                        <tr style="height:48px;">
                            <th style="width:40px; padding:12px; vertical-align:middle;"></th>
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
                            <th style="width:160px; padding:12px; vertical-align:middle; text-align:center;">Acciones</th>
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
                            <td style="padding:12px; vertical-align:middle; text-align:center;">
                                <input type="checkbox" class="check-solicitud" value="{{ $item->idSolicitud }}" style="cursor:pointer;">
                            </td>
                            <td style="padding:12px; vertical-align:middle; text-align:center; font-weight:600; color:#0066cc;" data-order="{{ $item->idSolicitud }}">
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
                            <td style="padding:12px 8px; text-align:center; vertical-align:middle; white-space:nowrap;">
                                @php
                                    if ($item->idCliente) {
                                        $slugShow = \Illuminate\Support\Str::slug($item->nombreEmpresa ?? $item->nit ?? 'cliente') . '-' . $item->idCliente;
                                        $urlShow = route('cotizacion-expres.show', $slugShow) . '?solicitud=' . $item->idSolicitud;
                                    } else {
                                        $urlShow = route('cotizacion-expres.show', 'solicitud-' . $item->idSolicitud);
                                    }
                                @endphp
                                <div style="display:inline-flex; align-items:center; gap:4px;">
                                <a href="{{ $urlShow }}"
                                   class="btn btn-info btn-xs" style="width:28px; height:28px; padding:0; display:inline-flex; align-items:center; justify-content:center;" title="Ver detalle">
                                    <i class="fa fa-search" style="font-size:12px;"></i>
                                </a>
                                @if($item->idCliente)
                                @php
                                    $slugDoc = \Illuminate\Support\Str::slug($item->nombreEmpresa ?? 'cliente') . '-' . $item->idCliente;
                                    $totalDocs = $documentosPorCliente[$item->idCliente] ?? 0;
                                @endphp
                                <a href="{{ route('cotizacion-expres.historial-documentos', $slugDoc) }}"
                                   class="btn btn-warning btn-xs" style="width:28px; height:28px; padding:0; display:inline-flex; align-items:center; justify-content:center; position:relative;" title="Ver {{ $totalDocs }} documento(s)">
                                    <i class="fa fa-folder-open" style="font-size:12px;"></i>
                                    @if($totalDocs > 0)
                                    <span style="position:absolute; top:-4px; right:-4px; background:#fff; color:#f39c12; border-radius:50%; width:14px; height:14px; font-size:9px; line-height:14px; text-align:center; font-weight:700;">{{ $totalDocs }}</span>
                                    @endif
                                </a>
                                @else
                                <span style="width:28px; height:28px; display:inline-flex;"></span>
                                @endif
                                <button type="button"
                                        class="btn btn-success btn-xs btn-whatsapp-individual"
                                        style="width:28px; height:28px; padding:0; display:inline-flex; align-items:center; justify-content:center;"
                                        title="Abrir WhatsApp"
                                        data-telefono="{{ $item->telefono ?? '' }}"
                                        data-empresa="{{ $item->nombreEmpresa ?? '' }}"
                                        data-solicitud="{{ $item->idSolicitud }}">
                                    <i class="fa fa-whatsapp" style="font-size:12px;"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-xs" style="width:28px; height:28px; padding:0; display:inline-flex; align-items:center; justify-content:center;" title="Eliminar" onclick="confirmarEliminarIndividual('{{ $item->idSolicitud }}')">
                                    <i class="fa fa-trash" style="font-size:12px;"></i>
                                </button>
                                </div>
                                </div>
                                <form id="formDelete-{{ $item->idSolicitud }}" action="{{ route('cotizacion-expres.destroy', $item->idSolicitud) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted" style="padding: 30px;">
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

    .check-solicitud {
        width: 16px;
        height: 16px;
        accent-color: #dc3545;
        cursor: pointer;
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


    // Ocultar botones extra que aparezcan dinamicamente
    setTimeout(function() {
        $('button:contains("Columnas"), button:contains("Excel")').hide();
    }, 500);

    var mensajeDefaultReporte = function() {
        var fecha = new Date().toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });
        return '¡Hola! 👋 Te enviamos el reporte de cotizaciones Express de *PROSARC S.A. ESP* correspondiente al ' + fecha + '.\n\nPor favor encuéntras adjunto el archivo Excel con el detalle de las solicitudes.\n\n_Prosarc – Protección, Servicios Ambientales, Respel de Colombia S.A. ESP_';
    };

    function normalizarNumero(numero) {
        var limpio = (numero || '').toString().replace(/\D/g, '');
        if (!limpio) return '57';
        if (limpio.indexOf('57') === 0) return limpio;
        if (limpio.length === 10) return '57' + limpio;
        return '57' + limpio;
    }

    function reiniciarListaNumerosMensaje() {
        var $lista = $('#lista-numeros-mensaje');
        $lista.find('.input-group').slice(1).remove();
        $lista.find('.numero-input-mensaje').first().val('57');
    }

    function abrirModalMensajeWhatsapp(numeroInicial) {
        reiniciarListaNumerosMensaje();
        $('#mensaje-whatsapp-manual').val('');
        if (numeroInicial) {
            $('#lista-numeros-mensaje .numero-input-mensaje').first().val(normalizarNumero(numeroInicial));
        }
        $('#modal-enviar-mensaje').modal('show');
    }

    // Modal reporte por WhatsApp (original)
    $('#btn-enviar-reporte').on('click', function() {
        $('#mensaje-whatsapp-reporte').val(mensajeDefaultReporte());
        $('#modal-enviar-reporte').modal('show');
    });

    $('#btn-agregar-numero-reporte').on('click', function() {
        var html = '<div class="input-group" style="margin-bottom:8px;">' +
            '<input type="text" class="form-control numero-input-reporte" placeholder="573001234567">' +
            '<span class="input-group-btn">' +
            '<button class="btn btn-danger btn-sm btn-eliminar-numero-reporte" type="button"><i class="fa fa-times"></i></button>' +
            '</span></div>';
        $('#lista-numeros-reporte').append(html);
    });

    $(document).on('click', '.btn-eliminar-numero-reporte', function() {
        $(this).closest('.input-group').remove();
    });

    $('#btn-confirmar-envio-reporte').on('click', function() {
        var numeros = [];
        $('.numero-input-reporte').each(function() {
            var v = $(this).val().trim().replace(/\D/g, '');
            if (v) numeros.push(v);
        });

        if (numeros.length === 0) {
            mostrarModalMensaje('Ingresa al menos un numero.', 'warning');
            return;
        }

        var $btn = $(this);
        var textoOriginal = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
        $('#modal-enviar-reporte').modal('hide');

        // Descargar el Excel
        var link = document.createElement('a');
        link.href = '{{ route("cotizacion-expres.excel") }}?telefono=' + numeros.join(',');
        link.target = '_blank';
        link.click();

        // Enviar via Wati (servidor)
        $.ajax({
            url: '{{ route("cotizacion-expres.enviar-reporte") }}',
            method: 'POST',
            data: {
                numeros: numeros,
                _token: '{{ csrf_token() }}'
            },
            success: function(resp) {
                if (resp.ok) {
                    mostrarModalMensaje('Reporte enviado a ' + numeros.length + ' numero(s)', 'success');
                }
            },
            error: function() {
                var mensaje = encodeURIComponent($('#mensaje-whatsapp-reporte').val().trim() || mensajeDefaultReporte());
                setTimeout(function() {
                    numeros.forEach(function(numero, i) {
                        setTimeout(function() {
                            window.open('https://wa.me/' + numero + '?text=' + mensaje, '_blank');
                        }, i * 800);
                    });
                }, 1500);
            },
            complete: function() {
                $btn.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // Modal nuevo: mensaje manual por WhatsApp (desde acciones)
    $('#btn-agregar-numero-mensaje').on('click', function() {
        var html = '<div class="input-group" style="margin-bottom:8px;">' +
            '<input type="text" class="form-control numero-input-mensaje" placeholder="573001234567" value="57">' +
            '<span class="input-group-btn">' +
            '<button class="btn btn-danger btn-sm btn-eliminar-numero-mensaje" type="button"><i class="fa fa-times"></i></button>' +
            '</span></div>';
        $('#lista-numeros-mensaje').append(html);
    });

    $(document).on('click', '.btn-eliminar-numero-mensaje', function() {
        $(this).closest('.input-group').remove();
    });

    $('#btn-confirmar-envio-mensaje').on('click', function() {
        var mensajeTexto = $('#mensaje-whatsapp-manual').val().trim();
        if (!mensajeTexto) {
            mostrarModalMensaje('Escribe el mensaje que deseas enviar.', 'warning');
            return;
        }

        var numeros = [];
        $('.numero-input-mensaje').each(function() {
            var v = normalizarNumero($(this).val());
            if (v && v.length > 2) numeros.push(v);
        });

        if (numeros.length === 0) {
            mostrarModalMensaje('Ingresa al menos un numero.', 'warning');
            return;
        }

        var $btn = $(this);
        var textoOriginal = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
        $('#modal-enviar-mensaje').modal('hide');

        var mensaje = encodeURIComponent(mensajeTexto);
        numeros.forEach(function(numero, i) {
            setTimeout(function() {
                window.open('https://wa.me/' + numero + '?text=' + mensaje, '_blank');
            }, i * 500);
        });

        setTimeout(function() {
            mostrarModalMensaje('Se abrio WhatsApp para ' + numeros.length + ' numero(s).', 'success');
            $btn.prop('disabled', false).html(textoOriginal);
        }, Math.max(700, numeros.length * 550));
    });

    // --- Seleccion multiple y eliminacion masiva ---
    function actualizarBotonEliminar() {
        var count = $('.check-solicitud:checked').length;
        var $btn = $('#btnEliminarSeleccion');
        if (count > 0) {
            $btn.show().find('#countSeleccion').text(count);
        } else {
            $btn.hide();
        }
    }

    $(document).on('change', '.check-solicitud', function() {
        actualizarBotonEliminar();
    });

    $(document).on('click', '.btn-whatsapp-individual', function() {
        var telefono = ($(this).data('telefono') || '').toString();
        abrirModalMensajeWhatsapp(telefono || '57');
    });
});

function eliminarSeleccionados() {
    var ids = $('.check-solicitud:checked').map(function() { return this.value; }).get();
    if (ids.length === 0) return;
    mostrarModalConfirmacion('Eliminar ' + ids.length + ' solicitud(es)?', function() {
        var $btn = $('#btnEliminarSeleccion');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Eliminando...');

        $.ajax({
            url: '{{ route("cotizacion-expres.eliminar-lote") }}',
            method: 'POST',
            data: { ids: ids, _token: '{{ csrf_token() }}' },
            success: function() { location.reload(); },
            error: function() {
                mostrarModalMensaje('Error al eliminar', 'danger');
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Eliminar seleccionados');
            }
        });
    });
}

function confirmarEliminarIndividual(id) {
    mostrarModalConfirmacion('Eliminar solicitud #' + id + '?', function() {
        document.getElementById('formDelete-' + id).submit();
    });
}

function mostrarModalMensaje(mensaje, tipo) {
    var icono = tipo === 'success' ? 'fa-check-circle' : (tipo === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle');
    var color = tipo === 'success' ? '#00a65a' : (tipo === 'warning' ? '#f39c12' : '#dd4b39');
    $('#modalMensaje .modal-icono').attr('class', 'modal-icono fas ' + icono).css('color', color);
    $('#modalMensaje .modal-texto').text(mensaje);
    $('#modalMensaje').modal('show');
}

function mostrarModalConfirmacion(mensaje, callback) {
    $('#modalConfirmar .modal-texto').text(mensaje);
    $('#modalConfirmar .btn-confirmar').off('click').on('click', function() {
        $('#modalConfirmar').modal('hide');
        callback();
    });
    $('#modalConfirmar').modal('show');
}
</script>

{{-- Modal enviar reporte --}}
<div class="modal fade" id="modal-enviar-reporte" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#25D366; padding:14px 18px;">
                <button type="button" class="close" onclick="$('#modal-enviar-reporte').modal('hide')" style="color:#fff; opacity:1; font-size:20px;">
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
                    <textarea id="mensaje-whatsapp-reporte" rows="5" class="form-control" style="font-size:12px; line-height:1.5; resize:vertical; border-color:#bbf7d0; background:#f0fdf4; color:#166534;"></textarea>
                </div>

                <p style="font-size:13px; color:#555; margin-bottom:10px;">
                    Números destino (con código de país, sin +):
                </p>

                <div id="lista-numeros-reporte">
                    <div class="input-group" style="margin-bottom:8px;">
                        <input type="text" class="form-control numero-input-reporte" placeholder="573001234567">
                        <span class="input-group-btn">
                            <button class="btn btn-success btn-sm" type="button" id="btn-agregar-numero-reporte" title="Agregar número">
                                <i class="fa fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <p style="font-size:11px; color:#999; margin-top:10px; margin-bottom:0;">
                    <i class="fa fa-info-circle"></i>
                    Se descargara el Excel y se enviara automaticamente via Wati a cada numero.
                </p>
            </div>
            <div class="modal-footer" style="background:#f9f9f9;">
                <button type="button" class="btn btn-default" onclick="$('#modal-enviar-reporte').modal('hide')">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-envio-reporte" style="background:#25D366; border-color:#25D366;">
                    <i class="fa fa-whatsapp"></i> Descargar y Abrir WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal mensaje manual --}}
<div class="modal fade" id="modal-enviar-mensaje" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#25D366; padding:14px 18px;">
                <button type="button" class="close" onclick="$('#modal-enviar-mensaje').modal('hide')" style="color:#fff; opacity:1; font-size:20px;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" style="color:#fff; font-weight:600; font-size:15px;">
                    <i class="fa fa-whatsapp"></i> Enviar Mensaje por WhatsApp
                </h4>
            </div>
            <div class="modal-body" style="padding:18px;">
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:600; color:#166534; margin-bottom:4px; display:block;">
                        <i class="fa fa-pencil"></i> Mensaje:
                    </label>
                    <p style="font-size:11px; color:#666; margin:0 0 6px 0;">Escribe el mensaje que deseas enviar.</p>
                    <textarea id="mensaje-whatsapp-manual" rows="5" class="form-control" style="font-size:12px; line-height:1.5; resize:vertical; border-color:#bbf7d0; background:#f0fdf4; color:#166534;"></textarea>
                </div>

                <p style="font-size:13px; color:#555; margin-bottom:10px;">
                    Números destino (con código de país, sin +):
                </p>

                <div id="lista-numeros-mensaje">
                    <div class="input-group" style="margin-bottom:8px;">
                        <input type="text" class="form-control numero-input-mensaje" placeholder="573001234567" value="57">
                        <span class="input-group-btn">
                            <button class="btn btn-success btn-sm" type="button" id="btn-agregar-numero-mensaje" title="Agregar número">
                                <i class="fa fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#f9f9f9;">
                <button type="button" class="btn btn-default" onclick="$('#modal-enviar-mensaje').modal('hide')">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-envio-mensaje" style="background:#25D366; border-color:#25D366;">
                    <i class="fa fa-whatsapp"></i> Enviar y Abrir WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal mensaje --}}
<div class="modal fade" id="modalMensaje" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:6px; border:none;">
            <div class="modal-body text-center" style="padding:25px;">
                <i class="modal-icono fas fa-check-circle" style="font-size:40px; margin-bottom:12px; display:block;"></i>
                <p class="modal-texto" style="font-size:14px; color:#333; margin:0;"></p>
                <button type="button" class="btn btn-default btn-sm" onclick="$('#modalMensaje').modal('hide')" style="margin-top:15px;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal confirmacion --}}
<div class="modal fade" id="modalConfirmar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:6px; border:none;">
            <div class="modal-body text-center" style="padding:25px;">
                <i class="fas fa-exclamation-triangle" style="font-size:40px; color:#f39c12; margin-bottom:12px; display:block;"></i>
                <p class="modal-texto" style="font-size:14px; color:#333; margin:0 0 18px 0;"></p>
                <button type="button" class="btn btn-default btn-sm" onclick="$('#modalConfirmar').modal('hide')">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm btn-confirmar">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection
