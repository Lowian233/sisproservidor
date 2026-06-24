@extends('layouts.app')
@section('htmlheader_title')
Editar Cotización
@endsection

@section('title')
Edición de cotización
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #469cfd, #a1ccfc); padding-right:30vw; position:relative; overflow:hidden;">
    Editar Cotización
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="box box-primary">
                <!-- form start -->
                <form role="form" action="{{ route('cotizacion.update', $cotizacion->id_cotizacion) }}" method="POST" enctype="multipart/form-data" data-toggle="validator">
                @method('PATCH')
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                           <ul>
                            @foreach ($errors->all() as $error)
                            <p>{{$error}}</p>
                            @endforeach
                            </ul>
                        </div>
                     @endif
                     <table class="table table-bordered">
                    <div class="box-body">
                        <!-- Número de Cotización (Input Oculto) -->
                        <input type="hidden" name="id_cotizacion" value="{{ $cotizacion->id_cotizacion }}">

                        <!-- Visualización del Número de Cotización -->
                        <div class="col-md-6">
                            <label>Número de Cotización</label>
                            <input class="form-control" type="text" value="{{ $cotizacion->id_cotizacion }}" readonly>
                        </div>
                        <!-- Fecha de Cotización -->
                        <div class="col-md-6">
                            <label>Fecha de cotización</label>
                            <input class="form-control" type="text" name= "FechaCotizacion"value="{{ $cotizacion->FechaCotizacion }}" readonly>
                        </div>
                        <!-- Cliente y Sede -->
                        <div class="col-md-6">
                            <label>Cliente</label>
                            <input class="form-control" type="text" name="Razon_Social" value="{{ $cotizacion->Razon_Social }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Nit</label>
                            <input class="form-control" type="text" name="Nit" value="{{ $cotizacion->Nit}}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Sede</label>
                            <input class="form-control" type="text" name="sede" value="{{ $cotizacion->sede }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Telefono</label>
                            <input class="form-control" type="text" name="Telefono" value="{{ $cotizacion->Telefono }}" readonly>
                        </div>
                        <!-- Campo Correo -->
                        <div class="col-md-6">
                            <label>Correo</label>
                            <input class="form-control" type="email" name="Correo" value="{{ $cotizacion->Correo }}" readonly>
                        </div>
                        <!-- Campo Dirección -->
                        <div class="col-md-6">
                            <label>Dirección</label>
                            <input class="form-control" type="text" name="Direccion" value="{{ $cotizacion->Direccion }}" readonly>
                        </div>
                        <!-- Campo Frecuencia de Recolección -->
                        <div class="col-md-6">
                            <label>Frecuencia de Recolección</label>
                            <input class="form-control" type="text" name="frecuencia_recoleccion" value="{{ $cotizacion->frecuencia_recoleccion }}" required>
                        </div>
                    </table>
</div>
                        <!-- Contenedor Principal de Residuos -->             
                    <div class="form-group">
                        <div id="residuosContainer">
                            @foreach($cotizacion->coti_respel as $respel) 
                               <div class="residuo-form" style="border-radius: 10px solid black;">
                               <table class="table">
                                <div class ="card text-center" style="font-size: 1.70rem; font-weight: bold; background-color: #17a2b8; color: white; padding: 5px;margin:5px;">
                                    Residuo #{{ $loop->iteration }}

                                </div>
                                <input type="hidden" name="id[]" value="{{ $respel->id }}">
                                    <!-- Campo Residuo (Nombre) -->
                                    <div class="col-md-6">
                                        <label for="residuo">Residuo</label>
                                        <input class="form-control" type="text" value="{{ $respel->respel->RespelName }}" readonly>
                                    </div>
                                    <!-- Campo oculto para el ID del residuo -->
                                    <input type="hidden" name="residuos[]" value="{{ $respel->respel->ID_Respel }}">
                                    <!-- Campo Clasificación 4741 -->
                                    <div class="col-md-6">
                                        <label for="clasf4741">Clasificación 4741</label>
                                        <input class="form-control" type="text" name="clasf4741[]" value="{{ $respel->clasf4741 }}" readonly>
                                    </div>
                                    <!-- Campo Tratamiento (Nombre) -->
                                    <div class="col-md-6">
                                        <label for="tratamiento">Tratamiento</label>
                                        <input class="form-control" type="text" value="{{ $respel->tratamiento->TratName }}" readonly>
                                    </div>
                                    <!-- Campo oculto para el ID del tratamiento -->
                                    <input type="hidden" name="tratamientos[]" value="{{ $respel->tratamiento->ID_Trat }}">
                                    <!-- Campo peligrosidad -->
                                    <div class="col-md-6">
                                        <label for="peligrosidad">peligrosidad</label>
                                        <input class="form-control" type="text" name="peligrosidad[]" value="{{ $respel->peligrosidad }}" readonly>
                                    </div>
                                    <!-- Campo Estado Físico -->
                                    <div class="col-md-6">
                                        <label>Estado Físico</label>
                                        <input class="form-control" type="text" name="estado_fisico[]" value="{{ $respel->estado_fisico }}" readonly>
                                    </div>
                                    <!-- Campo Cantidad de Kilos -->
                                    <div class="col-md-6">
                                        <label>Cantidad de Kilos</label>
                                        <input class="form-control cantidad-kilos" type="number" name="cantidad_kilos[]" value="{{ $respel->cantidad_kilos }}" step="0" required>
                                    </div>
                                     <!-- Campo Subtotal -->
                                    <div class="col-md-6">
                                        <label>Subtotal</label>
                                        <input type="hidden" name="subtotal[]" readonly>
                                        <span class="subtotalFormatted" style="font-weight: bold;">$</span>
                                    </div>
                                    <!-- Campo Precio por Kilo -->
                                    <div class="col-md-6">
                                        <label>Precio por Kilo</label>
                                        <input class="form-control precio-kg" type="number" name="precio_kg[]" value="{{ $respel->precio_kg }}" step="0" required>
                                    </div>
                                   
                                  </table>
                                </div> <!-- Cierre de residuo-form -->
                            @endforeach
                        </div> <!-- Cierre de residuosContainer -->
                    </div> <!-- Cierre de form-group -->
                </div>
                        
                         <!-- Estado de la Cotización -->
                       <div class="box" style="box-sizing: border-box;"> 
                        <div class="col-md-6">  
                            <label>Estado de La Cotización</label>
                            <select class="form-control" name="CoStatus" required>
                                <option value="Pendiente" {{ $cotizacion->CoStatus == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Aceptado" {{ $cotizacion->CoStatus == 'Aceptado' ? 'selected' : '' }}>Aceptado</option>
                                <option value="Rechazado" {{ $cotizacion->CoStatus == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                            </select>
                        </div>
                        
                        <!-- Transporte y Total -->
                        <div class="col-md-6">
                            <label>Transporte</label>
                            <input class="form-control" type="number" name="transporte" id="transporte" value="{{ $cotizacion->Transporte }}" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                                        <label for="Total">Total:</label>
                                        <!-- Campo numérico oculto usado para cálculos -->
                                        <input type="hidden" class="form-control" id="Total" name="Total" readonly>
                                        <!-- Campo visible para mostrar el total formateado -->
                                        <input type="text" class="form-control" id="TotalFormatted" readonly>
                        </div>

                    </div>
                    </div>
                
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
            <!-- /.box -->
            
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Evento DOMContentLoaded disparado');

        // Cálculo inicial de todos los subtotales y el total general al cargar la página
        calcularTodosLosSubtotalesYTotal();

        // Asignar eventos 'input' a los campos de kilos y precio
        document.querySelectorAll('input[name="cantidad_kilos[]"], input[name="precio_kg[]"]').forEach(input => {
            input.addEventListener('input', calcularTodosLosSubtotalesYTotal);
        });

        // Asignar evento 'input' al campo de transporte
        const transporteInput = document.getElementById('transporte');
        if (transporteInput) {
            transporteInput.addEventListener('input', calcularTotalGeneral);
        } 
    });

    function formatearMoneda(valor) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(valor);
    }

    function calcularTodosLosSubtotalesYTotal() {
        console.log('Función calcularTodosLosSubtotalesYTotal llamada');
        const residuos = document.querySelectorAll('.residuo-form');
        let totalSubtotales = 0;

        residuos.forEach((residuo) => {
            const cantidadKilos = parseFloat(residuo.querySelector('input[name="cantidad_kilos[]"]').value) || 0;
            const precioKg = parseFloat(residuo.querySelector('input[name="precio_kg[]"]').value) || 0;
            const subtotal = cantidadKilos * precioKg;

            console.log(`Cantidad de Kilos: ${cantidadKilos}, Precio por Kilo: ${precioKg}, Subtotal: ${subtotal}`);

            const subtotalInput = residuo.querySelector('input[name="subtotal[]"]');
            if (subtotalInput) subtotalInput.value = subtotal.toFixed(0);

            const subtotalSpan = residuo.querySelector('.subtotalFormatted');
            if (subtotalSpan) subtotalSpan.textContent = formatearMoneda(subtotal);

            totalSubtotales += subtotal;
        });

        calcularTotalGeneral(totalSubtotales);
    }

    // Función para calcular el total general
function calcularTotalGeneral() {
    let subtotales = document.querySelectorAll('input[name="subtotal[]"]');
    let totalGeneral = 0;
    
    // Sumar los subtotales
    subtotales.forEach(function(input) {
        totalGeneral += parseFloat(input.value) || 0;
    });
    
    // Añadir el valor del transporte
    let transporte = parseFloat(document.querySelector('input[name="transporte"]').value) || 0;
    totalGeneral += transporte;
    
    // Almacenar el total numérico (valor usado en cálculos)
    document.getElementById('Total').value = totalGeneral.toFixed(2);
    
    // Formatear el total a COP y mostrarlo en el campo visible
    let totalFormatted = totalGeneral.toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
    document.getElementById('TotalFormatted').value = totalFormatted;
}
</script>
@endsection
