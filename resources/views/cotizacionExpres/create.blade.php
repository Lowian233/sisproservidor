@extends('layouts.cotizaciones')

@section('htmlheader_title')
    Nueva Cotización Express
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 160, 100), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
    Nueva Cotización Express
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid">

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="box box-info" style="border-top:3px solid #00c0ef; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="padding:20px; border-bottom:1px solid #f0f0f0;">
                    <h3 style="margin:0; font-size:16px; font-weight:600; color:#333;">
                        Formulario de solicitud
                    </h3>
                </div>

                <form action="{{ route('cotizacion-expres.store') }}" method="POST">
                    @csrf

                    <div class="box-body" style="padding:25px;">

                        {{-- SECCIÓN 1: DATOS EMPRESA --}}
                        <div style="margin-bottom:30px;">
                            <h4 style="font-weight:700; color:#333; margin-bottom:20px; padding-bottom:10px; border-bottom:2px solid #f0f0f0;">
                                <i class="fa fa-building" style="color:#0066cc; margin-right:8px;"></i>
                                Datos de la Empresa
                            </h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nit" style="font-weight:600; color:#333; font-size:13px;">NIT <span style="color:red;">*</span></label>
                                        <input type="text" id="nit" name="nit" class="form-control"
                                               placeholder="Ej: 1234567890" value="{{ old('nit') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombreEmpresa" style="font-weight:600; color:#333; font-size:13px;">Nombre de la Empresa <span style="color:red;">*</span></label>
                                        <input type="text" id="nombreEmpresa" name="nombreEmpresa" class="form-control"
                                               placeholder="Ej: Empresa Ltda" value="{{ old('nombreEmpresa') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ciudadEmpresa" style="font-weight:600; color:#333; font-size:13px;">Ciudad</label>
                                        <input type="text" id="ciudadEmpresa" name="ciudadEmpresa" class="form-control"
                                               placeholder="Ej: Bogotá" value="{{ old('ciudadEmpresa') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="direccion" style="font-weight:600; color:#333; font-size:13px;">Dirección</label>
                                        <input type="text" id="direccion" name="direccion" class="form-control"
                                               placeholder="Ej: Calle 1 No. 23-45" value="{{ old('direccion') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="numeroEmpresa" style="font-weight:600; color:#333; font-size:13px;">Teléfono Principal</label>
                                        <input type="text" id="numeroEmpresa" name="numeroEmpresa" class="form-control"
                                               placeholder="Ej: 3001234567" value="{{ old('numeroEmpresa') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="numero_contacto" style="font-weight:600; color:#333; font-size:13px;">Número de Contacto Alterno</label>
                                        <input type="text" id="numero_contacto" name="numero_contacto" class="form-control"
                                               placeholder="Ej: 3109876543" value="{{ old('numero_contacto') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="correoEmpresa" style="font-weight:600; color:#333; font-size:13px;">Correo Electrónico</label>
                                <input type="email" id="correoEmpresa" name="correoEmpresa" class="form-control"
                                       placeholder="Ej: info@empresa.com" value="{{ old('correoEmpresa') }}">
                            </div>
                        </div>

                        {{-- SECCIÓN 2: CONTACTOS --}}
                        <div style="margin-bottom:30px;">
                            <h4 style="font-weight:700; color:#333; margin-bottom:20px; padding-bottom:10px; border-bottom:2px solid #f0f0f0;">
                                <i class="fa fa-user" style="color:#0066cc; margin-right:8px;"></i>
                                Datos de Contacto
                            </h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="encargado" style="font-weight:600; color:#333; font-size:13px;">Encargado</label>
                                        <input type="text" id="encargado" name="encargado" class="form-control"
                                               placeholder="Ej: Juan Pérez" value="{{ old('encargado') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombreRepLegal" style="font-weight:600; color:#333; font-size:13px;">Representante Legal</label>
                                        <input type="text" id="nombreRepLegal" name="nombreRepLegal" class="form-control"
                                               placeholder="Ej: Carlos López" value="{{ old('nombreRepLegal') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="identificacionRepLegal" style="font-weight:600; color:#333; font-size:13px;">Documento de Identidad</label>
                                        <input type="text" id="identificacionRepLegal" name="identificacionRepLegal" class="form-control"
                                               placeholder="Ej: 1098765432" value="{{ old('identificacionRepLegal') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lugarExpedicion" style="font-weight:600; color:#333; font-size:13px;">Lugar de Expedición</label>
                                        <input type="text" id="lugarExpedicion" name="lugarExpedicion" class="form-control"
                                               placeholder="Ej: Bogotá" value="{{ old('lugarExpedicion') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BOTONES --}}
                        <div style="display:flex; gap:8px; justify-content:flex-end; border-top:1px solid #f0f0f0; padding-top:20px;">
                            <a href="{{ route('cotizacion-expres.index') }}" class="btn btn-default" style="padding:8px 20px; background:#f4f4f4; border:1px solid #ddd;">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success" style="padding:8px 20px; background-color:#00a65a; border-color:#008d4c;">
                                <i class="fa fa-save"></i> Crear Solicitud
                            </button>
                        </div>

                    </div>
                </form>
            </div>
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
        margin-bottom: 15px;
    }
</style>
@endsection
