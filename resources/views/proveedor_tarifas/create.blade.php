@extends('layouts.app')
@section('htmlheader_title')
Tarifas del Proveedor
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
    Tarifas del Proveedor
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<style>
    .tipo-kg { color: #000000; }
    .tipo-otro { color: #008000; }
</style>
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <!-- Default box -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>{{ $proveedor->CliShortname }}</b></h3>
                    <div class="box-tools pull-right">
                        <a class="btn btn-default btn-close pull-right" style="margin-right: 1.7rem;" href="{{ route('contactos.show', ['contacto' => $proveedor->CliSlug])}}"><b><i class="fas fa-backspace" color="red"></i> Volver al Proveedor</b></a>
                    </div>
                </div>
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <!-- /.box-header -->
                            <!-- form start -->

                            <form role="form" action="{{route('proveedor-tarifas.store', ['slug' => $proveedor->CliSlug])}}" method="POST" id="createtratamientoForm">
                                @csrf
                                @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                        <li>{{$error}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                <div class="box-body" id="boxbodypretrat">
                                    <div class="col-md-12">
                                        <label>
                                            <input type="radio" name="tipo_tarifa" value="tratamiento" id="tipo_tratamiento" checked onchange="toggleTipoTarifa()">
                                            Por Tratamiento
                                        </label>
                                        &nbsp;&nbsp;&nbsp;
                                        <label>
                                            <input type="radio" name="tipo_tarifa" value="concepto" id="tipo_concepto" onchange="toggleTipoTarifa()">
                                            Por Concepto (Transporte, Alquiler, etc.)
                                        </label>
                                    </div>
                                    <div class="col-md-6" id="div_tratamiento">
                                        <label for="select2trat">Tratamiento</label>
                                        <select class="form-control select" id="select2trat" name="FK_Tratamiento">
                                            <option value="">Seleccione un tratamiento</option>
                                            @foreach ($tratamientos as $tratamiento)
                                            <option value="{{$tratamiento->ID_Trat}}" @if(isset($tratamientoSeleccionado) && $tratamientoSeleccionado == $tratamiento->ID_Trat) selected @endif>{{$tratamiento->TratName}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="div_concepto" style="display:none;">
                                        <label for="PTarifaConcepto">Concepto del Servicio</label>
                                        <input type="text" class="form-control" id="PTarifaConcepto" name="PTarifaConcepto" placeholder="Ej: Transporte Mosquera, Alquiler contenedor" maxlength="255">
                                    </div>
                                    <div class="col-md-6" id="div_categoria" style="display:none;">
                                        <label for="PTarifaCategoria">Categoría</label>
                                        <select class="form-control select" id="PTarifaCategoria" name="PTarifaCategoria">
                                            <option value="">Seleccione categoría</option>
                                            <option value="Transporte">Transporte</option>
                                            <option value="Alquiler">Alquiler</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="input1">Rango</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Desde </span>
                                            <input type="number" class="form-control" aria-label="Cantidad mas cercana la unidad" name="PTarifaDesde" required min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="select2tipo">Unidad</label>
                                        <select class="form-control select" id="select2tipo" name="PTarifatipo" required>
                                            <option selected value="Kg">Kg</option>
                                            <option value="Unid">Unidades</option>
                                            <option value="Lt">Litros</option>
                                            <option value="Viaje">Viaje</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="input2">Precio</label>
                                        <input id="input2" class="form-control" type="number" min="0" step="0.01" name="PTarifaPrecio" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="select2frecuencia">Frecuencia</label>
                                        <select class="form-control select" id="select2frecuencia" name="PTarifaFrecuencia" required>
                                            <option value="Servicio">Servicio</option>
                                            <option value="Mensual">Mensual</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="input3">Vencimiento</label>
                                        <input min="{{date('Y-m-d')}}" id="input3" class="form-control" type="date" name="PTarifaVencimiento" required>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-success pull-right" style="margin-right: 1.7rem;"><i class="fas fa-check"></i> Agregar Tarifa</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.box-body -->
                </div>
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="box">
                            <table id="TarifasProveedorTable" class="table table-compact table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tratamiento/Concepto</th>
                                        <th>Categoría</th>
                                        <th>Rango</th>
                                        <th>Frecuencia</th>
                                        <th>Precio</th>
                                        <th>Vence</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($proveedor->proveedorTarifas && $proveedor->proveedorTarifas->count() > 0)
                                    @foreach ($proveedor->proveedorTarifas->where('PTarifaDelete', 0) as $tarifa)
                                    @foreach ($tarifa->rangos as $rango)
                                    @php
                                        $claseTipo = $tarifa->PTarifatipo == 'Kg' ? 'tipo-kg' : 'tipo-otro';
                                    @endphp
                                    <tr>
                                        <td>{{$tarifa->ID_PTarifa}}</td>
                                        <td>
                                            @if($tarifa->FK_Tratamiento)
                                                <strong>{{$tarifa->tratamiento->TratName ?? 'N/A'}}</strong>
                                            @else
                                                <strong>{{$tarifa->PTarifaConcepto ?? 'N/A'}}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tarifa->PTarifaCategoria)
                                                <span class="badge bg-info">{{$tarifa->PTarifaCategoria}}</span>
                                            @else
                                                <span class="badge bg-primary">Tratamiento</span>
                                            @endif
                                        </td>
                                        <td>desde {{number_format($rango->PTarifaDesde, 2, ',', '.')}} <b class="{{$claseTipo}}">{{$tarifa->PTarifatipo}}</b></td>
                                        <td>{{$tarifa->PTarifaFrecuencia}}</td>
                                        <td>${{number_format($rango->PTarifaPrecio, 2, ',', '.')}}</td>
                                        <td>{{$tarifa->PTarifaVencimiento ? date('d/m/Y', strtotime($tarifa->PTarifaVencimiento)) : 'N/A'}}</td>
                                        <td>
                                            <form method="POST" id="Eliminar{{$rango->ID_PRango}}" action="{{route('proveedor-tarifas.destroy', ['slug' => $proveedor->CliSlug, 'ID_PRango' => $rango->ID_PRango])}}">
                                                @csrf
                                                @method('DELETE')
                                                <input form="Eliminar{{$rango->ID_PRango}}" type="submit" class="btn btn-danger btn-xs" value="Borrar">
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay tarifas registradas para este proveedor.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!--/.col (right) -->
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>
@section('NewScript')
<script>
    function toggleTipoTarifa() {
        var tipoTratamiento = document.getElementById('tipo_tratamiento').checked;
        var divTratamiento = document.getElementById('div_tratamiento');
        var divConcepto = document.getElementById('div_concepto');
        var divCategoria = document.getElementById('div_categoria');
        var selectTratamiento = document.getElementById('select2trat');
        var inputConcepto = document.getElementById('PTarifaConcepto');
        var selectCategoria = document.getElementById('PTarifaCategoria');
        
        if (tipoTratamiento) {
            divTratamiento.style.display = 'block';
            divConcepto.style.display = 'none';
            divCategoria.style.display = 'none';
            selectTratamiento.setAttribute('required', 'required');
            inputConcepto.removeAttribute('required');
            selectCategoria.removeAttribute('required');
            inputConcepto.value = '';
            selectCategoria.value = '';
        } else {
            divTratamiento.style.display = 'none';
            divConcepto.style.display = 'block';
            divCategoria.style.display = 'block';
            selectTratamiento.removeAttribute('required');
            inputConcepto.setAttribute('required', 'required');
            selectCategoria.setAttribute('required', 'required');
            selectTratamiento.value = '';
        }
    }
    
    // Inicializar al cargar la página
    $(document).ready(function() {
        toggleTipoTarifa();
    });
</script>
@endsection
@endsection

