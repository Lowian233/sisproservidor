@extends('layouts.cotizaciones')

@section('htmlheader_title')
    Cotizaciones de servicio express
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 160, 100), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
    Cotizaciones de servicio express
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('styles')
<style>
    /* --- Estados View / Edit --- */
    .val-text  { display:block; padding: 7px 0; }
    .val-input { display:none; }

    .editing .val-text  { display:none; }
    .editing .val-input { display:block; }

    /* Inputs del modo edición: recuadro gris y texto azul */
    .val-input input {
        width:100%;
        border:1px solid #ccc;
        border-radius:4px;
        padding:8px 12px;
        font-size:13px;
        color: #0066cc; /* Texto azul */
        background-color: #f4f4f4; /* Recuadro gris */
        outline:none;
        height: 38px;
    }
    .val-input input:focus { 
        border-color:#3c8dbc; 
        background-color: #fff;
    }

    /* Layout rows */
    .data-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .data-label {
        width: 180px;
        min-width: 180px;
        font-weight: 700;
        color: #333;
        margin: 0;
        font-size: 13px;
        padding-top: 7px; /* Alinear con el texto */
    }
    .data-content {
        flex: 1;
        display: flex;
        gap: 10px;
    }

    /* Botones guardar/cancelar — ocultos por defecto */
    .btns-edit  { display:none !important; }
    .editing .btns-edit  { display:flex !important; justify-content:flex-end; gap:8px; margin-top:20px; }

    /* Botones editar/eliminar — visibles por defecto */
    .btns-view  { display:block; }
    .editing .btns-view  { display:none !important; }
</style>
@endsection

@section('main-content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="padding:6px 10px; font-size:12px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <form id="formEditar"
          action="{{ route('cotizacion-expres.update', $cotizacionExpres->slug) }}"
          method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="idSolicitud" value="{{ $cotizacionExpres->idSolicitud }}">

        <div class="row" id="cardWrapper">

            {{-- ===== COLUMNA IZQUIERDA ===== --}}
            <div class="col-md-3">
                <div class="box box-info" style="border-top:3px solid #00c0ef; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div class="box-body" style="padding:20px;">

                        {{-- Botones editar / eliminar (modo vista) --}}
                        @if(empty($cotizacionExpres->sin_cliente))
                        <div class="btns-view" style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 20px;">
                            <button type="button" class="btn btn-warning btn-sm" onclick="activarEdicion()" style="padding: 8px 12px; background-color: #f39c12; border-color: #e08e0b; border-radius: 4px;">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" style="padding: 8px 12px; border-radius: 4px;"
                                    onclick="if(confirm('¿Eliminar esta solicitud?')) document.getElementById('formEliminar').submit()">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        @else
                        <div class="alert alert-info" style="font-size:12px; padding:8px 10px; margin-bottom:15px;">
                            Solicitud sin cliente vinculado. Los datos de contacto se completarán cuando Wati asigne el cliente.
                        </div>
                        @endif

                        {{-- Nombre empresa --}}
                        <h4 style="font-weight:700; margin-bottom:25px; font-size:15px; text-align:center; color: #333;">
                            {{ $cotizacionExpres->razon_social ?? $cotizacionExpres->nombre_solicitante }}
                        </h4>

                        {{-- Campos izquierda --}}
                        <div style="font-size:13px;">
                            <div style="margin-bottom:18px;">
                                <label style="font-weight:700; color:#333; display:block; margin-bottom:2px;">Razón social</label>
                                <span class="val-text" style="color: {{ $cotizacionExpres->razon_social ? '#333' : '#999' }};">{{ $cotizacionExpres->razon_social ?? 'No se ha establecido' }}</span>
                                <div class="val-input">
                                    <input type="text" name="razon_social"
                                           value="{{ $cotizacionExpres->razon_social ?? '' }}"
                                           placeholder="Razón social">
                                </div>
                            </div>

                            <div style="margin-bottom:18px;">
                                <label style="font-weight:700; color:#333; display:block; margin-bottom:2px;">Nombre</label>
                                <span class="val-text" style="color: {{ $cotizacionExpres->nombre_solicitante ? '#333' : '#999' }};">{{ $cotizacionExpres->nombre_solicitante ?? 'No se ha establecido' }}</span>
                                <div class="val-input">
                                    <input type="text" name="nombre_solicitante"
                                           value="{{ $cotizacionExpres->nombre_solicitante }}"
                                           placeholder="Nombre">
                                </div>
                            </div>

                            <div style="margin-bottom:18px;">
                                <label style="font-weight:700; color:#333; display:block; margin-bottom:2px;">Nit</label>
                                <span class="val-text" style="color: {{ $cotizacionExpres->nit ? '#333' : '#999' }};">{{ $cotizacionExpres->nit ?? 'No se ha establecido' }}</span>
                                <div class="val-input">
                                    <input type="text" name="nit"
                                           value="{{ $cotizacionExpres->nit ?? '' }}"
                                           placeholder="Nit">
                                </div>
                            </div>
                        </div>

                        {{-- Botones guardar / cancelar (modo edición) --}}
                        <div class="btns-edit">
                            <button type="button" class="btn btn-default btn-sm" onclick="cancelarEdicion()" style="flex:1; padding:8px; background: #f4f4f4; border: 1px solid #ddd;">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success btn-sm" style="flex:1; padding:8px; background-color: #00a65a; border-color: #008d4c;">
                                Guardar
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ===== COLUMNA DERECHA ===== --}}
            <div class="col-md-9">
                <div class="box box-info" style="border-top:3px solid #00c0ef; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="padding:20px; border-bottom:1px solid #f0f0f0;">
                        <h3 style="margin:0; font-size:16px; font-weight:600; color:#333;">
                            Datos completos
                        </h3>
                    </div>

                    <div class="box-body" style="padding:0;">

                        <div style="padding:25px 20px; border-bottom:1px solid #f0f0f0; display:flex; justify-content:flex-end;">
                            <div class="btns-view" style="margin-bottom:0; display:flex; gap:8px;">
                                <a href="{{ route('cotizacion-expres.historial-documentos', $cotizacionExpres->slug) }}"
                                   class="btn btn-success btn-sm"
                                   style="background-color: #00a65a; border-color: #008d4c; padding: 6px 15px;">
                                    <i class="fa fa-folder-open"></i> Ver documentos
                                </a>
                                <a href="{{ route('cotizacion-expres.documentos', $cotizacionExpres->slug) }}"
                                   class="btn btn-sm" style="background-color:#34495e; color:#fff; padding: 6px 15px;">
                                    <i class="fa fa-upload"></i> Cargar documento
                                </a>
                            </div>
                        </div>

                        {{-- Tabla de datos --}}
                        <table style="width: 100%; border-collapse: collapse; margin: 0;">
                            <tbody>
                                {{-- Encargado --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Encargado
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->encargado ? '#333' : '#999' }};">{{ $cotizacionExpres->encargado ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="encargado"
                                                   value="{{ $cotizacionExpres->encargado ?? '' }}" placeholder="Nombre del encargado"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Representante legal --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Representante legal
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->representante_legal ? '#333' : '#999' }};">{{ $cotizacionExpres->representante_legal ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="representante_legal"
                                                   value="{{ $cotizacionExpres->representante_legal ?? '' }}"
                                                   placeholder="Representante legal"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Correo electrónico --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Correo electrónico
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->correo ? '#333' : '#999' }};">{{ $cotizacionExpres->correo ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="correo"
                                                   value="{{ $cotizacionExpres->correo ?? '' }}"
                                                   placeholder="Correo electrónico"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Documento de identidad --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Documento de identidad
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->documento_identidad ? '#333' : '#999' }};">{{ $cotizacionExpres->documento_identidad ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="documento_identidad"
                                                   value="{{ $cotizacionExpres->documento_identidad ?? '' }}"
                                                   placeholder="Documento de identidad"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Dirección --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Dirección
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->direccion ? '#333' : '#999' }};">{{ $cotizacionExpres->direccion ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="direccion"
                                                   value="{{ $cotizacionExpres->direccion ?? '' }}"
                                                   placeholder="Dirección"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Teléfono --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Teléfono
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->telefono ? '#333' : '#999' }};">{{ $cotizacionExpres->telefono ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="telefono"
                                                   value="{{ $cotizacionExpres->telefono ?? '' }}"
                                                   placeholder="Teléfono"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Número de Contacto --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Número de contacto
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->numero_contacto ? '#333' : '#999' }};">{{ $cotizacionExpres->numero_contacto ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="numero_contacto"
                                                   value="{{ $cotizacionExpres->numero_contacto ?? '' }}"
                                                   placeholder="Número de contacto"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Ciudad --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Ciudad
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->ciudad ? '#333' : '#999' }};">{{ $cotizacionExpres->ciudad ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="ciudad"
                                                   value="{{ $cotizacionExpres->ciudad ?? '' }}"
                                                   placeholder="Ciudad/Municipio"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Localidad --}}
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Localidad del servicio
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->localidad ? '#333' : '#999' }};">{{ $cotizacionExpres->localidad ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="localidad"
                                                   value="{{ $cotizacionExpres->localidad ?? '' }}"
                                                   placeholder="Localidad"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>

                                {{-- Lugar de expedición --}}
                                <tr>
                                    <td style="width: 40%; padding: 15px 20px; font-weight: 700; color: #333; font-size: 13px;">
                                        Lugar de expedición
                                    </td>
                                    <td style="width: 60%; padding: 15px 20px; color: #333; font-size: 13px;">
                                        <span class="val-text" style="color: {{ $cotizacionExpres->lugar_expedicion ? '#333' : '#999' }};">{{ $cotizacionExpres->lugar_expedicion ?? 'No se ha establecido' }}</span>
                                        <div class="val-input">
                                            <input type="text" name="lugar_expedicion"
                                                   value="{{ $cotizacionExpres->lugar_expedicion ?? '' }}"
                                                   placeholder="Lugar de expedición"
                                                   style="width: 100%;">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Botones guardar / cancelar derecha (modo edición) --}}
                        <div class="btns-edit" style="padding: 15px 20px; border-top: 1px solid #f0f0f0; gap: 8px; justify-content: flex-end;">
                            <button type="button" class="btn btn-default btn-sm" onclick="cancelarEdicion()" style="padding: 8px 20px; background: #f4f4f4; border: 1px solid #ddd;">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success btn-sm" style="padding: 8px 20px; background-color: #00a65a; border-color: #008d4c;">
                                Guardar Cambios
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>{{-- /.row --}}
    </form>

    {{-- Form separado para eliminar --}}
    <form id="formEliminar"
          action="{{ route('cotizacion-expres.destroy', $cotizacionExpres->slug) }}"
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- ===== PANEL DE SEDES ===== --}}
    <div class="row" style="margin-top:20px;"
         id="sedesPanel"
         data-base-url="{{ url('cotizacion-expres/' . $cotizacionExpres->slug . '/sedes') }}">
        <div class="col-md-12">
            <div class="box box-info" style="border-top:3px solid #00c0ef; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div style="padding:20px; border-bottom:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0; font-size:16px; font-weight:600; color:#333;">
                        Sedes de la empresa
                        <small style="color:#999; font-weight:400;">(opcional, para empresas con varias ubicaciones)</small>
                    </h3>
                    <button type="button" class="btn btn-success btn-sm"
                            style="background-color:#00a65a; border-color:#008d4c; padding:6px 15px;"
                            onclick="abrirModalSede()">
                        <i class="fa fa-plus"></i> Agregar sede
                    </button>
                </div>
                <div class="box-body" style="padding:0;">
                    <table class="table" style="width:100%; margin:0; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f9f9f9; border-bottom:2px solid #f0f0f0;">
                                <th style="padding:12px 20px; font-size:13px; color:#333;">Nombre de la sede</th>
                                <th style="padding:12px 20px; font-size:13px; color:#333;">Dirección</th>
                                <th style="padding:12px 20px; font-size:13px; color:#333;">Localidad</th>
                                <th style="padding:12px 20px; font-size:13px; color:#333; width:110px; text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="sedesTbody">
                            @forelse($sedes as $sede)
                                <tr data-id="{{ $sede->id }}"
                                    data-nombre="{{ $sede->nombreSede }}"
                                    data-direccion="{{ $sede->direccion }}"
                                    data-localidad="{{ $sede->localidad }}"
                                    style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:12px 20px; font-size:13px; color:#333;">{{ $sede->nombreSede }}</td>
                                    <td style="padding:12px 20px; font-size:13px; color:#333;">{{ $sede->direccion ?: '—' }}</td>
                                    <td style="padding:12px 20px; font-size:13px; color:#333;">{{ $sede->localidad ?: '—' }}</td>
                                    <td style="padding:12px 20px; text-align:right; white-space:nowrap;">
                                        <button type="button" class="btn btn-warning btn-xs"
                                                style="background-color:#f39c12; border-color:#e08e0b;"
                                                onclick="editarSede(this)">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs"
                                                onclick="eliminarSede(this)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="sedesVacio">
                                    <td colspan="4" style="padding:20px; text-align:center; color:#999; font-size:13px;">
                                        Esta empresa no tiene sedes registradas. La recolección usa la dirección del cliente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ============ MODAL: Crear / editar sede ============ --}}
<div class="modal fade" id="modalSede" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:6px; overflow:hidden; border:none;">
            <div style="background-image:linear-gradient(40deg,rgb(255,160,100),rgb(252,98,98)); padding:12px 16px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between;">
                <div style="background-color:#ecf0f5; position:absolute; height:200%; width:35vw; transform:rotate(30deg); right:-10vw; top:-50%;"></div>
                <span id="modalSedeTitulo" style="color:#fff; font-weight:600; font-size:14px; position:relative; z-index:1;">Agregar sede</span>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; position:relative; z-index:1; line-height:1;">&times;</button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <input type="hidden" id="sedeId" value="">
                <div class="form-group">
                    <label style="font-weight:700; color:#333; font-size:13px;">Nombre de la sede *</label>
                    <input type="text" id="sedeNombre" class="form-control" placeholder="Ej: Sede Norte, Bodega Principal">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; color:#333; font-size:13px;">Dirección</label>
                    <input type="text" id="sedeDireccion" class="form-control" placeholder="Ej: Cra 7 #45-67">
                </div>
                <div class="form-group">
                    <label style="font-weight:700; color:#333; font-size:13px;">Localidad</label>
                    <input type="text" id="sedeLocalidad" class="form-control" placeholder="Ej: Usaquén">
                </div>
                <div id="sedeError" style="display:none; color:#dd4b39; font-size:12px; margin-top:5px;"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0f0f0;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnGuardarSede"
                        style="background-color:#00a65a; border-color:#008d4c;" onclick="guardarSede()">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL: Ver documentos ============ --}}
<div class="modal fade" id="modalVerDocumentos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width:680px;">
        <div class="modal-content" style="border-radius:6px; overflow:hidden; border:none;">

            <div style="background-image:linear-gradient(40deg,rgb(255,160,100),rgb(252,98,98)); padding:12px 16px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between;">
                <div style="background-color:#ecf0f5; position:absolute; height:200%; width:35vw; transform:rotate(30deg); right:-10vw; top:-50%;"></div>
                <span style="color:#fff; font-weight:600; font-size:14px; position:relative; z-index:1;">Documentos</span>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; position:relative; z-index:1; line-height:1;">&times;</button>
            </div>

            <div class="modal-body" style="padding:0; background:#f5f5f5;">
                @php
                $docsDemo = [
                    ['nombre'=>'Nombre del documento','empresa'=>$cotizacionExpres->razon_social ?? 'Soluciones Integrales Andinas S.A.S.','fecha'=>'Abril 20 2026','hora'=>'3:22 pm'],
                    ['nombre'=>'Nombre del documento','empresa'=>$cotizacionExpres->razon_social ?? 'Soluciones Integrales Andinas S.A.S.','fecha'=>'Abril 20 2026','hora'=>null],
                    ['nombre'=>'Nombre del documento','empresa'=>$cotizacionExpres->razon_social ?? 'Soluciones Integrales Andinas S.A.S.','fecha'=>null,'hora'=>null],
                ];
                @endphp
                @foreach($docsDemo as $doc)
                <div style="display:flex; align-items:stretch; background:#fff; margin-bottom:1px;">
                    <div style="flex:1; padding:14px 16px; border-left:4px solid #27ae60;">
                        <div style="font-weight:700; font-size:13px; margin-bottom:3px;">{{ $doc['nombre'] }}</div>
                        <div style="font-size:12px; color:#555; margin-bottom:2px;">{{ $doc['empresa'] }}</div>
                        @if($doc['fecha'])<div style="font-size:12px; color:#777;">{{ $doc['fecha'] }}</div>@endif
                        @if($doc['hora'])<div style="font-size:12px; color:#777;">{{ $doc['hora'] }}</div>@endif
                        <div style="margin-top:8px; padding-top:6px; border-top:1px solid #eee;">
                            <a href="#" style="font-size:12px; color:#555; text-decoration:none;">
                                Ver <i class="fa fa-search" style="font-size:11px;"></i>
                            </a>
                        </div>
                    </div>
                    <div style="width:220px; min-width:220px; padding:10px; display:flex; align-items:center; justify-content:center;">
                        <div style="width:100%; height:140px; background:#fff; border:1px solid #ccc; border-radius:4px; box-shadow:2px 2px 6px rgba(0,0,0,.12); padding:10px; overflow:hidden;">
                            <div style="font-size:7px; color:#999; line-height:1.6;">
                                <div style="text-align:right; margin-bottom:6px; color:#aaa;">26 de septiembre de 2018<br>Guadalajara, México</div>
                                <p style="margin:0 0 3px;"><strong>Sres:</strong></p>
                                <p style="margin:0 0 3px;">Empresa de Alimentos Naturales<br>Libertad Nº 199, Guadalajara</p>
                                <p style="margin:0 0 3px;"><u>ATN: Juan Alberto Torres Bravo</u><br>Encargado de Recursos Humanos<br>Presente,</p>
                                <p style="margin:0 0 3px;">Estimado señor,</p>
                                <p style="margin:0; color:#bbb;">Junto con saludar cordialmente y según oferta de trabajo publicada en el periódico "La Nación" me es grato dirigirme a Ud. para presentar mi candidatura al cargo...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    /* Modo vista: mostrar botones de editar/eliminar */
    #cardWrapper .btns-view {
        display: flex;
    }
    #cardWrapper .btns-edit {
        display: none;
    }

    /* Modo edición: mostrar botones de guardar/cancelar, ocultar editar/eliminar */
    #cardWrapper.editing .btns-view {
        display: none;
    }
    #cardWrapper.editing .btns-edit {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    /* Modo edición: mostrar inputs, ocultar textos */
    #cardWrapper .val-input {
        display: none;
    }
    #cardWrapper.editing .val-input {
        display: block;
    }
    #cardWrapper.editing .val-text {
        display: none;
    }
</style>
<script>
function activarEdicion() {
    document.getElementById('cardWrapper').classList.add('editing');
}

function cancelarEdicion() {
    document.getElementById('cardWrapper').classList.remove('editing');
}

// ================== Sedes (CRUD AJAX) ==================
const SEDES_BASE = document.getElementById('sedesPanel').dataset.baseUrl;
const CSRF = document.querySelector('meta[name="csrf-token"]')
    ? document.querySelector('meta[name="csrf-token"]').content
    : document.querySelector('input[name="_token"]').value;

function abrirModalSede() {
    document.getElementById('modalSedeTitulo').textContent = 'Agregar sede';
    document.getElementById('sedeId').value = '';
    document.getElementById('sedeNombre').value = '';
    document.getElementById('sedeDireccion').value = '';
    document.getElementById('sedeLocalidad').value = '';
    document.getElementById('sedeError').style.display = 'none';
    $('#modalSede').modal('show');
}

function editarSede(btn) {
    const tr = btn.closest('tr');
    document.getElementById('modalSedeTitulo').textContent = 'Editar sede';
    document.getElementById('sedeId').value = tr.dataset.id;
    document.getElementById('sedeNombre').value = tr.dataset.nombre || '';
    document.getElementById('sedeDireccion').value = tr.dataset.direccion || '';
    document.getElementById('sedeLocalidad').value = tr.dataset.localidad || '';
    document.getElementById('sedeError').style.display = 'none';
    $('#modalSede').modal('show');
}

function guardarSede() {
    const id = document.getElementById('sedeId').value;
    const nombre = document.getElementById('sedeNombre').value.trim();
    const errBox = document.getElementById('sedeError');

    if (!nombre) {
        errBox.textContent = 'El nombre de la sede es obligatorio.';
        errBox.style.display = 'block';
        return;
    }

    const url = id ? `${SEDES_BASE}/${id}` : SEDES_BASE;
    const btn = document.getElementById('btnGuardarSede');
    btn.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            nombreSede: nombre,
            direccion: document.getElementById('sedeDireccion').value.trim(),
            localidad: document.getElementById('sedeLocalidad').value.trim(),
        }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) {
            errBox.textContent = data.message || 'No se pudo guardar la sede.';
            errBox.style.display = 'block';
            return;
        }
        location.reload();
    })
    .catch(() => {
        errBox.textContent = 'Error de conexión. Intenta de nuevo.';
        errBox.style.display = 'block';
    })
    .finally(() => { btn.disabled = false; });
}

function eliminarSede(btn) {
    const tr = btn.closest('tr');
    if (!confirm('¿Eliminar esta sede?')) return;

    fetch(`${SEDES_BASE}/${tr.dataset.id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'X-HTTP-Method-Override': 'DELETE',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ _method: 'DELETE' }),
    })
    .then(r => r.json())
    .then(() => location.reload())
    .catch(() => alert('No se pudo eliminar la sede.'));
}
</script>
@endsection