<?php

//Use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\VehicProgController;
use App\Permisos;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes(['verify' => true]);

Route::get('/noscriptpage', function () {
    return view('noscriptpage');
});

/*Rutas del usuario*/
Route::get('/profile/{id}', 'userController@show')->name('profile');
Route::get('/profile/{id}/edit', 'userController@edit');
Route::put('/profile/{id}','userController@update');
Route::get('/profile/{id}/passwordreset', 'userController@viewchangepassword')->name('profile.changepassword');
Route::patch('/profile/{id}', 'userController@changepassword');

Route::get('/preguntas-frecuentes', function () {
    return view('preguntas.index');
});

// Rutas para fotos de cliente
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/fotos-cliente', 'FotosClienteController@index')->name('fotos-cliente.index');
    Route::get('/fotos-cliente/download/{id}', 'FotosClienteController@download')->name('fotos-cliente.download');
    Route::get('/fotos-cliente/download-all', 'FotosClienteController@downloadAll')->name('fotos-cliente.download-all');
});

Route::post('fotos-cliente/{solserslug}', 'FotosClienteController@store')->name('fotos-cliente.store');
Route::get('/fotos-cliente/{solserslug}/show', 'FotosClienteController@show')->name('fotos-cliente.show');

Route::get('qr-code', function ()
{

	$qrCode = new Endroid\QrCode\QrCode('https://sispro.prosarc.com');

	header('Content-Type: '.$qrCode->getContentType());
	// return $qrCode->writeDataUri();
	echo "<img src='".$qrCode->writeDataUri()."'>";
});

/* REGISTRO EXPRESS - Solo para comerciales autenticadas */
Route::middleware(['web', 'auth'])->group(function () {
	Route::get('/registroexpress', 'registroexpressController@create')->name('registroexpress');
	Route::post('/sendregisterexpress', 'registroexpressController@store');
	Route::get('/pdftest', 'ServiceExpressController@pdftest');
    Route::get('testprefactura', function () {
        $prefacturas = App\Prefactura::with(['cliente', 'comercial', 'servicio.programacionesrecibidas', 'prefacTratamiento.prefacresiduo'])->whereIn('ID_Prefactura', [3, 5, 20])->get();

        return new App\Mail\ServicioFacturado($prefacturas);
    });
	Route::get('/recibotest', 'ServiceExpressController@recibotest');
});



Route::middleware(['web', 'auth', 'verified', 'bindings'])->group(function () {
    //    Route::get('/link1', function ()    {
	//        // Uses Auth Middleware
	//    });
	// Route::get('/', function () {
	// Only verified users may enter...

    //Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
	#adminlte_routes
	Route::post('/changeRol/{id}', 'userController@changeRol');
	Route::resource('/clientes', 'ClientController');
	Route::post('/clientes/{id}/changeComercial', 'ClientController@changeComercial');
	Route::put('/clientes/{slug}/toggle-proveedor', 'ClientController@toggleProveedor')->name('clientes.toggleProveedor');
	Route::get('/clientes/{slug}/create-usuario-generador', 'ClientController@createUsuarioGenerador')->name('clientes.createUsuarioGenerador');
	Route::post('/clientes/{slug}/store-usuario-generador', 'ClientController@storeUsuarioGenerador')->name('clientes.storeUsuarioGenerador');
	Route::get('/cliente/{slug}', 'ClienteController@show')->name('cliente-show');
	Route::get('/cliente/{slug}/edit', 'ClienteController@edit')->name('cliente-edit');
	Route::put('/cliente/{slug}/update', 'ClienteController@update')->name('cliente-update');
	Route::get('/cliente/{slug}/updateCliStatus', 'ClienteController@updateCliStatus')->name('cliente-updateCliStatus');
	Route::get('/cliente/{slug}/negarCliStatus', 'ClienteController@negarCliStatus')->name('cliente-negarCliStatus');
	Route::get('/cliente/{slug}/TipoFacturacionContado', 'ClienteController@facturacionContado')->name('cliente-facturacionContado');
	Route::get('/cliente/{slug}/TipoFacturacionCredito', 'ClienteController@facturacionCredito')->name('cliente-facturacionCredito');
	Route::resource('/clientexpress', 'clientExpressController');
	Route::get('/clientesexpress', 'ClientController@indexExpress')->name('clientes.clientesExpress');
	Route::get('/clientesexpress/{cliente}/edit', 'ClientController@editExpress')->name('clientes.editExpress');
	Route::put('/clientexpress/{cliente}/update', 'ClientController@updateExpress')->name('clientes.updateExpress');
	Route::get('/sedexpress/{cliente}/create', 'sclientcontroller@createSedeExpress')->name('cliente.createSedeExpress');
	Route::post('/sedexpress/{cliente}', 'sclientcontroller@storeSedeExpress')->name('cliente.storeSedeExpress');
	Route::resource('/contactos', 'ContactoController');
	Route::post('/contacto-vehiculo-create/{id}', 'VehiculoContactoController@store');
	Route::put('/contacto-vehiculo-edit/{id}', 'VehiculoContactoController@update');
	Route::delete('/contacto-vehiculo-delete/{id}', 'VehiculoContactoController@destroy');
	Route::resource('/sclientes', 'sclientcontroller');
	Route::get('/sede/{slug}/edit', 'SedesAllController@edit')->name('sedes-edit');
	Route::put('/sedes/{slug}/update', 'SedesAllController@update')->name('sedes-update');
	Route::delete('/sedes/{slug}/destroy', 'SedesAllController@destroy')->name('sedes-destroy');
	Route::get('/generadores/create', 'genercontroller@create');
	//correcta
	Route::post('/generadores/create', 'GenerController@create');
	Route::get('/generadores/createit', 'genercontroller@createit')->name('generadorprueba');

	Route::get('/solicitud-servicio/createit', 'SolicitudServicioController@createit')->name('solicitudprueba');
	Route::get('/solicitud-servicio/create', 'SolicitudServicioController@create')->name('solicitud-servicio.create');
	Route::post('/solicitud-servicio/create', 'SolicitudServicioController@create');
	Route::post('solicitud-servicio/dev-store', 'SolicitudServicioController@store')
  ->name('solicitud-servicio.dev-store')
  ->middleware('auth'); // SIN 'can:' ni 'permission:' ni middlewares propios


	Route::post('/Soy-Gener/{id}', 'genercontroller@storeSoyGenerador');
	Route::resource('/sgeneradores', 'sgenercontroller');
	Route::resource('/respels', 'RespelController');

	//Cliente tarifas routes

	// Route::get('/cliente/{cliente}/tarifascliente_create', 'ClienteTarifasController@create');
	// Route::post('/cliente/{cliente}/tarifascliente_store', 'ClienteTarifasController@store')->name('cliente-tarifas-store');
	Route::resource('/cliente/{slug}/clientetarifas', 'ClienteTarifasController');

	//Proveedor tarifas routes
	Route::get('/proveedor/{slug}/proveedor-tarifas', 'ProveedorTarifasController@create')->name('proveedor-tarifas.index');
	Route::get('/proveedor/{slug}/proveedor-tarifas/create', 'ProveedorTarifasController@create')->name('proveedor-tarifas.create');
	Route::post('/proveedor/{slug}/proveedor-tarifas', 'ProveedorTarifasController@store')->name('proveedor-tarifas.store');
	Route::delete('/proveedor/{slug}/proveedor-tarifas/{ID_PRango}', 'ProveedorTarifasController@destroy')->name('proveedor-tarifas.destroy');
	/*Route::resource('/vencidos', 'RespelController');*/


	Route::get('vencidos', ['as' => 'vencidos', 'uses' => 'RespelController@vencidos']);


	Route::resource('/respelspublic', 'RespelPublicController');
	Route::get('/clientToRp/{id}', 'RespelPublicController@clientToRp');
	Route::get('/rpToClient/{id}', 'RespelPublicController@rpToClient');
	Route::resource('/categorypublic', 'CategoryRPController');
	Route::resource('/subcategorypublic', 'SubCategoryRPController');
	Route::put('/respels/{id}/updateStatusRespel', 'RespelController@updateStatusRespel');
	Route::put('/respels/{id}/makePublicRespel', 'RespelController@makePublicRespel');
	Route::put('/respels/{id}/updateTDE', 'RespelController@updateTDE');
	Route::resource('/generadores', 'genercontroller');
	Route::get('/respels/{id}/editADP', 'RespelController@editADP');
	Route::get('/respelsexpress', 'RespelController@indexExpress')->name('respels.indexExpress');
	/*Rutas para ver los residuos Express por año*/
	Route::get('respelsexpress.2020', ['as' => 'respelsexpress.2020', 'uses' => 'RespelController@respelExpress2020']);
	Route::get('respelsexpress.2021', ['as' => 'respelsexpress.2021', 'uses' => 'RespelController@respelExpress2021']);
	Route::get('respelsexpress.2022', ['as' => 'respelsexpress.2022', 'uses' => 'RespelController@respelExpress2022']);
	Route::get('respelsexpress.2023', ['as' => 'respelsexpress.2023', 'uses' => 'RespelController@respelExpress2023']);
	Route::get('respelsexpress.2024', ['as' => 'respelsexpress.2024', 'uses' => 'RespelController@respelExpress2024']);
	Route::get('respelsexpress.2025', ['as' => 'respelsexpress.2025', 'uses' => 'RespelController@respelExpress2025']);
	Route::get('respelsexpress.2026', ['as' => 'respelsexpress.2026', 'uses' => 'RespelController@respelExpress2026']);
	Route::post('/respelGener', 'RespelSedeGenerController@storeGener');
	Route::delete('/respelGener/{id}', 'RespelSedeGenerController@destroyGener');
	Route::post('/respelSGener', 'RespelSedeGenerController@storeSGener');
	Route::delete('/respelSGener/{id}', 'RespelSedeGenerController@destroySGener');
	Route::resource('/permisos', 'PermisoUsuarioController');
	Route::get('/permisos/{id}/editpassword','PermisoUsuarioController@editpassword')->name('permisos-edit');
	Route::put('/permiso/{id}','PermisoUsuarioController@updatepassword');
	Route::resource('/UsuariosCliente', 'PermisoClienteController');
	Route::get('/UsersClientes', 'PermisoClienteController@usersclientes')->name('users-clientes');
	Route::get('/UsuariosCliente/{id}/editpassword','PermisoClienteController@editpassword')->name('permisos-edit');
	Route::put('/UsuarioCliente/{id}','PermisoClienteController@updatepassword');
	Route::resource('/audits', 'auditController');
	Route::resource('/place/departament', 'DepartamentoController');
	Route::resource('/areas','AreaController');
	Route::resource('/areasInterno','AreaInternoController');
	Route::resource('/place/municipal','municipalityController');
	Route::resource('/cargos','CargoController');
	Route::resource('/cargosInterno','CargoInternoController');
	Route::resource('/personal', 'PersonalController');
	Route::post('/personal/{slug}/asignaradministrador','PersonalController@changeAdminUser');
	Route::resource('/personalInterno', 'PersonalInternoController');
	Route::get('/vehicle/{placa}/elementos-ley/edit', 'VehiculoElementoLeyController@edit')->name('vehicle.elementos-ley.edit');
	Route::put('/vehicle/{placa}/elementos-ley', 'VehiculoElementoLeyController@update')->name('vehicle.elementos-ley.update');
	// Registrar gasolina: formulario con select de vehículo (logística/conductor elige placa sin pasar por index)
	Route::get('/vehicle/combustible/create', 'VehiculoCombustibleController@createStandalone')->name('vehicle.combustible.create.standalone');
	Route::post('/vehicle/combustible', 'VehiculoCombustibleController@storeStandalone')->name('vehicle.combustible.store.standalone');
	Route::get('/vehicle/{placa}/combustible/create', 'VehiculoCombustibleController@create')->name('vehicle.combustible.create');
	Route::post('/vehicle/{placa}/combustible', 'VehiculoCombustibleController@store')->name('vehicle.combustible.store');
	Route::get('/vehicle/{placa}/combustible', 'VehiculoCombustibleController@index')->name('vehicle.combustible.index');
	Route::resource('/vehicle','VehicleController');
	Route::get('/vehicle/export-excel', 'VehicleController@exportToExcel')->name('vehicle.export-excel');
	Route::resource('/programacion-express','ProgramacionExpressController');
	Route::post('/programacion-express/{id}/añadirVehiculo','ProgramacionExpressController@añadirVehiculo');
	Route::put('/programacion-express/{id}/updateStatus','ProgramacionExpressController@updateStatus');
	Route::post('/programacion-express/{id}/sendParafiscales','ProgramacionExpressController@sendParafiscales');
// Importante: rutas específicas deben ir antes del resource para evitar que
	// '/vehicle-programacion/{vehicle_programacion}' capture 'historial-preoperacional' y devuelva 404.
	Route::get('/vehicle-programacion/historial-preoperacional','VehicProgController@historialPreoperacional')
		->name('vehicle-programacion.historial-preoperacional');
	// Botón "Crear preoperacional" siempre habilitado (redirige al pendiente o muestra aviso)
	Route::get('/vehicle-programacion/preoperacional/create','VehicProgController@createPreoperacional')
		->name('vehicle-programacion.preoperacional-create');

	Route::resource('/vehicle-programacion','VehicProgController');
	Route::put('/vehicle-programacion/{id}/updateStatus','VehicProgController@updateStatus');
	Route::post('/vehicle-programacion/{id}/añadirVehiculo','VehicProgController@añadirVehiculo');
	Route::post('/vehicle-programacion/{id}/sendParafiscales','VehicProgController@sendParafiscales');
	Route::get('/vehicle-programacion/{id}/preoperacional','VehicProgController@preoperacional')->name('vehicle-programacion.preoperacional');
	Route::post('/vehicle-programacion/{id}/store-preoperacional','VehicProgController@storePreoperacional')->name('vehicle-programacion.store-preoperacional');
	Route::get('/vehicle-programacion/{id}/download-pdf-preoperacional','VehicProgController@downloadPdfPreoperacional')->name('vehicle-programacion.download-pdf-preoperacional');
	Route::resource('/vehicle-mantenimiento','VehicManteController');
	Route::put('/vehicle-programacion/{id}/updateTransportador','VehicProgController@updateTransportador')->name('vehicle-programacion.updateTransportador');
	Route::resource('/tratamiento','TratamientoController');
	Route::resource('/pretratamiento','PretratamientoController');
	Route::resource('/asistencia', 'AssistancesController');
	Route::resource('/compra/orden','OrdenCompraController');
	// Route::resource('/compra/cotizacion','QuotationController');
	Route::resource('/activos','ActivoController');
	Route::resource('/movimiento-activos','MovimientoActivoController');
	Route::resource('/capacitacion','TrainingsController');
	Route::get('/personalInterno/{slug}/capacitacion/create', 'TrainingPersonalsController@createForPersonal')->name('personalInterno.capacitacion.create');
	Route::get('/capacitacion-personal/{id}/pdf', 'TrainingPersonalsController@downloadPdf')->name('capacitacion-personal.downloadPdf');
	Route::resource('/capacitacion-personal','TrainingPersonalsController');
	Route::resource('/inventariotech', 'InventarioTechonologiesController');
	// Route::resource('/recibo-material', 'ReciboMaterialController');
	// Route::resource('/respel-envios', 'RespelEnviosController');
	Route::resource('/solicitud-residuo', 'SolicitudResiduoController');
	Route::put('/solicitud-residuo/{id}/Update', 'SolicitudResiduoController@updateSolRes');
	Route::put('/solicitud-residuo/{id}/corregirSolRes', 'SolicitudResiduoController@corregirSolRes');
	Route::put('/solicitud-residuo/{id}/corregirSolResExpress', 'SolicitudResiduoController@corregirSolResExpress');
	Route::put('/solicitud-residuo/{id}/UpdatePrice', 'SolicitudResiduoController@updateSolResPrice');
	// Rutas para Express
	Route::put('/serviciosexpress-residuo/{id}/Update', 'SolicitudResiduoController@updateSolRes');
	Route::get('/solicitud-serv/{id}/AñadirRespel', 'SolicitudResiduoController@Respelcliente') ->name('solicitud-serv.AñadirRespel');
	//Rutas para reportes
	//Route::get('/reportes.indextemp', 'SolicitudResiduoController@reportes');
	Route::get('/reportes/regular', ['as'=> 'reportes.regular', 'uses' => 'SolicitudResiduoController@reportesreg']);
	Route::get('/reportes.indextemp',  ['as'=> 'reportes.indextemp', 'uses' =>'SolicitudResiduoController@reportes']);
	Route::get('/reportes.ReporteRegular', ['as'=> 'reportes.ReporteRegular', 'uses' => 'SolicitudResiduoController@reportesreg']);
	Route::get('/reportes.ReporteExpress', ['as'=> 'reportes.ReporteExpress', 'uses' => 'SolicitudResiduoController@reportesexpress']);
	Route::post('/reportes/regular', 'SolicitudResiduoController@reportesreg');
	Route::post('/reportes/express', 'SolicitudResiduoController@reportesExpr');
	Route::get('/reportes.Tiporeporte', ['as'=> 'reportes.Tiporeporte', 'uses' => 'SolicitudResiduoController@tiporeporte']);
	Route::get('/reportes.refechas', ['as'=> 'reportes.refechas', 'uses' => 'SolicitudResiduoController@refechas']);
	Route::get('/reportes.ventasfechas', ['as'=> 'reportes.ventasfechas', 'uses' => 'SolicitudResiduoController@ventasfechas']);
	Route::post('/reportes/registroentrada', 'SolicitudResiduoController@registroentrada');
	Route::post('/reportes/ventas', 'SolicitudResiduoController@ventas');
	// Rutas para reportes de clientes
	Route::get('/reportes/cliente', ['as'=> 'reportes.cliente', 'uses' => 'SolicitudResiduoController@reportesCliente']);
	Route::post('/reportes/cliente/generar', ['as'=> 'reportes.cliente.generar', 'uses' => 'SolicitudResiduoController@reportesClienteGenerar']);
	Route::post('/reportes/cliente/excel', ['as'=> 'reportes.cliente.excel', 'uses' => 'SolicitudResiduoController@exportToExcel']);

	//Rutas para Cotizaciones
	// Rutas específicas de cotización antes del resource para evitar conflictos
	Route::get('/cotizacion/tarifa-proveedor', 'CotizacionController@obtenerTarifaProveedor')->name('cotizacion.tarifa-proveedor');
	Route::post('/cotizacion/createclientext', 'CotizacionController@clienteexistente')->name('cotizacion.createclientext');
	Route::post('/cotizacion/cliente', 'CotizacionController@cliente')->name('cotizacion.cliente');
	Route::get('cotizacion/{id}/pdf', [App\Http\Controllers\CotizacionController::class, 'downloadPDF'])->name('cotizacion.pdf');
	Route::put('cotizacion/{id}/aprobar', 'CotizacionController@aprobar')->name('cotizacion.aprobar');
	Route::post('cotizacion/firma', 'CotizacionController@updateFirma')->name('cotizacion.firma');
	Route::resource('/cotizacion', 'CotizacionController');
	Route::get('cotizacion/{cotizacion}', 'CotizacionController@show')->name('cotizacion.show');
    // Rutas para Cotizaciones Express
	Route::get('/cotizacion-expres', 'CotizacionExpresController@index')->name('cotizacion-expres.index');
	Route::get('/cotizacion-expres/create', 'CotizacionExpresController@create')->name('cotizacion-expres.create');
	Route::post('/cotizacion-expres', 'CotizacionExpresController@store')->name('cotizacion-expres.store');
    Route::get('/cotizacion-expres/excel', 'CotizacionExpresController@exportExcel')->name('cotizacion-expres.excel');
	Route::post('/cotizacion-expres/reporte/enviar','CotizacionExpresController@enviarReporte')->name('cotizacion-expres.enviar-reporte');
	Route::post('/cotizacion-expres/eliminar-lote','CotizacionExpresController@eliminarLote')->name('cotizacion-expres.eliminar-lote');

	// Rutas específicas con {slug} en sub-rutas (ANTES que la ruta genérica {slug})
	Route::get('/cotizacion-expres/{slug}/documentos', 'CotizacionExpresController@documentos')->name('cotizacion-expres.documentos');
	Route::get('/cotizacion-expres/{slug}/historial-documentos', 'CotizacionExpresController@historialDocumentos')->name('cotizacion-expres.historial-documentos');
	Route::post('/cotizacion-expres/{slug}/cargar-documento', 'CotizacionExpresController@cargarDocumento')->name('cotizacion-expres.cargar-documento');
	Route::get('/cotizacion-expres/{slug}/edit', 'CotizacionExpresController@edit')->name('cotizacion-expres.edit');

	// Sedes de un cliente express (CRUD vía AJAX desde la vista de detalle)
	Route::get('/cotizacion-expres/{slug}/sedes', 'CotizacionExpresController@sedes')->name('cotizacion-expres.sedes.index');
	Route::post('/cotizacion-expres/{slug}/sedes', 'CotizacionExpresController@storeSede')->name('cotizacion-expres.sedes.store');
	Route::post('/cotizacion-expres/{slug}/sedes/{idSede}', 'CotizacionExpresController@updateSede')->name('cotizacion-expres.sedes.update');
	Route::delete('/cotizacion-expres/{slug}/sedes/{idSede}', 'CotizacionExpresController@destroySede')->name('cotizacion-expres.sedes.destroy');

	// Alias con guión "express" (compatibilidad con enlaces antiguos / Wati)
	Route::get('/cotizacion-express/{slug}', function ($slug) {
		return redirect()->route('cotizacion-expres.show', $slug);
	});

	// Ruta genérica {slug} (DESPUÉS de las rutas específicas)
	Route::get('/cotizacion-expres/{slug}', 'CotizacionExpresController@show')->name('cotizacion-expres.show');
	Route::post('/cotizacion-expres/{slug}', 'CotizacionExpresController@update')->name('cotizacion-expres.update');
	Route::delete('/cotizacion-expres/{slug}', 'CotizacionExpresController@destroy')->name('cotizacion-expres.destroy');


	//Rutas para Inventario
	Route::get('/inventario', 'SolicitudServicioController@inventario')->name('inventario');
	Route::post('inventario/almacenamientogeneral', 'SolicitudServicioController@almacenamientogeneral')->name('almacenamiento');
	Route::get('/jaulas', 'SolicitudServicioController@jaulas')->name('jaulas');
	Route::get('/jaulas/Asignar', 'SolicitudServicioController@asignar')->name('asignar');
	Route::get('/jaulas/tratamiento/{id}', 'SolicitudServicioController@tratamiento')->name('tratamiento');
	Route::get('/jaulas/MostrarJaula/{id}', 'SolicitudServicioController@MostrarJaula')->name('MostrarJaula');
	Route::get('/jaulas/disponibles/{id}/{solicitud}', 'SolicitudServicioController@jaulasdisponibles')->name('jaulasdisponibles');
	Route::post('/jaulas/disponibles/asignarJaulas', 'SolicitudServicioController@asignarJaulas')->name('asignar.jaulas');
	Route::get('/termodestruccion', 'SolicitudServicioController@termodestruccion')->name('termo');
	Route::post('/termodestruccion/programar', 'SolicitudServicioController@progincineracion')->name('progincineracion');
	Route::get('/termodestruccion/programar/editar/{id}', 'SolicitudServicioController@editarprogamacion')->name('editarprogramacion');
	Route::post('/termodestruccion/update/{id}', 'SolicitudServicioController@updateprogincineracion')->name('updateprogincineracion');
	Route::get('/termodestruccion/programacion', 'SolicitudServicioController@programacion')->name('programacion');
	Route::get('/termodestruccion/informe', 'SolicitudServicioController@informe')->name('informe');
	Route::post('/termodestruccion/informe/{id}', 'SolicitudServicioController@informepdf')->name('informepdf');
	Route::get('/termodestruccion/incineracion', 'SolicitudServicioController@incineracion')->name('incineracion');

	//Route::post('/solicitud-servicio/{id}/NumFactura', [SolicitudServicioController::class, 'NumFactura'])->name('NumFactura.dato');
	Route::put('/solicitud-servicio/{id}/NumFactura', 'SolicitudServicioController@NumFactura');
	Route::get('/reportes.ReporteDatos', ['as'=> 'reportes.ReporteDatos', 'uses' => 'SolicitudResiduoController@reportesRegularesDatos']);
	Route::resource('/solicitud-servicio', 'SolicitudServicioController');
	Route::post('/solicitud-servicio/changestatus', 'SolicitudServicioController@changestatus');
	Route::post('/solicitud-servicio/reversarStatus', 'SolicitudServicioController@reversarStatus');
	Route::post('/solicitud-servicio/cancelarServicio', 'SolicitudServicioController@cancelarServicio');
	Route::put('/solicitud-servicio/{id}/updateRms', 'SolicitudServicioController@updateRms');
	Route::get('/solicitud-servicio/{id}/sendtobilling', 'SolicitudServicioController@sendtobilling');
	Route::get('/solicitud-servicio/{id}/add-respel', 'SolicitudServicioController@addRespel');
	Route::put('/solicitud-servicio/{id}/update-respel', 'SolicitudServicioController@updateRespel');
	Route::put('/solicitud-servicio/repeat/{id}', 'SolicitudServicioController@repeat');
	Route::get('/solicitud-servicio/{id}/documentos', 'SolicitudServicioController@solservdocindex')->name('solicitud-servicio.documentos');
	Route::get('/solicitud-servicio/{id}/recibomaterial', 'SolicitudServicioController@recibomaterial')->name('recibo.material');

	// Añadir (RM Express unificado en ServiceExpressController)
	Route::get('/serviciosexpress/{id}/recibomaterial', 'ServiceExpressController@recibomaterialExpress')->name('serviceexpress.recibomaterial');
	Route::post('/serviciosexpress/{id}/conciliar', 'ServiceExpressController@conciliarMaterialExpress')->name('serviceexpress.conciliar');
	Route::get('/serviciosexpress/{id}/rmpdf', 'ServiceExpressController@generarPDFExpress')->name('serviceexpress.rmpdf');
	Route::post('/solicitud-servicio/{id}/firmacliente', 'SolicitudServicioController@firmacliente');
	Route::post('/solicitud-servicio/{id}/firmaconductor', 'SolicitudServicioController@firmaconductor');
	Route::post('/solicitud-servicio/{id}/firmapda', 'SolicitudServicioController@firmapda');
	Route::get('/solicitud-servicio/{id}/{slug}/wordtemplate', 'SolicitudServicioController@rmtemplate')->name('recibomaterial');
	Route::get('/solicitud-servicio/{slug}/duplicarpesos', 'SolicitudServicioController@duplicarpesos')->name('duplicarpesos');
	Route::post('/solicitud-servicio/{id}/NuevoRespel', 'SolicitudServicioController@NuevoRespel');
    Route::put('/solicitud-servicio/updatePrecinto/{id}', 'SolicitudServicioController@updatePrecinto')->name('vehicle-programacion.updatePrecinto');

	/*Rutas para ver las solicitudes por año*/
	Route::get('solicitud-serv.2020', ['as' => 'solicitud-serv.2020', 'uses' => 'SolicitudServicioController@soli2020']);
	Route::get('solicitud-serv.2021', ['as' => 'solicitud-serv.2021', 'uses' => 'SolicitudServicioController@soli2021']);
	Route::get('solicitud-serv.2022', ['as' => 'solicitud-serv.2022', 'uses' => 'SolicitudServicioController@soli2022']);
	Route::get('solicitud-serv.2023', ['as' => 'solicitud-serv.2023', 'uses' => 'SolicitudServicioController@soli2023']);
	Route::get('solicitud-serv.2024', ['as' => 'solicitud-serv.2024', 'uses' => 'SolicitudServicioController@soli2024']);
	Route::get('solicitud-serv.2025', ['as' => 'solicitud-serv.2025', 'uses' => 'SolicitudServicioController@soli2025']);
	Route::get('solicitud-serv.2026', ['as' => 'solicitud-serv.2026', 'uses' => 'SolicitudServicioController@soli2026']);

	/*Rutas para ver los servicios Express por año*/
	Route::get('serviciosexpress.2020', ['as' => 'serviciosexpress.2020', 'uses' => 'ServiceExpressController@soli2020']);
	Route::get('serviciosexpress.2021', ['as' => 'serviciosexpress.2021', 'uses' => 'ServiceExpressController@soli2021']);
	Route::get('serviciosexpress.2022', ['as' => 'serviciosexpress.2022', 'uses' => 'ServiceExpressController@soli2022']);
	Route::get('serviciosexpress.2023', ['as' => 'serviciosexpress.2023', 'uses' => 'ServiceExpressController@soli2023']);
	Route::get('serviciosexpress.2024', ['as' => 'serviciosexpress.2024', 'uses' => 'ServiceExpressController@soli2024']);
	Route::get('serviciosexpress.2025', ['as' => 'serviciosexpress.2025', 'uses' => 'ServiceExpressController@soli2025']);
	Route::get('serviciosexpress.2026', ['as' => 'serviciosexpress.2026', 'uses' => 'ServiceExpressController@soli2026']);

	Route::resource('/serviciosexpress', 'ServiceExpressController');
	Route::post('/serviciosexpress/changestatus', 'ServiceExpressController@changestatus');
	Route::post('/serviciosexpress/reversarStatus', 'ServiceExpressController@reversarStatus');
	Route::post('/serviciosexpress/cancelarServicio', 'ServiceExpressController@cancelarServicio');
	Route::put('/serviciosexpress/{id}/updateRms', 'ServiceExpressController@updateRms');
	Route::get('/rutadeldia', 'ServiceExpressController@rutadeldia');
	Route::get('/generar-firmas-express', 'ServiceExpressController@generarFirmasExpress')->name('generar-firmas-express');
	Route::get('/serviciosexpress/{id}/sendtobilling', 'ServiceExpressController@sendtobilling');
	Route::get('/serviciosexpress/{id}/add-respel', 'ServiceExpressController@addRespel');
	Route::put('/serviciosexpress/{id}/update-respel', 'ServiceExpressController@updateRespel');
	Route::post('/serviciosexpress/{id}/firmacliente', 'ServiceExpressController@firmacliente');
	Route::post('/serviciosexpress/{id}/firmaconductor', 'ServiceExpressController@firmaconductor');
	Route::post('/serviciosexpress/{id}/firmapda', 'ServiceExpressController@firmapda');
	Route::get('/serviciosexpress/{id}/{slug}/wordtemplate', 'ServiceExpressController@rmtemplate')->name('serviceexpress.wordtemplate');
	Route::put('/serviciosexpress/repeat/{id}', 'ServiceExpressController@repeat');
	Route::post('/serviciosexpress/certificarExpress', 'ServiceExpressController@certificarExpress');
	Route::post('/serviciosexpress/conciliarExpress', 'ServiceExpressController@conciliarExpress');
	Route::get('/serviciosexpress/{id}/documentos', 'ServiceExpressController@solservdocindex')->name('solicitud-servicio.documentos');
	Route::get('/ResiduosComunes', 'ServiceExpressController@getResiduosComunes');
	Route::resource('/observacion', 'ObservacionController');
	Route::resource('/recibosdepago', 'ReciboDePagoController');
	Route::post('/recepcionerrada', 'ObservacionController@recepcionErrada');
	Route::post('/recordatorio', 'ObservacionController@sendRecordatorio');
	Route::get('/serviciosRecepcionado', 'SolicitudServicioController@serviciosRecepcionado');
	Route::get('/almacenamiento', 'SolicitudServicioController@indexalmacenados')->name('almacenamiento');
	Route::get('/solicitud-servicio/{id}/documentos/create', 'CertificadoController@create');
	// Rutas específicas ANTES del resource para que se resuelvan correctamente
	Route::post('/certificadosexpress/{id}/updateFile', 'CertificadoExpressController@updateFile')->name('certificadosexpress.updateFile');
	Route::get('/certificadosexpress/{id}/approveFile', 'CertificadoExpressController@approveFile')->name('certificadosexpress.approveFile');
	Route::post('/certificados/{id}/updateFile', 'CertificadoController@updateFile')->name('certificados.updateFile');
	Route::get('/certificados/{id}/approveFile', 'CertificadoController@approveFile')->name('certificados.approveFile');
	Route::resource('/certificadosexpress', 'CertificadoExpressController');
	Route::get('/certificadosexpress/{id}/firmar/{servicio}', 'CertificadoController@firmar');
	Route::get('/certificadosexpress/{id}/firmar', 'CertificadoController@firmarindex');
	Route::get('/certificadosexpress/{id}/wordtemplate', 'CertificadoController@wordtemplate');
	Route::post('/certificadosexpress/{id}/independiente', 'CertificadoController@independiente');
	Route::resource('/certificados', 'CertificadoController');
	Route::get('/certificados/{id}/firmar/{servicio}', 'CertificadoController@firmar');
	Route::get('/certificados/{id}/firmar', 'CertificadoController@firmarindex');
	Route::get('/certificados/{id}/wordtemplate', 'CertificadoController@wordtemplate');
	Route::post('/certificados/{id}/independiente', 'CertificadoController@independiente');
    // Recibos de material: selector por año + listado por año (debe ir antes del resource)
    Route::get('/recibomaterial/year/{year}', 'RecibomaterialController@indexYear')->name('recibomaterial.year');
    Route::resource('/recibomaterial', 'RecibomaterialController');
    Route::get('/recibomaterial/{slugFirmas}/file', 'RecibomaterialController@file')->name('recibomaterial.file');
    Route::post('/recibomaterial/{slugFirmas}/updateFile', 'RecibomaterialController@updateFile')->name('recibomaterial.updateFile');
    Route::get('/recibomaterial/{slugFirmas}/approveFile', 'RecibomaterialController@approveFile')->name('recibomaterial.approveFile');
    // Índice de Recibos de Material Express (controlador prepara $rms)
    Route::get('/recibomaterialexpress', 'RecibomaterialController@indexExpress')->name('recibomaterialexpress.index');
	Route::resource('/verificationcodes', 'VerificationCodeController');
	Route::resource('/groupcodes', 'GroupCodeController');
	Route::resource('/verifycodes', 'VerificationCodeController');
	Route::post('/manifiestos/{id}/updateFile', 'ManifiestoController@updateFile')->name('manifiestos.updateFile');
	Route::resource('/manifiestos', 'ManifiestoController');
	Route::get('/manifiestos/{id}/firmar/{servicio}', 'ManifiestoController@firmar');
	Route::get('/manifiestos/{id}/firmar', 'ManifiestoController@firmarindex');
	Route::resource('/articulos-proveedor', 'ArticuloXProveedorController');
	Route::resource('/code', 'QrCodesController');
	Route::resource('/horario', 'HorarioController');
	// Route::resource('/asistencias', 'AsistenciaController');
	Route::resource('/recurso', 'RecursoController');
	Route::resource('/requerimientos', 'RequerimientoController');
	Route::put('/requerimientos/{id}/updateTrat/{servicio}/{ID_SolSer}', 'RequerimientoController@updateTrat')->name('requerimientos.updateTrat');
	Route::resource('/holidays', 'holidayController');
	Route::resource('/cotizacion', 'CotizacionController');
	Route::resource('/tarifas', 'TarifaController');
	Route::get('/home', 'HomeController@index')->name('home');
	Route::get('/home/eventos-calendario', 'HomeController@obtenerEventosCalendario')->name('home.eventos-calendario');
	Route::get('/logout', 'Auth\LoginController@logout');
	Route::get('/sclientes/{id}', 'sclientcontroller@getMunicipio');
	Route::get('/ClasificacionA', function(){return view('layouts.RespelPartials.ClasificacionA');})->name('ClasificacionA');
	Route::get('/ClasificacionY', function(){return view('layouts.RespelPartials.ClasificacionY');})->name('ClasificacionY');
	Route::resource('/contratos', 'ContratoController');
	Route::resource('/requeri-client', 'RequerimientosClienteController');
	/*Rutas de peticiones de Ajax*/
	// Route::get('/muni-depart/{id}', 'AjaxController@MuniDepart');
	Route::get('/doc-number/{id}', 'AjaxController@DocNumber');
	Route::get('/area-sede/{id}', 'AjaxController@AreasSedes');
	Route::get('/cargo-area/{id}', 'AjaxController@CargosAreas');
	Route::put('/CambioDeFechaProgVehic/{id}', 'AjaxController@CambioDeFecha');
	Route::get('/RespelGener/{id}', 'AjaxController@RespelGener');
	Route::get('/sedegener-respel/{id}', 'AjaxController@SGenerRespel');
	Route::get('/contacto-vehiculos/{id}', 'AjaxController@VehiculosContacto');
	Route::get('/RequeRespel/{id}', 'AjaxController@RequeRespel');
	// Ruta para mostrar el formulario de filtros de logística
	Route::get('/reportes/ReportLogistica', [App\Http\Controllers\SolicitudResiduoController::class, 'showReportLogistica'])
	->name('reportes.ReportLogistica')
	->middleware('auth');
	// Ruta para reporte de logística
	Route::post('/reportes/ReporteLogistica', [App\Http\Controllers\SolicitudResiduoController::class, 'reportesLogistica'])
	->name('reportes.ReporteLogistica')
	->middleware('auth');
	Route::get('/SustanciaControlada/{id}', 'AjaxController@SustanciaControlada');
	Route::get('/vehicle-transport/{id}', 'AjaxController@VehicTransport');
	Route::get('/preTratamientoDinamico/{id}', 'AjaxController@preTratamientoDinamico');
	Route::get('/SubcategoriaDinamico/{id}', 'AjaxController@SubcategoriaDinamico');
	Route::get('/verificarduplicado/{numero}/{type}', 'AjaxController@verificarDuplicado');
	Route::get('/certificarservicio/{servicio}', 'AjaxController@certificarServicio');
	Route::post('/facturarservicio/{servicio}', 'AjaxController@facturarServicio');
	Route::post('/recordatorioAjax', 'AjaxController@sendRecordatorio');
	Route::get('/renewtokenaftererror', 'AjaxController@renewTokenAfterError');
	Route::put('/firmarCertificado/{slug}', 'AjaxController@firmarCertificado')->name('certificados.ajaxfirmar');
	Route::get('/ClienteExpress-Residuos/{id}', 'AjaxController@clienteExpressResiduos');
	Route::resource('/prefacturas', 'PrefacturaController');

	/*Rutas paa ver los certificados por año*/
	//Route::get('/certificados/cert2020', 'CertificadoController@cert2020')->name('certificados.2020');
	Route::get('certificados.2020', ['as' => 'certificados.2020', 'uses' => 'CertificadoController@cert2020']);
	Route::get('certificados.2021', ['as' => 'certificados.2021', 'uses' => 'CertificadoController@cert2021']);
	Route::get('certificados.2022', ['as' => 'certificados.2022', 'uses' => 'CertificadoController@cert2022']);
	Route::get('certificados.2023', ['as' => 'certificados.2023', 'uses' => 'CertificadoController@cert2023']);
	Route::get('certificados.2024', ['as' => 'certificados.2024', 'uses' => 'CertificadoController@cert2024']);
	Route::get('certificados.2025', ['as' => 'certificados.2025', 'uses' => 'CertificadoController@cert2025']);
	Route::get('certificados.2026', ['as' => 'certificados.2026', 'uses' => 'CertificadoController@cert2026']);

	Route::get('certificadosExpress.ano', ['as' => 'certificadosExpress.ano', 'uses' => 'CertificadoExpressController@index']);
	Route::get('certificadosExpress.2023', ['as' => 'certificadosExpress.2023', 'uses' => 'CertificadoExpressController@certex2023']);
	Route::get('certificadosExpress.2024', ['as' => 'certificadosExpress.2024', 'uses' => 'CertificadoExpressController@certex2024']);
	Route::get('certificadosExpress.2025', ['as' => 'certificadosExpress.2025', 'uses' => 'CertificadoExpressController@certex2025']);
	Route::get('certificadosExpress.2026', ['as' => 'certificadosExpress.2026', 'uses' => 'CertificadoExpressController@certex2026']);

	Route::get('/solicitud-serv.Createrespel', 'RespelController@createrespelcliente') ->name('solicitud-serv.Createrespel');
	Route::post('/respel', 'RespelController@storenewrespel')->name('respel');

	/*Rutas de generacion de PDF*/
	Route::get('/PdfManiCarg/{id}','PdfController@PdfManiCarg');
	/*Rutas de envio de e-mail */
	Route::get('/email-solser/{slug}', 'EmailController@sendemail')->name('email-solser');
	Route::get('/email-respel/{slug}', 'EmailController@sendEmailRespel')->name('email-respel');
	// Rutas para Calificaciones y Comentarios
	// IMPORTANTE: Las rutas específicas deben ir ANTES de Route::resource para evitar conflictos
	Route::get('/calificaciones/pendientes', 'CalificacionController@pendientesCliente')->name('calificaciones.pendientes');
	Route::get('/calificaciones/create/{hash}', 'CalificacionController@create')->name('calificaciones.create');
	Route::post('/calificaciones/{id}/responder', 'CalificacionController@responder')->name('calificaciones.responder');
	Route::get('/Calificaciones.index', ['as'=> 'Calificaciones.index', 'uses' => 'CalificacionController@verCalificaciones']);
	// Excluir 'create' del resource porque tenemos una ruta personalizada con hash
	Route::resource('/calificaciones', 'CalificacionController')->except(['create']);
});

Route::get('/muni-depart/{id}', 'AjaxController@MuniDepart');

Route::get('/vehicle-transport/{slug}', [VehicProgController::class, 'getVehiculosTransportador']);

// Rutas para importación de residuos
Route::get('respel/import', 'RespelImportController@index')->name('respel.import.index');
Route::post('respel/import', 'RespelImportController@import')->name('respel.import');

/*
|--------------------------------------------------------------------------
| Rutas del CRM
|--------------------------------------------------------------------------
*/

// Rutas del CRM - Nuevo sistema (en desarrollo)
Route::group(['prefix' => 'crm', 'as' => 'crm.', 'middleware' => ['auth']], function () {
    // Dashboard
    Route::get('/', 'CrmDashboardController@index')->name('dashboard');
    Route::get('/recordatorios', 'CrmDashboardController@obtenerRecordatorios')->name('recordatorios');
    // Vista de Gerencia Comercial
    Route::get('/gerencia', 'CrmDashboardController@gerencia')->name('gerencia');
    Route::get('/gerencia/clientes-nuevos-mes', 'CrmDashboardController@clientesNuevosMes')->name('gerencia.clientes-nuevos-mes');

    // Clientes nuevos del comercial
    Route::get('/mis-clientes-nuevos', 'CrmDashboardController@misClientesNuevos')->name('mis-clientes-nuevos');

    // Clientes
     Route::post('/clientes/{slug}/toggle-activo', 'CrmClienteController@toggleActivo')->name('clientes.toggle-activo');
    Route::get('/clientes', 'CrmClienteController@index')->name('clientes.index');
    Route::get('/clientes/{slug}', 'CrmClienteController@show')->name('clientes.show');

    // Actividades
    Route::get('/actividades', 'CrmActividadController@index')->name('actividades.index');
    Route::get('/actividades/create', 'CrmActividadController@create')->name('actividades.create');
    Route::post('/actividades', 'CrmActividadController@store')->name('actividades.store');
    Route::get('/actividades/{id}/edit', 'CrmActividadController@edit')->name('actividades.edit');
    Route::put('/actividades/{id}', 'CrmActividadController@update')->name('actividades.update');
    Route::post('/actividades/{id}/estado', 'CrmActividadController@updateEstado')->name('actividades.updateEstado');

    // Oportunidades
    Route::get('/oportunidades', 'CrmOportunidadController@index')->name('oportunidades.index');
    Route::get('/oportunidades/create', 'CrmOportunidadController@create')->name('oportunidades.create');
    Route::get('/oportunidades/{id}/detalle', 'CrmOportunidadController@detalle')->name('oportunidades.detalle');
    Route::get('/oportunidades/{id}/edit', 'CrmOportunidadController@edit')->name('oportunidades.edit');
    Route::post('/oportunidades', 'CrmOportunidadController@store')->name('oportunidades.store');
    Route::put('/oportunidades/{id}', 'CrmOportunidadController@update')->name('oportunidades.update');
    Route::post('/oportunidades/{id}/etapa', 'CrmOportunidadController@updateEtapa')->name('oportunidades.updateEtapa');
    Route::post('/oportunidades/{id}/cerrar', 'CrmOportunidadController@cerrar')->name('oportunidades.cerrar');
    // Las rutas del nuevo CRM se agregarán aquí
});
