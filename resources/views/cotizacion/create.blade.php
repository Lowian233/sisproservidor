@extends('layouts.app')

@section('htmlheader_title')
Crear Cotizacion
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #469cfd, #a1ccfc); padding-right:30vw; position:relative; overflow:hidden;">
    Crear Nueva Cotizacion
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<!-- Template de opciones de Tratamiento (oculto) -->
<div id="tratamiento-options-template" style="display: none;">
    <option value="">Seleccione un tratamiento</option>
    @foreach($tratamientos as $tratamiento)
        <option value="{{ $tratamiento->ID_Trat }}">{{ $tratamiento->TratName }}</option>
    @endforeach
    
</div>
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Crear Cotizacion</h3>
                    <a onclick="Clientenuevo()" class="btn btn-primary" style="float: right;">Cliente Nuevo</a>
                </div>
                <div class="row" id="ClienteExistente">
                    <div class="col-md-12">
                        <div class="box box-primary"> 
                            <div class="form-group col-md-12">
                                <br>
								@if(in_array(Auth::user()->UsRol, ['Comercial','Comercialap','Ejecutivo Comercial']) || in_array(Auth::user()->UsRol2, ['Comercial','Comercialap','Ejecutivo Comercial']))
								<div class="panel panel-default" style="margin-bottom: 15px;">
									<div class="panel-heading">
										<strong>Mi firma (para PDF de cotizaci??n)</strong>
									</div>
									<div class="panel-body">
										<div class="row">
											<div class="col-md-4">
												<img
													src="{{ asset('img/' . Auth::user()->id . '.png') }}"
													onerror="this.onerror=null;this.src='{{ asset('img/5.png') }}';"
													alt="Firma"
													style="max-width: 100%; max-height: 140px; background: #fff; border: 1px solid #eee; padding: 8px;"
												/>
												<p style="margin-top: 8px; color: #777; font-size: 12px;">
													Sube una imagen <strong>PNG</strong> (ideal con fondo transparente).
												</p>
											</div>
											<div class="col-md-8">
												<form action="{{ route('cotizacion.firma') }}" method="POST" enctype="multipart/form-data">
													@csrf
													<div class="form-group">
														<label>Actualizar firma</label>
														<input type="file" name="firma" class="form-control" accept="image/png">
													</div>
													<button type="submit" class="btn btn-info">
														<i class="fa fa-upload"></i> Guardar nueva firma
													</button>
												</form>
											</div>
										</div>
									</div>
								</div>
								@endif
                                <form role="form" action="/cotizacion/createclientext" method="post" enctype="multipart/form-data" data-toggle="validator">
                                    @csrf
                                    <label for="Cliente" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Cliente</b>" data-content="Cliente"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Seleccione el Cliente</label>
                                    <small class="help-block with-errors">*</small>
                                    <select name="Cliente" id="Cliente" class="form-control select" required>
                                        <option value="">{{ __('adminlte::message.select') }}</option>
                                            @foreach($clientes as $Cliente)
                                                <option value="{{$Cliente->ID_Cli}}">{{$Cliente->CliName}}</option>
                                            @endforeach
                                        </select>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-info" style="margin: 10px 30px;" id="btn-generar">{{ __('Crear') }}</button>
                                    </div>        
                                </form>    
                            </div>
                        </div>
                    </div>
                </div>           
            <div id="clienteNuevo" style="display: none;">  
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">                            
                            <form role="form" action="/cotizacion" method="POST" enctype="multipart/form-data">
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
                                <div class="box-body">
                                 <div style="margin-top: 20px; margin-bottom: 20px;">
                                    <div class="col-md-6">
                                        <label for="Nit">NIT</label>
                                        <input type="text" class="form-control" name="Nit" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="Razon_Social">Razon Social</label>
                                        <input type="text" class="form-control" name="Razon_Social" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="telefono">Telefono</label>
                                        <input type="text" class="form-control" name="Telefono" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="Correo">Correo</label>
                                        <input type="email" class="form-control" name="Correo">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="Direccion">Direccion</label>
                                        <input type="text" class="form-control" name="Direccion" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sede">Sede</label>
                                        <input type="text" class="form-control" name="sede" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="frecuencia_recoleccion">Frecuencia De Recolecion</label>
                                        <input type="text" class="form-control" name="frecuencia_recoleccion" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transporte">Transporte</label>
                                        <input type="number" class="form-control" name="transporte" required step="0" value="" oninput="calcularTotalGeneral()">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_cotizacion">Tipo de Cotizacion</label>
                                        <select class="form-control" name="tipo_cotizacion" required>
                                            <option value="">Seleccione tipo...</option>
                                            <option value="Visitas">Visitas</option>
                                            <option value="Pagina Web">Pagina Web</option>
                                            <option value="Correo">Correo</option>
                                        </select>
                                    </div>
                                    </div>
                                
                                    <div style="text-align: right;  margin-bottom: 20px;">
                                <!-- Botn Crear Residuo con Icono -->
                                        <a href="{{ route('respelspublic.create') }}" target="_blank" class="btn btn-success"style="margin-bottom: -29px;">
                                            <i class="fas fa-plus-circle"></i> Crear Un Nuevo Residuo
                                        </a>
                                    </div>
                                    <div id="residuosContainer" class="col-md-12">
                                    <!-- Aqu se agregar el primer conjunto de residuos -->
                                    <div class="residuo-form" style="margin-bottom: 20px;"> <!-- Espacio a09adido -->
                                        <div class="form-group">
                                            <label for="residuo">Selecciona el Residuo:</label>
                                            <select name="residuos[]" class="form-control" required onchange="mostrarPeligrosidad(this); mostrarClasificacion(this); checkResidueStatus();">
                                            <option value="">Seleccione un residuo</option>
                                            @foreach($residuoscomunes as $residuo)
                                            <option value="{{ $residuo->ID_Respel }}"
                                                data-nombre="{{ $residuo->RespelName }}"
                                                data-clasificacion="{{ $residuo->clasificacion }}"
                                                data-peligrosidad="{{ $residuo->RespelIgrosidad }}"
                                                data-status="{{ $residuo->RespelStatus }}">
                                                {{ $residuo->RespelName }}
                                            </option>
                                            @endforeach
                                            </select>
                                        <div class="peligrosidadContainer" style="display: none;margin-bottom: 20px;">
                                            <div style="background-image: linear-gradient(40deg, #ff4f4f, #efdcdc); padding-left: 02vw; position:relative; overflow:hidden; width:73vw;height:3vw;border-radius: 1vw;line-height: 3vw;margin: 1vw;">
                                            <strong>Peligrosidad del Residuo:</strong> <span class="peligrosidadTexto"></span>
                                            <span style="background-color:#ffff; position:absolute; height:138%; width:37vw; transform:rotate(30deg); right:-17vw; top:-45%;"></span>
                                            </div>
                                            <div class="contenedorTextoClasi">
                                            <div class="col-md-6" style="margin-top: 1.3vw;padding-left: unset;">
                                                <label for="clasf4741">Clasificacion 4741</label>
                                                <input type="text" class="form-control clasificacion" name="clasf4741[]" readonly>
                                            </div>
                                        </div>
                                        <!-- Campo Oculto para Peligrosidad -->
                                        <input type="hidden" name="peligrosidad[]" class="peligrosidadInput">
                                        </div>
                                        <div class="col-md-6" style="margin-top: 1vw;padding-right: unset;">
                                                <label for="estado_fisico">Estado Fisico</label>
                                                <select class="form-control" name="estado_fisico[]" required>
                                                    <option value="Solido">Solido</option>
                                                    <option value="Liquido">Liquido</option>
                                                    <option value="Gaseoso">Gaseoso</option>
                                                    <option value="Aerosol">Aerosol</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                     <!-- agregar tratamiento -->
                                     <div id="tratamientos-container-0" class="tratamientos-container">
                                     <div class="tratamiento-row">
                                        <div class="form-group" style="margin-top: 8vw;">
                                            <label for="ID_Trat">Selecciona el Tratamiento:</label>
                                            <select name="tratamientos[0][]" class="form-control" required>
                                                <option value="">Seleccione un tratamiento</option>
                                                @foreach($tratamientos as $tratamiento)
                                                <option value="{{ $tratamiento->ID_Trat }}">{{ $tratamiento->TratName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                       <!-- Campos para cantidad de kilos y precio por kilo -->
                                        <div class="row residuo-form">
                                            <div class="col-md-6">
                                                <label for="cantidad_kilos">Cantidad de Kilos</label>
                                                <input type="number" class="form-control" name="cantidad_kilos[0][]" required min="0" step="0.01" oninput="calcularSubtotal(this)">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="precio_kg">Precio por Kilo</label>
                                                <input type="number" class="form-control" name="precio_kg[0][]" required step="0.01" value="0" oninput="calcularSubtotal(this)">
                                            </div>

                                            <!-- Subtotal calculado numericamente (oculto o no editable) -->
                                            <div class="col-md-12" style="margin-top: 30px;">
                                                <label for="subtotal">Subtotal</label>
                                                <!-- Campo numerico oculto usado para clculos -->
                                                <input type="hidden" class="form-control subtotal" name="subtotal[0][]" readonly style="display:none;">
                                                
                                                <!-- input donde se mostrar el subtotal formateado -->
                                                <span class="subtotalFormatted" style="font-weight: bold;"></span>
                                            </div>
                                            </div>
                                        </div>                      
                                    </div>
                                    </div>
                                    <div><button type="button" class="btn btn-primary add-tratamiento" data-residuo-index="0" style="margin-top: 10px; margin-bottom: 20px;">Agregar Tratamiento</button>
                                    </div>
                                    </div>
                                    <div style="text-align: right; padding-left: 10vw;margin-right: 2vw;">
                                        <button type="button" class="btn btn-primary" id="agregarResiduoBtn" style="margin-top: 10px;"><i class="fas fa-plus-circle"></i> Agregar Residuo</button>
                                        
                                    </div>
                                    <div class="col-md-12" style="margin-top: 20px;">
                                        <label for="Observaciones">Observaciones</label>
                                    <textarea class="form-control" name="Observaciones" rows="3"></textarea>
                                </div>  
                                <!-- Campo para el total general -->
                                <div class="col-md-6" style="text-align: center; margin-top: 20px;">
                                        <label for="Total">Total:</label>
                                        <!-- Campo numerico oculto usado para clculos -->
                                        <input type="hidden" class="form-control" id="Total" name="Total" readonly>
                                        <!-- Campo visible para mostrar el total formateado -->
                                        <input type="text" class="form-control" id="TotalFormatted" readonly>
                                    </div>
                                    <div>
                                    <div class="col-md-6"style="margin-top: 1.7vw;">
                                                    <label for="CoStatus">Estado</label>
                                                    <select class="form-control"  name="CoStatus" required >
                                                        <option value="Pendiente">Pendiente</option>
                                                        <option value="Aceptado" disabled>Aceptado</option>
                                                        <option value="Rechazado">Rechazado</option>
                                                    </select>
                                    </div>
                                    </div> 
                                    <!-- Campo oculto para el estado de la cotizacin -->
                                    <input type="hidden" name="status" value="Pendiente"> 
                                    <div class="col-md-12" style="margin-top: 1.7vw;">
                                        <div class="box-footer" style="margin-right: 5vw;">
                                                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Crear Cotizacion</button>
                                                <a class="btn btn-default pull-right"  onclick="AgregarRes()"><i class="fas fa-backspace" color="red"></i> Cancelar</a>
                                        </div>
                                    </div>  
                                                     
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let contadorResiduos = 1; // Inicia en 1 ya que el primer residuo usa indice 0

    // Funcin para inicializar los botones de agregar tratamiento
    function initializeAddTratamientoButtons() {
        document.querySelectorAll('.add-tratamiento').forEach(function(button) {
            button.removeEventListener('click', handleAddTratamiento); // Evitar multiples listeners
            button.addEventListener('click', handleAddTratamiento);
        });
    }

    function handleAddTratamiento() {
        const residuoIndex = this.getAttribute('data-residuo-index');
        const container = document.getElementById(`tratamientos-container-${residuoIndex}`);
        const tratamientoOptions = document.getElementById('tratamiento-options-template').innerHTML;

        // Crear una nueva fila de tratamiento
        const newRow = document.createElement('div');
        newRow.classList.add('tratamiento-row');

        // Contenido de la nueva fila de tratamiento
        newRow.innerHTML = `
            <div class="form-group" style="margin-top: 8vw;">
                <label for="ID_Trat">Selecciona el Tratamiento:</label>
                <select name="tratamientos[${residuoIndex}][]" class="form-control" required>
                    ${tratamientoOptions}
                </select>
            </div>
            <!-- Campos para cantidad de kilos y precio por kilo -->
            <div class="row residuo-form">
                <div class="col-md-6">
                    <label for="cantidad_kilos">Cantidad de Kilos</label>
                    <input type="number" class="form-control" name="cantidad_kilos[${residuoIndex}][]" required min="0" step="0.01" oninput="calcularSubtotal(this)">
                </div>

                <div class="col-md-6">
                    <label for="precio_kg">Precio por Kilo</label>
                    <input type="number" class="form-control" name="precio_kg[${residuoIndex}][]" required step="0.01" value="0" oninput="calcularSubtotal(this)">
                </div>

                <!-- Subtotal calculado numericamente (oculto o no editable) -->
                <div class="col-md-12" style="margin-top: 30px;">
                    <label for="subtotal">Subtotal</label>
                    <input type="hidden" class="form-control subtotal" name="subtotal[${residuoIndex}][]" readonly style="display:none;">
                    <span class="subtotalFormatted" style="font-weight: bold;"></span>
                </div>
            </div>
        `;

        // A09adir la nueva fila al contenedor de tratamientos
        container.appendChild(newRow);
    }

    // Inicializar los botones existentes
    initializeAddTratamientoButtons();

    // Manejar la adicin de nuevos residuos
    document.getElementById("agregarResiduoBtn").addEventListener("click", function() {
        const residuoIndex = contadorResiduos;
        contadorResiduos++; // Incrementar el contador para el prximo residuo

        // Crear nuevo contenedor de residuo
        const nuevoContenedor = document.createElement("div");
        nuevoContenedor.classList.add('residuo-form');
        nuevoContenedor.style.marginBottom = '10px';
        nuevoContenedor.style.position = 'relative';

        // Crear el ttulo del residuo
        const tituloResiduo = document.createElement("div");
        tituloResiduo.style.marginTop = '10px';
        tituloResiduo.style.marginBottom = '10px';
        tituloResiduo.style.background = '#ffbb33';
        tituloResiduo.style.padding = '10px';
        tituloResiduo.style.borderRadius = '5px';
        tituloResiduo.style.textAlign = 'center';
        tituloResiduo.style.color = 'black';
        tituloResiduo.style.fontSize = 'larger';
        tituloResiduo.textContent = `Residuo ${residuoIndex + 1}`;

        // A09adir el ttulo al nuevo contenedor
        nuevoContenedor.appendChild(tituloResiduo);

        // Contenido del nuevo residuo (el formulario)
        const contenidoResiduo = `
            <div class="form-group">
                <label for="residuo">Selecciona el Residuo:</label>
                <select name="residuos[]" class="form-control" required onchange="mostrarPeligrosidad(this); mostrarClasificacion(this); checkResidueStatus();">
                    <option value="">Seleccione un residuo</option>
                    @foreach($residuoscomunes as $residuo)
                    <option value="{{ $residuo->ID_Respel }}" data-nombre="{{ $residuo->RespelName }}"
                        data-clasificacion="{{ $residuo->clasificacion }}" data-peligrosidad="{{ $residuo->RespelIgrosidad }}"
                        data-status="{{ $residuo->RespelStatus }}">
                        {{ $residuo->RespelName }}
                    </option>
                    @endforeach
                </select>
                <div class="peligrosidadContainer" style="display: none;margin-bottom: 20px;">
                    <div style="background-image: linear-gradient(40deg, #ff4f4f, #efdcdc); padding-left: 02vw; position:relative; overflow:hidden; width:73vw;height:3vw;border-radius: 1vw;line-height: 3vw;margin: 1vw;">
                        <strong>Peligrosidad del Residuo:</strong> <span class="peligrosidadTexto"></span>
                        <span style="background-color:#ffff; position:absolute; height:138%; width:37vw; transform:rotate(30deg); right:-17vw; top:-45%;"></span>
                    </div>
                    <div class="contenedorTextoClasi">
                        <div class="col-md-6" style="margin-top: 1.3vw;padding-left: unset;">
                            <label for="clasf4741">Clasificacin 4741</label>
                            <input type="text" class="form-control clasificacion" name="clasf4741[]" readonly>
                        </div>
                    </div>
                    <!-- Campo Oculto para Peligrosidad -->
                    <input type="hidden" name="peligrosidad[]" class="peligrosidadInput">
                </div>
                <div class="col-md-6" style="margin-top: 1vw;padding-right: unset;">
                    <label for="estado_fisico">Estado Fisico</label>
                    <select class="form-control" name="estado_fisico[]" required>
                        <option value="Solido">Solido</option>
                        <option value="Liquido">Liquido</option>
                        <option value="Gaseoso">Gaseoso</option>
                        <option value="Aerosol">Aerosol</option>
                    </select>
                </div>
            </div>
            
            <!-- Ttulo de Tratamientos -->
            <div class="tratamientos-title" style="margin-top: 20px; font-weight: bold;">
                Tratamientos
            </div>
            
            <!-- Contenedor de Tratamientos para este Residuo -->
            <div id="tratamientos-container-${residuoIndex}" class="tratamientos-container">
                <div class="tratamiento-row">
                    <div class="form-group" style="margin-top: 8vw;">
                        <label for="ID_Trat">Selecciona el Tratamiento:</label>
                        <select name="tratamientos[${residuoIndex}][]" class="form-control" required>
                            <option value="">Seleccione un tratamiento</option>
                            @foreach($tratamientos as $tratamiento)
                            <option value="{{ $tratamiento->ID_Trat }}">{{ $tratamiento->TratName }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Campos para cantidad de kilos y precio por kilo -->
                    <div class="row residuo-form">
                        <div class="col-md-6">
                            <label for="cantidad_kilos">Cantidad de Kilos</label>
                            <input type="number" class="form-control" name="cantidad_kilos[${residuoIndex}][]" required min="0" step="0.01" oninput="calcularSubtotal(this)">
                        </div>
                        <div class="col-md-6">
                            <label for="precio_kg">Precio por Kilo</label>
                            <input type="number" class="form-control" name="precio_kg[${residuoIndex}][]" required step="0.01" value="0" oninput="calcularSubtotal(this)">
                        </div>
                        <!-- Subtotal calculado -->
                        <div class="col-md-12" style="margin-top: 30px;">
                            <label for="subtotal">Subtotal</label>
                            <input type="hidden" class="form-control subtotal" name="subtotal[${residuoIndex}][]" readonly style="display:none;">
                            <span class="subtotalFormatted" style="font-weight: bold;"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botn para agregar ms tratamientos a este Residuo -->
            <button type="button" class="btn btn-primary add-tratamiento" data-residuo-index="${residuoIndex}" style="margin-top: 10px; margin-bottom: 20px;">
                Agregar Tratamiento
            </button>
        `;

        // Agregar el contenido del residuo al nuevo contenedor
        nuevoContenedor.innerHTML += contenidoResiduo;

        // Agregar el nuevo residuo al contenedor principal
        document.getElementById("residuosContainer").appendChild(nuevoContenedor);

        // Inicializar los botones de agregar tratamiento para el nuevo residuo
        initializeAddTratamientoButtons();
    });

    // Inicializar los botones de agregar tratamiento existentes
    initializeAddTratamientoButtons();
});

// Mostrar la peligrosidad al seleccionar un residuo
function mostrarPeligrosidad(selectElement) {
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    var peligrosidad = selectedOption.getAttribute('data-peligrosidad');
    var contenedor = selectElement.closest('.residuo-form').querySelector('.peligrosidadContainer');
    var peligrosidadTexto = contenedor.querySelector('.peligrosidadTexto');
    var peligrosidadInput = contenedor.querySelector('.peligrosidadInput');

    if (peligrosidad && peligrosidad.trim() !== "") {
        peligrosidadTexto.innerText = peligrosidad;
        peligrosidadInput.value = peligrosidad; // Asignar valor al input oculto
        contenedor.style.display = "block";
    } else {
        peligrosidadTexto.innerText = "Sin peligrosidad";
        peligrosidadInput.value = ""; // Limpiar valor si no hay peligrosidad
        contenedor.style.display = "none";
    }
}

function mostrarClasificacion(selectElement) {
    // Obtenemos la opcin seleccionada
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    // Obtenemos el valor de data-clasificacion
    var clasificacion = selectedOption.getAttribute('data-clasificacion');
    // Obtenemos el input de clasificacion dentro del formulario
    var clasificacionInput = selectElement.closest('.residuo-form').querySelector('.clasificacion');
    
    console.log("Selected Option:", selectedOption);
    console.log("Clasificacin:", clasificacion);
    console.log("Input Element:", clasificacionInput);

    // Verificamos si el input existe y actualizamos su valor
    if (clasificacionInput) {
        if (clasificacion && clasificacion.trim() !== "") {
            clasificacionInput.value = clasificacion; // Asignamos el valor clasificacion al input
        } else {
            clasificacionInput.value = "Sin clasificacin"; // Valor predeterminado si no hay clasificacin
        }
        console.log("Valor asignado al input:", clasificacionInput.value); // Verificamos el valor asignado
    } else {
        console.error("No se encontr el elemento input para clasificacin.");
    }
}

// Funcin para calcular el subtotal basado en cantidad de kilos y precio por kilo
function calcularSubtotal(element) {
    var container = element.closest('.residuo-form');
    var cantidadKilos = container.querySelector('input[name^="cantidad_kilos"]').value;
    var precioKg = container.querySelector('input[name^="precio_kg"]').value;

    if (cantidadKilos && precioKg) {
        var subtotal = parseFloat(cantidadKilos) * parseFloat(precioKg);
        
        // Almacenar el subtotal numerico (valor usado en clculos)
        container.querySelector('input[name^="subtotal"]').value = subtotal.toFixed(2);
        
        // Formatear el subtotal a COP y mostrarlo en el span
        let subtotalFormatted = subtotal.toLocaleString('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        
        // Mostrar el subtotal formateado en el span
        container.querySelector('.subtotalFormatted').textContent = subtotalFormatted;

        // Actualizar el total general
        calcularTotalGeneral();
    }
}

// Funcin para calcular el total general
function calcularTotalGeneral() {
    let subtotales = document.querySelectorAll('input[name^="subtotal"]');
    let totalGeneral = 0;
    
    // Sumar los subtotales
    subtotales.forEach(function(input) {
        totalGeneral += parseFloat(input.value) || 0;
    });
    
    // A09adir el valor del transporte
    let transporte = parseFloat(document.querySelector('input[name="transporte"]').value) || 0;
    totalGeneral += transporte;
    
    // Almacenar el total numerico (valor usado en clculos)
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

function checkResidueStatus() {
    // Obtener todos los selects de residuos
    var residueSelects = document.querySelectorAll('select[name="residuos[]"]');
    var disableAceptado = false;
    var ajaxCalls = []; // Array para almacenar las promesas AJAX

    residueSelects.forEach(function(selectElement){
        var residuoId = selectElement.value;
        if (residuoId) {
            // Hacer una peticin AJAX para obtener el estado del residuo
            var ajaxCall = fetch(`/residuo/status/${residuoId}`)
                .then(response => response.json())
                .then(data => {
                    var status = data.status;
                    if (status && status.trim() !== 'Aceptado') {
                        disableAceptado = true;
                    }
                })
                .catch(error => {
                    console.error('Error al obtener el estado del residuo:', error);
                    disableAceptado = true; // En caso de error, deshabilitar "Aceptado"
                });

            ajaxCalls.push(ajaxCall);
        } else {
            disableAceptado = true; // Si no hay residuo seleccionado, deshabilitar "Aceptado"
        }
    });

    // Esperar a que todas las peticiones AJAX se completen
    Promise.all(ajaxCalls).then(() => {
        // Obtener el select de CoStatus
        var coStatusSelect = document.querySelector('select[name="CoStatus"]');
        var aceptadoOption = coStatusSelect.querySelector('option[value="Aceptado"]');

        if (disableAceptado) {
            aceptadoOption.disabled = true;
            if (coStatusSelect.value === 'Aceptado') {
                coStatusSelect.value = '';
            }
        } else {
            aceptadoOption.disabled = false;
        }
    });
}

function Clientenuevo(){
    document.getElementById("clienteNuevo").style.display = "block";
    document.getElementById("ClienteExistente").style.display = "none";

}
</script>
@endsection
