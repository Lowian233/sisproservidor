@extends('layouts.cotizaciones')

@section('htmlheader_title')
    Cargar documentos
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 160, 100), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
    Cotizaciones de servicio express
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid">

    <div class="box box-info">
        <div class="box-body" style="padding: 20px;">
            <div class="row">

                {{-- Tarjeta 1: Cargar varios documentos --}}
                <div class="col-sm-4">
                    <div style="display:flex; align-items:center; gap:14px; padding-bottom:16px; border-bottom:1px solid #eee;">
                        <div style="min-width:80px; height:80px; background:#27ae60; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-folder-open" style="font-size:34px; color:#fff;"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:700; font-size:14px; margin-bottom:3px;">Cargar varios documentos</div>
                            <div style="font-size:12px; color:#555; margin-bottom:8px;">{{ $razonSocial }}</div>
                            <label style="display:inline-flex; align-items:center; gap:5px; color:#888; font-size:12px; cursor:pointer; font-weight:400;">
                                <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="notificarCarga(this,'varios')">
                                Cargar <i class="fas fa-upload" style="font-size:11px;"></i>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Tarjeta 2: Comprobante de pago --}}
                <div class="col-sm-4">
                    <div style="display:flex; align-items:center; gap:14px; padding-bottom:16px; border-bottom:1px solid #eee;">
                        <div style="min-width:80px; height:80px; background:#1abc9c; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-file-upload" style="font-size:34px; color:#fff;"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:700; font-size:14px; margin-bottom:3px;">Comprobante de pago</div>
                            <div style="font-size:12px; color:#555; margin-bottom:8px;">{{ $razonSocial }}</div>
                            <label style="display:inline-flex; align-items:center; gap:5px; color:#888; font-size:12px; cursor:pointer; font-weight:400;">
                                <input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="notificarCarga(this,'comprobante')">
                                Cargar <i class="fas fa-upload" style="font-size:11px;"></i>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Tarjeta 3: Cédula --}}
                <div class="col-sm-4">
                    <div style="display:flex; align-items:center; gap:14px; padding-bottom:16px; border-bottom:1px solid #eee;">
                        <div style="min-width:80px; height:80px; background:#2980b9; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-file-upload" style="font-size:34px; color:#fff;"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:700; font-size:14px; margin-bottom:3px;">Cedula</div>
                            <div style="font-size:12px; color:#555; margin-bottom:8px;">{{ $razonSocial }}</div>
                            <label style="display:inline-flex; align-items:center; gap:5px; color:#888; font-size:12px; cursor:pointer; font-weight:400;">
                                <input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="notificarCarga(this,'cedula')">
                                Cargar <i class="fas fa-upload" style="font-size:11px;"></i>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div style="margin-top:8px;">
        <a href="{{ route('cotizacion-expres.show', $slug) }}" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
    </div>

</div>

{{-- Toast de confirmación --}}
<div id="toastCarga" style="display:none; position:fixed; bottom:24px; right:24px; background:#27ae60; color:#fff; padding:10px 20px; border-radius:6px; font-size:13px; box-shadow:0 2px 8px rgba(0,0,0,.2); z-index:9999;">
    <i class="fa fa-check"></i> Archivo seleccionado correctamente
</div>

@endsection

@section('scripts')
<script>
function notificarCarga(input, tipo) {
    if (input.files && input.files.length > 0) {
        var toast = document.getElementById('toastCarga');
        toast.style.display = 'block';
        setTimeout(function(){ toast.style.display = 'none'; }, 2500);
    }
}
</script>
@endsection
