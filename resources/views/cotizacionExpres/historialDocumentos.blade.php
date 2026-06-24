@extends('layouts.cotizaciones')

@section('htmlheader_title')
    Cotizaciones de servicio express
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 160, 100), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden; color:#fff; display:block; padding-left:15px;">
    Cotizaciones de servicio express
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid">

    {{-- FILTRO DE BÚSQUEDA --}}
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fas fa-filter" style="color:#666;"></i> Filtro de búsqueda</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('cotizacion-expres.historial-documentos', $slug) }}">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Fecha</label>
                            <div class="input-group">
                                <input type="date" name="fecha" id="fecha"
                                       class="form-control"
                                       value="{{ request('fecha') ? \Carbon\Carbon::createFromFormat('d/m/Y', request('fecha'))->format('Y-m-d') : '' }}"
                                       style="cursor: pointer;">
                                <span class="input-group-addon" style="cursor: pointer; pointer-events:none;"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group" style="padding-top:25px;">
                            <a href="{{ route('cotizacion-expres.historial-documentos', $slug) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TARJETAS POR MES --}}
    <div style="margin-top:15px; display:flex; gap:15px; flex-wrap:wrap;">
        @php
            $meses = [];
            for ($i = 2; $i >= 0; $i--) {
                $fecha = now()->subMonths($i);
                $meses[] = [
                    'mes_es' => ucfirst($fecha->locale('es')->translatedFormat('F')),
                    'mes_numero' => $fecha->month,
                    'año' => $fecha->year,
                ];
            }
        @endphp

        @foreach($meses as $mes)
        <div style="flex:1; min-width:250px;">
            <div style="background:#27ae60; border-radius:6px; padding:15px; display:flex; align-items:flex-start; gap:12px; color:#fff; cursor:pointer; transition:all 0.3s; box-shadow:0 2px 4px rgba(0,0,0,0.1);"
                 data-toggle="modal" data-target="#modalDocumentosMes" onclick="abrirMes('{{ $mes['mes_es'] }}', {{ $mes['mes_numero'] }}, {{ $mes['año'] }})">
                <div style="font-size:32px; min-width:45px; padding-top:2px;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:14px; margin-bottom:2px;">{{ $mes['mes_es'] }}</div>
                    <div style="font-size:12px; opacity:0.95; margin-bottom:4px; word-break:break-word;">{{ $razonSocial ?? 'BranCorp' }}</div>
                    <div style="font-size:11px; opacity:0.8; text-decoration:underline;">Ver más</div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- RECUADRO CARGAR DOCUMENTO --}}
        <div style="flex:1; min-width:250px;">
            <div style="background:#0066cc; border-radius:6px; padding:15px; display:flex; align-items:flex-start; gap:12px; color:#fff; cursor:pointer; transition:all 0.3s; box-shadow:0 2px 4px rgba(0,0,0,0.1); hover: background:#005aa8;"
                 data-toggle="modal" data-target="#modalCargarDocumento">
                <div style="font-size:32px; min-width:45px; padding-top:2px;">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:14px; margin-bottom:2px;">Cargar Documento</div>
                    <div style="font-size:12px; opacity:0.95; margin-bottom:4px; word-break:break-word;">Sube un nuevo documento</div>
                    <div style="font-size:11px; opacity:0.8; text-decoration:underline;">Hacer clic aquí</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE DOCUMENTOS --}}
    <div class="box box-info" style="border-top:3px solid #0066cc; margin-top:20px;">
        <div style="padding:15px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="margin:0; font-size:16px; font-weight:600; color:#000;">
                <i class="fas fa-file" style="color:#333; margin-right:8px;"></i> Documentos
            </h3>
        </div>
        <div class="box-body no-padding">
            @if($documentos->isNotEmpty())
            <div>
                <table class="table table-hover" style="margin-bottom:0; font-size:13px; border-collapse:collapse;">
                    <thead style="background-color:#f4f4f4;">
                        <tr style="height:48px;">
                            <th style="width:40px; padding:12px; vertical-align:middle; text-align:center;">No</th>
                            <th style="padding:12px; vertical-align:middle;">Nombre del documento</th>
                            <th style="padding:12px; vertical-align:middle;">Fecha</th>
                            <th style="width:60px; padding:12px; vertical-align:middle; text-align:center;">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentos as $index => $doc)
                        <tr style="border-bottom: 1px solid #f0f0f0; height:48px;">
                            <td style="padding:12px; vertical-align:middle; text-align:center;">{{ $index + 1 }}</td>
                            <td style="padding:12px; vertical-align:middle;">
                                {{ $doc->nombre }}
                                <span style="color:#aaa; font-size:11px; margin-left:6px;">{{ $doc->tamaño }}</span>
                            </td>
                            <td style="padding:12px; vertical-align:middle;">{{ $doc->fechaCarga->format('d/m/Y H:i') }}</td>
                            <td style="padding:12px; text-align:center; vertical-align:middle;">
                                <button class="btn btn-info btn-xs" style="padding:4px 8px; border:none; cursor:pointer;"
                                    data-toggle="modal" data-target="#modalDocumentoDetalle"
                                    onclick="abrirDocumento('{{ $doc->nombre }}', '{{ $razonSocial }}', '{{ $doc->url }}')">
                                    <i class="fa fa-search"></i>
                                </button>
                                <a href="{{ $doc->url }}" target="_blank" class="btn btn-default btn-xs" style="padding:4px 8px; margin-left:3px;" title="Descargar">
                                    <i class="fa fa-download"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted" style="padding: 30px;">
                <i class="fas fa-inbox fa-2x"></i><br>
                No hay documentos cargados para este cliente.
            </div>
            @endif
        </div>
    </div>

    {{-- BOTÓN VOLVER --}}
    <div style="margin-top:15px; margin-bottom:20px;">
        <a href="{{ route('cotizacion-expres.show', $slug) }}" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
    </div>

</div>

{{-- ============ MODAL 1: Documentos del Mes ============ --}}
<div class="modal fade" id="modalDocumentosMes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width:680px;">
        <div class="modal-content" style="border-radius:6px; overflow:hidden; border:none;">

            <div style="background-image:linear-gradient(40deg,rgb(255,160,100),rgb(252,98,98)); padding:12px 16px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between;">
                <div style="background-color:#ecf0f5; position:absolute; height:200%; width:35vw; transform:rotate(30deg); right:-10vw; top:-50%;"></div>
                <span style="color:#fff; font-weight:600; font-size:14px; position:relative; z-index:1;" id="tituloMes">Documentos - Mes</span>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; position:relative; z-index:1; line-height:1;">&times;</button>
            </div>

            <div class="modal-body" style="padding:0; background:#f5f5f5;" id="modalDocsMesBody">
                <div style="text-align:center; padding:20px; color:#999;">
                    <i class="fas fa-inbox fa-2x" style="margin-bottom:10px; display:block;"></i>
                    No hay documentos en este mes.
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ============ MODAL 2: Documento Individual ============ --}}
<div class="modal fade" id="modalDocumentoDetalle" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width:800px;">
        <div class="modal-content" style="border-radius:6px; overflow:hidden; border:none;">

            <div style="background-image:linear-gradient(40deg,rgb(255,160,100),rgb(252,98,98)); padding:12px 16px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between;">
                <div style="background-color:#ecf0f5; position:absolute; height:200%; width:35vw; transform:rotate(30deg); right:-10vw; top:-50%;"></div>
                <span style="color:#fff; font-weight:600; font-size:14px; position:relative; z-index:1;" id="nombreDocumento">Documento</span>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; position:relative; z-index:1; line-height:1;">&times;</button>
            </div>

            <div class="modal-body" style="padding:20px; background:#fff;">
                <div style="margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:15px;">
                    <h4 style="margin:0 0 3px 0; font-weight:700; color:#333;" id="docNombre">Nombre del documento</h4>
                    <p style="margin:0; font-size:13px; color:#666;" id="docEmpresa">Soluciones Integrales Andinas S.A.S.</p>
                </div>

                <div style="display:flex; justify-content:center; margin:20px 0;">
                    <iframe id="pdfViewer" style="width:100%; height:500px; border:2px solid #ddd; border-radius:4px;" frameborder="0"></iframe>
                    <img id="imgViewer" alt="Vista previa" style="display:none; max-width:100%; max-height:500px; border:2px solid #ddd; border-radius:4px;">
                </div>

                <div style="text-align:center; margin-top:15px;">
                    <a id="btnDescargar" href="#" class="btn btn-primary btn-sm" style="padding:6px 20px;" download>
                        <i class="fa fa-download"></i> Descargar
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ============ MODAL 3: Cargar Documento ============ --}}
<div class="modal fade" id="modalCargarDocumento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width:600px;">
        <div class="modal-content" style="border-radius:6px; overflow:hidden; border:none;">

            <div style="background-image:linear-gradient(40deg,rgb(39,174,96),rgb(46,204,113)); padding:12px 16px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between;">
                <div style="background-color:#ecf0f5; position:absolute; height:200%; width:35vw; transform:rotate(30deg); right:-10vw; top:-50%;"></div>
                <span style="color:#fff; font-weight:600; font-size:14px; position:relative; z-index:1;">
                    <i class="fa fa-upload" style="margin-right:8px;"></i> Cargar Documento
                </span>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; position:relative; z-index:1; line-height:1;">&times;</button>
            </div>

            <form id="formCargarDocumento" enctype="multipart/form-data" style="padding:20px;">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Nombre del Documento *</label>
                    <input type="text" id="nombreDoc" name="nombreDoc" class="form-control"
                           placeholder="Ej: Factura Mayo 2026" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Tipo de Documento</label>
                    <select id="tipoDoc" name="tipoDoc" class="form-control"
                            style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Selecciona un tipo</option>
                        <option value="Factura">Factura</option>
                        <option value="Recibo">Recibo</option>
                        <option value="Cotización">Cotización</option>
                        <option value="Contrato">Contrato</option>
                        <option value="Certificado">Certificado</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Archivo PDF *</label>
                    <div style="border:2px dashed #27ae60; border-radius:6px; padding:20px; text-align:center; background:#f9fff7; cursor:pointer;" id="dropZone">
                        <div style="font-size:32px; color:#27ae60; margin-bottom:10px;">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p style="margin:0; color:#27ae60; font-weight:600;">Arrastra el archivo aquí</p>
                        <p style="margin:5px 0 0 0; color:#666; font-size:12px;">o haz clic para seleccionar</p>
                        <input type="file" id="archivoDoc" name="archivoDoc" accept=".pdf"
                               style="display:none;">
                    </div>
                    <small style="color:#999; display:block; margin-top:8px;">
                        <i class="fa fa-info-circle"></i> Solo archivos PDF. Máximo 10MB
                    </small>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="padding:8px 20px;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" style="padding:8px 20px;">
                        <i class="fa fa-upload"></i> Cargar Documento
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    .form-control {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 13px;
        height: 38px;
    }
    .form-control:focus {
        border-color: #3c8dbc;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(60,141,188,.6);
    }
    .form-group {
        margin-bottom: 0;
    }
    /* Ocultar botones de DataTables */
    .dt-buttons {
        display: none !important;
    }
</style>
<script>
var documentosData = @json($documentos);
var razonSocial = '{{ $razonSocial }}';

$(document).ready(function () {
    // Convertir fecha al enviar si viene en formato d/m/Y
    $('#fecha').on('change', function() {
        var fechaValue = $(this).val(); // Formato: YYYY-MM-DD
        if (fechaValue) {
            // Convertir a d/m/Y para enviar al servidor
            var parts = fechaValue.split('-');
            var fechaFormato = parts[2] + '/' + parts[1] + '/' + parts[0];
            $(this).val(fechaFormato);
            // Enviar formulario automáticamente
            $(this).closest('form').submit();
        }
    });
});

function abrirMes(mes, numero, año) {
    document.getElementById('tituloMes').textContent = 'Documentos - ' + mes;

    // Filtrar documentos por mes y año
    var mesFormato = String(numero).padStart(2, '0');
    var docsDelMes = documentosData.filter(function(doc) {
        var fechaDoc = new Date(doc.fechaCarga);
        var mesDoc = String(fechaDoc.getMonth() + 1).padStart(2, '0');
        var añoDoc = fechaDoc.getFullYear();
        return mesDoc == mesFormato && añoDoc == año;
    });

    // Generar HTML
    var html = '';
    if (docsDelMes.length === 0) {
        html = '<div style="text-align:center; padding:20px; color:#999;"><i class="fas fa-inbox fa-2x" style="margin-bottom:10px; display:block;"></i>No hay documentos en este mes.</div>';
    } else {
        docsDelMes.forEach(function(doc) {
            var fecha = new Date(doc.fechaCarga);
            var fechaFormato = fecha.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: '2-digit' });
            var horaFormato = fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
            var rutaCompleta = doc.url;

            html += '<div style="display:flex; align-items:stretch; background:#fff; margin-bottom:1px; cursor:pointer;" data-toggle="modal" data-target="#modalDocumentoDetalle" onclick="abrirDocumento(\'' + doc.nombre + '\', \'' + razonSocial + '\', \'' + rutaCompleta + '\')">' +
                '<div style="flex:1; padding:14px 16px; border-left:4px solid #27ae60;">' +
                '<div style="font-weight:700; font-size:13px; margin-bottom:3px;">' + doc.nombre + '</div>' +
                '<div style="font-size:12px; color:#555; margin-bottom:2px;">' + razonSocial + '</div>' +
                '<div style="font-size:12px; color:#777;">' + fechaFormato + '</div>' +
                '<div style="font-size:12px; color:#777;">' + horaFormato + '</div>' +
                '<div style="margin-top:8px; padding-top:6px; border-top:1px solid #eee;"><span style="font-size:12px; color:#0066cc; text-decoration:none; cursor:pointer;">Ver <i class="fa fa-search" style="font-size:11px;"></i></span></div>' +
                '</div>' +
                '</div>';
        });
    }

    document.getElementById('modalDocsMesBody').innerHTML = html;
}

function abrirDocumento(nombre, empresa, rutaDocumento) {
    document.getElementById('nombreDocumento').textContent = nombre;
    document.getElementById('docNombre').textContent = nombre;
    document.getElementById('docEmpresa').textContent = empresa;
    document.getElementById('btnDescargar').href = rutaDocumento;

    var ext = (nombre.split('.').pop() || '').toLowerCase();
    var esImagen = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].indexOf(ext) >= 0;
    var iframe = document.getElementById('pdfViewer');
    var img = document.getElementById('imgViewer');

    if (esImagen) {
        iframe.style.display = 'none';
        iframe.removeAttribute('src');
        img.style.display = 'block';
        img.src = rutaDocumento;
    } else {
        img.style.display = 'none';
        img.removeAttribute('src');
        iframe.style.display = 'block';
        iframe.src = rutaDocumento;
    }
}

// Drag and Drop para cargar documentos
var archivoSeleccionado = null;

$(document).ready(function() {
    var dropZone = document.getElementById('dropZone');
    var inputArchivo = document.getElementById('archivoDoc');

    if (dropZone && inputArchivo) {
        dropZone.addEventListener('click', function() {
            inputArchivo.click();
        });

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.background = '#e8f8f5';
            dropZone.style.borderColor = '#16a085';
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.background = '#f9fff7';
            dropZone.style.borderColor = '#27ae60';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.background = '#f9fff7';
            dropZone.style.borderColor = '#27ae60';

            var files = e.dataTransfer.files;
            if (files.length > 0) {
                archivoSeleccionado = files[0];
                mostrarNombreArchivo(files[0].name);
            }
        });

        inputArchivo.addEventListener('change', function() {
            if (this.files.length > 0) {
                archivoSeleccionado = this.files[0];
                mostrarNombreArchivo(this.files[0].name);
            }
        });
    }

    function mostrarNombreArchivo(nombreArchivo) {
        var dropZone = document.getElementById('dropZone');
        if (dropZone) {
            dropZone.innerHTML = '<div style="font-size:32px; color:#27ae60; margin-bottom:10px;"><i class="fas fa-file-pdf"></i></div>' +
                                '<p style="margin:0; color:#27ae60; font-weight:600;">' + nombreArchivo + '</p>' +
                                '<p style="margin:5px 0 0 0; color:#666; font-size:12px;">Listo para cargar</p>';
        }
    }

    // Enviar formulario
    var formCargar = document.getElementById('formCargarDocumento');
    if (formCargar) {
        formCargar.addEventListener('submit', function(e) {
            e.preventDefault();

            var nombreDoc = document.getElementById('nombreDoc').value;
            var tipoDoc = document.getElementById('tipoDoc').value;
            var archivoDoc = archivoSeleccionado;

            if (!nombreDoc) {
                alert('Por favor ingresa el nombre del documento');
                return;
            }

            if (!archivoDoc) {
                alert('Por favor selecciona un archivo PDF');
                return;
            }

            if (!archivoDoc.name.endsWith('.pdf')) {
                alert('Solo se permiten archivos PDF');
                return;
            }

            if (archivoDoc.size > 10 * 1024 * 1024) {
                alert('El archivo es muy grande (máximo 10MB)');
                return;
            }

            // Enviar al servidor
            var formData = new FormData();
            formData.append('nombreDoc', nombreDoc);
            formData.append('tipoDoc', tipoDoc);
            formData.append('archivoDoc', archivoDoc);

            var slug = '{{ $slug }}';
            var url = '/cotizacion-expres/' + slug + '/cargar-documento';

            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var headers = {};
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            }

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: headers
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Limpiar formulario y cerrar modal
                    archivoSeleccionado = null;
                    document.getElementById('formCargarDocumento').reset();
                    $('#modalCargarDocumento').modal('hide');
                    mostrarNotificacion('¡Documento cargado exitosamente!');

                    // Recargar la página después de 2 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el documento: ' + error.message);
            });
        });
    }

    function mostrarNotificacion(mensaje) {
        var notif = document.createElement('div');
        notif.style.cssText = 'position:fixed; top:20px; right:20px; background:#27ae60; color:#fff; padding:15px 20px; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,0.2); z-index:9999; font-weight:600;';
        notif.textContent = mensaje;
        document.body.appendChild(notif);

        setTimeout(function() {
            notif.remove();
        }, 3000);
    }
});
</script>
@endsection
