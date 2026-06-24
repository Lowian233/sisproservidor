<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\CotizacionStoreRequest;
use App\Http\Requests\CotizacionUpdateRequest;
use App\Permisos;
use App\Cotizacion;
use App\CotiRespel;
use App\Respel;
use App\Tratamiento;
use App\ProveedorTarifa;
use App\ProveedorRango;
use App\Mail\CotizacionCreada;
use App\Mail\CotizacionAprobada;
use App\User;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Log;
//use PDF;
use Illuminate\Support\Facades\Storage;
            
            class CotizacionController extends Controller
            {
                // Mostrar la lista de cotizaciones y el formulario
                /**
                 * Display a listing of the resource.
                 *
                 * @return \Illuminate\Http\Response
                 */
                public function index()
            {
                // Verificar que el usuario tenga los roles permitidos
                    if (in_array(Auth::user()->UsRol, Permisos::COTIZACION) || 
                    in_array(Auth::user()->UsRol, ['Comercial', 'AdministradorBogota']))
                    {
                     // Iniciar la consulta base
                    $query = Cotizacion::with('coti_respel.respel', 'coti_respel.tratamiento');

                    // Filtrar cotizaciones según el rol
                    if (Auth::user()->UsRol === 'Comercial') {
                        // Comerciales solo ven las cotizaciones donde son el usuario logueado
                        $query->where('Auditlog', Auth::user()->email);
                    }
                    // Coordinador Comercial y Programador ven todas las cotizaciones
                    
                    // Ejecutar la consulta 
                    $cotizaciones = $query->get();

                    // Obtener todos los residuos y tratamientos para el formulario
                    $residuos = Respel::select('ID_Respel', 'RespelName', 'RespelIgrosidad', 'RespelPublic', 'YRespelClasf4741', 'ARespelClasf4741')->get();
                    $tratamientos = Tratamiento::select('ID_Trat', 'TratName')->get();

                    // Retornar la vista con los datos necesarios
                    return view('cotizacion.index', compact('cotizaciones', 'residuos', 'tratamientos'));
                }

                // Si el usuario no tiene el rol adecuado, redirigir
                return redirect()->route('home')->with('error', 'No tienes permiso para acceder a esta sección.');
            }
                       
                // Mostrar el formulario de creación de cotización
                /**
                 * Display a listing of the resource.
                 *
                 * @return \Illuminate\Http\Response
                 */
                public function create(Request $request)
                {
                    $residuoscomunes = Respel::Where('RespelPublic', '1')
                                ->select('ID_Respel','RespelName', 'YRespelClasf4741','ARespelClasf4741', 'RespelIgrosidad')
                                ->get()
                                ->map(function ($residuoscomunes) {
                                    // Llama a obtenerClasificacion para cada residuo y almacena el resultado en el atributo clasificacion
                                    $residuoscomunes->clasificacion = $this->obtenerClasificacion($residuoscomunes);
                                    return $residuoscomunes;
                                });
                    // Cargar tratamientos con sus gestores (sedes) y clientes asociados
                    // Incluir solo tratamientos que tienen gestor asociado y que el gestor tenga cliente
                    $tratamientos = Tratamiento::with(['gestor.clientes'])
                        ->where('TratDelete', 0)
                        ->whereHas('gestor', function($query) {
                            $query->whereNotNull('FK_SedeCli');
                        })
                        ->get();

                    $clientes = DB::table('clientes')
                        ->select('clientes.ID_Cli', 'clientes.CliName','clientes.CliNit')
                        ->where('CliCategoria', 'Cliente')
                        ->get();
                    
                    return view('cotizacion.create', compact('residuoscomunes', 'tratamientos', 'clientes'));
                }

                // Mostrar el formulario de creación de cotización
                /**
                 * Display a listing of the resource.
                 *
                 * @return \Illuminate\Http\Response
                 */
                public function clienteexistente(Request $request)
                {
                    $clienteId = intval($request->input('Cliente'));

                    $residuoscomunes = Respel::Where('RespelPublic', '1')
                                ->select('ID_Respel','RespelName', 'YRespelClasf4741','ARespelClasf4741', 'RespelIgrosidad')
                                ->get()
                                ->map(function ($residuoscomunes) {
                                    // Llama a obtenerClasificacion para cada residuo y almacena el resultado en el atributo clasificacion
                                    $residuoscomunes->clasificacion = $this->obtenerClasificacion($residuoscomunes);
                                    return $residuoscomunes;
                                });
                    // Cargar tratamientos con sus gestores (sedes) y clientes asociados
                    // Incluir solo tratamientos que tienen gestor asociado y que el gestor tenga cliente
                    $tratamientos = Tratamiento::with(['gestor.clientes'])
                        ->where('TratDelete', 0)
                        ->whereHas('gestor', function($query) {
                            $query->whereNotNull('FK_SedeCli');
                        })
                        ->get();

                    $Respels = DB::table('respels')
                        ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
                        ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                        ->select('respels.ID_Respel', 'respels.RespelName', 'respels.RespelIgrosidad', 'respels.YRespelClasf4741', 'respels.ARespelClasf4741', 'respels.RespelStatus', 'respels.RespelPublic', 'respels.RespelHojaSeguridad', 'respels.RespelTarj', 'clientes.CliName', 'clientes.CliNit','clientes.ID_Cli', 'sedes.SedeAddress', 'sedes.SedePhone1', 'sedes.SedeEmail')
                        ->where('clientes.ID_Cli', $clienteId)
                        ->get()
                        ->map(function ($Respels) {
                            // Llama a obtenerClasificacion para cada residuo y almacena el resultado en el atributo clasificacion
                            $Respels->clasificacion = $this->obtenerClasificacion($Respels);
                            return $Respels;
                        });

                        
                    $clientes = DB::table('clientes')
                        ->join('sedes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                        ->select('clientes.ID_Cli', 'clientes.CliName','clientes.CliNit', 'sedes.*')
                        ->where('clientes.ID_Cli', $clienteId)
                        ->first();    

                    //return $clientes;

                    return view('cotizacion.createclientexistente', compact('residuoscomunes', 'tratamientos', 'Respels', 'clientes'));
                }

                public function cliente(Request $request)
                 {

                   // return $request->all();
                     // Depuración para mostrar todos los datos recibidos
                     // dd($request->all());
                     //$validatedData = $request->validated();
                     
                    //DB::beginTransaction();
                    //try {
                      //  $validatedData = $request->validated();

                        // Crear la cotización principal
                        $cotizacion = Cotizacion::create([
                            'FechaCotizacion'         => now(),
                            'Nit'                     => $request->input('Nit'),
                            'Razon_Social'            => $request->input('Razon_Social'),
                            'Telefono'                => $request->input('Telefono'),
                            'Correo'                  => $request->input('Correo'),
                            'Direccion'               => $request->input('Direccion'),
                            'CoStatus'                => $request->input('CoStatus'),
                            'Total'                   => $request->input('Total'),
                            'transporte'              => $request->input('transporte'),
                            'sede'                    => $request->input('sede'),
                            'frecuencia_recoleccion'  => $request->input('frecuencia_recoleccion'),
                            'tipo_cotizacion'         => $request->input('tipo_cotizacion'),
                            'Observaciones'           => $request->input('Observaciones'),
                            'Auditlog'=> Auth::user()->email,
                            'status'                  => $request->input('status'), // Estado de aprobación 
                        ]);

                        // Ajustar campos extra si es necesario
                        $cotizacion->Auditlog      = Auth::user()->email;
                        $cotizacion->Observaciones = $request->input('Observaciones');
                        $cotizacion->save();

                        // Arrays enviados desde el formulario
                        $residuos      = $request->input('residuos');
                        $clasf4741     = $request->input('clasf4741');
                        $peligrosidad  = $request->input('peligrosidad');
                        $estadoFisico  = $request->input('estado_fisico');
                        $tratamientos  = $request->input('tratamientos');
                        $cantidadKilos = $request->input('cantidad_kilos');
                        $precioKg      = $request->input('precio_kg');
                        $precioProveedorKg = $request->input('precio_proveedor_kg');
                        $porcentajeGanancia = $request->input('porcentaje_ganancia');
                        $subtotal      = $request->input('subtotal');

                        // Verificar la longitud de los arreglos principales
                        $cantidadItems = count($residuos);
                        if (
                            count($clasf4741)    !== $cantidadItems ||
                            count($peligrosidad) !== $cantidadItems ||
                            count($estadoFisico) !== $cantidadItems ||
                            count($tratamientos) !== $cantidadItems
                        ) {
                            throw new \Exception('La cantidad de elementos de residuos no coincide con sus campos.');
                        }

                        // Verificar cada residuo y sus tratamientos dentro del mismo bucle
                        for ($i = 0; $i < $cantidadItems; $i++) {
                            // Asegurarse de que tratamientos y kilos sean arreglos reales
                            if (!is_array($tratamientos[$i])) {
                                throw new \Exception("El elemento en 'tratamientos[$i]' no es un array.");
                            }
                            if (!is_array($cantidadKilos[$i])) {
                                throw new \Exception("El elemento en 'cantidadKilos[$i]' no es un array.");
                            }
                            if (!is_array($precioKg[$i])) {
                                throw new \Exception("El elemento en 'precioKg[$i]' no es un array.");
                            }
                            if (!is_array($subtotal[$i])) {
                                throw new \Exception("El elemento en 'subtotal[$i]' no es un array.");
                            }

                            // Comparar porcentajes de longitud en los subarreglos
                            if (
                                count($tratamientos[$i]) !== count($cantidadKilos[$i]) ||
                                count($tratamientos[$i]) !== count($precioKg[$i])      ||
                                count($tratamientos[$i]) !== count($subtotal[$i])
                            ) {
                                throw new \Exception('La cantidad de tratamientos no coincide con kilos o precios.');
                            }

                            // Guardar registros para cada tratamiento
                            foreach ($tratamientos[$i] as $tIndex => $tratamientoId) {
                                $cotizacion->coti_respel()->create([
                                    'FK_ID_Respel'   => $residuos[$i],
                                    'FK_Tratamiento' => $tratamientoId,
                                    'cantidad_kilos' => $cantidadKilos[$i][$tIndex],
                                    'precio_kg'      => $precioKg[$i][$tIndex],
                                    'precio_proveedor_kg' => isset($precioProveedorKg[$i][$tIndex]) ? $precioProveedorKg[$i][$tIndex] : null,
                                    'porcentaje_ganancia' => isset($porcentajeGanancia[$i][$tIndex]) ? $porcentajeGanancia[$i][$tIndex] : null,
                                    'subtotal'       => $subtotal[$i][$tIndex],
                                    'peligrosidad'   => $peligrosidad[$i],
                                    'clasf4741'      => $clasf4741[$i],
                                    'estado_fisico'  => $estadoFisico[$i],
                                ]);
                            }
                        }
                        $destinatarios = ['coordinadorcomercial@prosarc.com.co'];
                        // Enviar correo de creación
                        /* $this->enviarCorreoCotizacionCreada($cotizacion); */
                        Mail::to($destinatarios)->send(new CotizacionCreada($cotizacion));

                        DB::commit();
                        return redirect()->route('cotizacion.show', $cotizacion->id_cotizacion)->with('success', 'Cotización creada correctamente.');
                }

                public function obtenerClasificacion($residuo)
                {
                    // Verifica que $residuo sea un objeto y no un string o null
                    if (is_object($residuo)) {
                        // Si YRespelClasf4741 tiene un valor, lo devuelve
                        if (!empty($residuo->YRespelClasf4741)) {
                            return $residuo->YRespelClasf4741;
                        }
                        // Si YRespelClasf4741 no tiene un valor pero ARespelClasf4741 sí, lo devuelve
                        elseif (!empty($residuo->ARespelClasf4741)) {
                            return $residuo->ARespelClasf4741;
                        }
                    }

                    // Si ambas clasificaciones están vacías, retorna 'sin clasificación'
                    return 'sin clasificación';
                }

                /**
                 * Obtener tarifa de proveedor por tratamiento
                 * 
                 * @param Request $request
                 * @return \Illuminate\Http\JsonResponse
                 */
                public function obtenerTarifaProveedor(Request $request)
                {
                    try {
                        $tratamientoId = $request->input('tratamiento_id');
                        $cantidadKg = $request->input('cantidad_kg', 0);

                        if (!$tratamientoId) {
                            return response()->json(['error' => 'Tratamiento no especificado'], 400);
                        }

                        Log::info('Buscando tarifa', ['tratamiento_id' => $tratamientoId, 'cantidad_kg' => $cantidadKg]);

                        // Obtener el tratamiento con su sede y proveedor
                        $tratamiento = Tratamiento::with(['gestor.clientes'])
                            ->where('ID_Trat', $tratamientoId)
                            ->where('TratDelete', 0)
                            ->first();

                        if (!$tratamiento) {
                            Log::warning('Tarifa: tratamiento no encontrado', ['tratamiento_id' => $tratamientoId]);
                            return response()->json(['error' => 'Tratamiento no encontrado'], 404);
                        }

                        // Verificar que tenga gestor (sede) asociado
                        if (!$tratamiento->FK_TratProv || !$tratamiento->gestor) {
                            Log::warning('Tarifa: tratamiento sin sede asociada', [
                                'tratamiento_id' => $tratamientoId,
                                'FK_TratProv' => $tratamiento->FK_TratProv,
                                'gestor_cargado' => $tratamiento->gestor ? 'si' : 'no'
                            ]);
                            return response()->json(['error' => 'El tratamiento no tiene sede/proveedor asociado'], 404);
                        }

                        // Obtener el proveedor (cliente) asociado al tratamiento a través de la sede
                        $proveedorId = $tratamiento->gestor->FK_SedeCli ?? null;
                        
                        if (!$proveedorId) {
                            Log::warning('Tarifa: proveedorId no encontrado desde sede', [
                                'tratamiento_id' => $tratamientoId,
                                'sede_id' => $tratamiento->gestor->ID_Sede,
                                'FK_SedeCli' => $tratamiento->gestor->FK_SedeCli,
                                'gestor_nombre' => $tratamiento->gestor->SedeName ?? 'N/A'
                            ]);
                            return response()->json(['error' => 'La sede del tratamiento no tiene cliente asociado'], 404);
                        }

                        // Verificar que el cliente sea realmente un proveedor
                        $proveedor = \App\Cliente::where('ID_Cli', $proveedorId)
                            ->where('CliCategoria', 'Proveedor')
                            ->first();
                        
                        if (!$proveedor) {
                            $clienteInfo = \App\Cliente::where('ID_Cli', $proveedorId)->first();
                            Log::warning('Tarifa: cliente no es proveedor', [
                                'tratamiento_id' => $tratamientoId,
                                'proveedorId' => $proveedorId,
                                'categoria' => $clienteInfo ? $clienteInfo->CliCategoria : 'no encontrado'
                            ]);
                            return response()->json(['error' => 'El tratamiento no está asociado a un proveedor válido. Categoría: ' . ($clienteInfo ? $clienteInfo->CliCategoria : 'no encontrado')], 404);
                        }

                    // Buscar tarifa del proveedor para este tratamiento
                    $tarifa = ProveedorTarifa::where('FK_Proveedor', $proveedorId)
                        ->where('FK_Tratamiento', $tratamientoId)
                        ->where('PTarifatipo', 'Kg')
                        ->where('PTarifaDelete', 0)
                        ->with(['rangos' => function($query) {
                            $query->orderBy('PTarifaDesde', 'asc');
                        }])
                        ->first();

                    if (!$tarifa || $tarifa->rangos->isEmpty()) {
                        Log::info('Tarifa: no encontrada', [
                            'tratamiento_id' => $tratamientoId,
                            'proveedorId' => $proveedorId,
                        ]);
                        return response()->json([
                            'error' => 'No se encontró tarifa para este tratamiento',
                            'precio_kg' => 0,
                            'rango_encontrado' => null
                        ], 404);
                    }

                    // Buscar el rango apropiado según la cantidad
                    $rangoEncontrado = null;
                    $precioPorKg = 0;

                    foreach ($tarifa->rangos as $rango) {
                        if ($cantidadKg >= $rango->PTarifaDesde) {
                            $rangoEncontrado = $rango;
                            $precioPorKg = $rango->PTarifaPrecio;
                        } else {
                            break; // Los rangos están ordenados ascendente
                        }
                    }

                    // Si no se encontró rango, usar el último (más alto)
                    if (!$rangoEncontrado && $tarifa->rangos->isNotEmpty()) {
                        $rangoEncontrado = $tarifa->rangos->last();
                        $precioPorKg = $rangoEncontrado->PTarifaPrecio;
                    }

                        return response()->json([
                            'success' => true,
                            'precio_kg' => $precioPorKg,
                            'rango_desde' => $rangoEncontrado ? $rangoEncontrado->PTarifaDesde : 0,
                            'tarifa_id' => $tarifa->ID_PTarifa,
                            'rango_id' => $rangoEncontrado ? $rangoEncontrado->ID_PRango : null
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al obtener tarifa de proveedor', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'tratamiento_id' => $request->input('tratamiento_id')
                        ]);
                        return response()->json([
                            'error' => 'Error interno al obtener tarifa: ' . $e->getMessage()
                        ], 500);
                    }
                }
                
                // Almacenar una nueva cotización
               /**
                 * Store a newly created resource in storage.
                 *
                 * @param  \Illuminate\Http\Request  $request
                 * @return \Illuminate\Http\Response
                 */
                
                 public function store(CotizacionStoreRequest $request)
                 {
                     // Depuración para mostrar todos los datos recibidos
                     // dd($request->all());
                     $validatedData = $request->validated();
                     
                    DB::beginTransaction();
                    try {
                        $validatedData = $request->validated();

                        // Crear la cotización principal
                        $cotizacion = Cotizacion::create([
                            'FechaCotizacion'         => now(),
                            'Nit'                     => $validatedData['Nit'],
                            'Razon_Social'            => $validatedData['Razon_Social'],
                            'Telefono'                => $validatedData['Telefono'],
                            'Correo'                  => $validatedData['Correo'],
                            'Direccion'               => $validatedData['Direccion'],
                            'CoStatus'                => $validatedData['CoStatus'],
                            'Total'                   => $validatedData['Total'],
                            'transporte'              => $validatedData['transporte'],
                            'sede'                    => $validatedData['sede'],
                            'frecuencia_recoleccion'  => $validatedData['frecuencia_recoleccion'],
                            'tipo_cotizacion'         => $validatedData['tipo_cotizacion'],
                            'Observaciones'           => $validatedData['Observaciones'] ?? null,
                            'Auditlog'=> Auth::user()->email,
                            'status'                  => $validatedData['status'], // Estado de aprobación 
                        ]);

                        // Ajustar campos extra si es necesario
                        $cotizacion->Auditlog      = Auth::user()->email;
                        $cotizacion->Observaciones = $validatedData['Observaciones'] ?? null;
                        $cotizacion->save();

                        // Arrays enviados desde el formulario
                        $residuos      = $validatedData['residuos'];
                        $clasf4741     = $validatedData['clasf4741'];
                        $peligrosidad  = $validatedData['peligrosidad'];
                        $estadoFisico  = $validatedData['estado_fisico'];
                        $tratamientos  = $validatedData['tratamientos'];
                        $cantidadKilos = $validatedData['cantidad_kilos'];
                        $precioKg      = $validatedData['precio_kg'];
                        $subtotal      = $validatedData['subtotal'];

                        // Verificar la longitud de los arreglos principales
                        $cantidadItems = count($residuos);
                        if (
                            count($clasf4741)    !== $cantidadItems ||
                            count($peligrosidad) !== $cantidadItems ||
                            count($estadoFisico) !== $cantidadItems ||
                            count($tratamientos) !== $cantidadItems
                        ) {
                            throw new \Exception('La cantidad de elementos de residuos no coincide con sus campos.');
                        }

                        // Verificar cada residuo y sus tratamientos dentro del mismo bucle
                        for ($i = 0; $i < $cantidadItems; $i++) {
                            // Asegurarse de que tratamientos y kilos sean arreglos reales
                            if (!is_array($tratamientos[$i])) {
                                throw new \Exception("El elemento en 'tratamientos[$i]' no es un array.");
                            }
                            if (!is_array($cantidadKilos[$i])) {
                                throw new \Exception("El elemento en 'cantidadKilos[$i]' no es un array.");
                            }
                            if (!is_array($precioKg[$i])) {
                                throw new \Exception("El elemento en 'precioKg[$i]' no es un array.");
                            }
                            if (!is_array($subtotal[$i])) {
                                throw new \Exception("El elemento en 'subtotal[$i]' no es un array.");
                            }

                            // Comparar porcentajes de longitud en los subarreglos
                            if (
                                count($tratamientos[$i]) !== count($cantidadKilos[$i]) ||
                                count($tratamientos[$i]) !== count($precioKg[$i])      ||
                                count($tratamientos[$i]) !== count($subtotal[$i])
                            ) {
                                throw new \Exception('La cantidad de tratamientos no coincide con kilos o precios.');
                            }

                            // Guardar registros para cada tratamiento
                            foreach ($tratamientos[$i] as $tIndex => $tratamientoId) {
                                $cotizacion->coti_respel()->create([
                                    'FK_ID_Respel'   => $residuos[$i],
                                    'FK_Tratamiento' => $tratamientoId,
                                    'cantidad_kilos' => $cantidadKilos[$i][$tIndex],
                                    'precio_kg'      => $precioKg[$i][$tIndex],
                                    'subtotal'       => $subtotal[$i][$tIndex],
                                    'peligrosidad'   => $peligrosidad[$i],
                                    'clasf4741'      => $clasf4741[$i],
                                    'estado_fisico'  => $estadoFisico[$i],
                                ]);
                            }
                        }
                        $destinatarios = ['notificaciones@prosarc.com.co'];
                        // Enviar correo de creación
                        /* $this->enviarCorreoCotizacionCreada($cotizacion); */
                        Mail::to($destinatarios)->send(new CotizacionCreada($cotizacion));

                        DB::commit();
                        return redirect()->route('cotizacion.show', $cotizacion->id_cotizacion)->with('success', 'Cotización creada correctamente.');
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Cotización Error:', ['error' => $e->getMessage()]);
                        return back()->withErrors([
                            'error' => 'Hubo un problema al crear la cotización: ' . $e->getMessage()
                        ]);
                    }
                }
                 public function edit($id)
                 { 
                     $cotizacion = Cotizacion::with('coti_respel')->findOrFail($id);
                    
                 
                     return view('cotizacion.edit', compact('cotizacion' ));
                 }
                 /**
                 * Actualiza la cotización en el almacenamiento.
                 *
                 * @param  \Illuminate\Http\Request  $request
                 * @param  int  $id_cotizacion
                 * @return \Illuminate\Http\Response
                 */
                public function update(CotizacionUpdateRequest $request, $id_cotizacion)
                {
                DB::beginTransaction();

                try {
                    // Verificar que el usuario tenga el rol adecuado
                    if (Auth::user()->UsRol !== "Programador") {
                        return redirect()->route('home')->with('error', 'No tienes permiso para acceder a esta sección.');
                    }
                    
                    /* dd($request->all()); */
                    // Validar los datos de la solicitud
                    $validatedData = $request->validated();
                    
                    // Encontrar la cotización existente
                    $cotizacion = Cotizacion::findOrFail($id_cotizacion);

                    // Actualizar los campos principales de la cotización
                    $cotizacion->FechaCotizacion = now();
                    $cotizacion->Nit = $validatedData['Nit'];
                    $cotizacion->Razon_Social = $validatedData['Razon_Social'];
                    $cotizacion->Telefono = $validatedData['Telefono'];
                    $cotizacion->Correo = $validatedData['Correo'];
                    $cotizacion->Direccion = $validatedData['Direccion'];
                    $cotizacion->CoStatus = $validatedData['CoStatus'];
                    $cotizacion->Total = $validatedData['Total'];
                    $cotizacion->transporte = $validatedData['transporte'];
                    $cotizacion->sede = $validatedData['sede'];
                    $cotizacion->frecuencia_recoleccion = $validatedData ['frecuencia_recoleccion'];
                    /* $cotizacion->tipo_cotizacion = $validatedData'tipo_cotizacion'; */
                    $cotizacion->Observaciones = $validatedData['Observaciones'] ?? null;
                    $cotizacion->save();

                    // Obtener los arreglos de datos de residuos
                    $residuos = $validatedData['residuos'];
                    $clasf4741 = $validatedData['clasf4741'];
                    $tratamientos = $validatedData['tratamientos'];
                    $cantidadKilos = $validatedData['cantidad_kilos'];
                    $precioKg = $validatedData['precio_kg'];
                    $subtotal = $validatedData['subtotal'];
                    $peligrosidad = $validatedData['peligrosidad'];
                    $estadoFisico = $validatedData['estado_fisico'];

                    // Obtener los IDs de coti_respel desde el formulario
                    $cotiRespelIds = $request->input('id');

                    // Verificar que todos los arreglos tengan la misma longitud
                    $cantidadItems = count($residuos);
                    if (
                        count($cotiRespelIds) !== $cantidadItems ||
                        count($clasf4741) !== $cantidadItems ||
                        count($tratamientos) !== $cantidadItems ||
                        count($cantidadKilos) !== $cantidadItems ||
                        count($precioKg) !== $cantidadItems ||
                        count($subtotal) !== $cantidadItems ||
                        count($peligrosidad) !== $cantidadItems ||
                        count($estadoFisico) !== $cantidadItems
                    ) {
                        throw new \Exception('La cantidad de elementos en los campos no coincide.');
                    }

                    // Iterar sobre los arreglos para actualizar cada `coti_respel`
                    for ($i = 0; $i < $cantidadItems; $i++) {
                        $cotiRespelId = $cotiRespelIds[$i];

                        // Buscar el coti_respel correspondiente
                        $cotiRespel = CotiRespel::findOrFail($cotiRespelId);

                        // Actualizar los campos necesarios
                        $cotiRespel->cantidad_kilos = $cantidadKilos[$i];
                        $cotiRespel->precio_kg = $precioKg[$i];
                        $cotiRespel->subtotal = $subtotal[$i];
                        // Si es necesario actualizar otros campos, descomenta y asigna
                        // $cotiRespel->peligrosidad = $peligrosidad[$i];
                        // $cotiRespel->clasf4741 = $clasf4741[$i];
                        // $cotiRespel->estado_fisico = $estadoFisico[$i];
                        // $cotiRespel->FK_ID_Respel = $residuos[$i];
                         $cotiRespel->FK_Tratamiento = $tratamientos[$i];

                        $cotiRespel->save();
                    }

                    DB::commit();

                    // Redirigir con mensaje de éxito
                    return redirect()->route('cotizacion.show', $cotizacion->id_cotizacion)->with('success', 'Cotización actualizada correctamente.');
                } catch (\Exception $e) {
                    // Revertir la transacción en caso de error
                    DB::rollBack();
                    Log::error('Error al actualizar cotización:', ['error' => $e->getMessage()]);
                    return back()->withErrors(['error' => 'Hubo un problema al actualizar la cotización: ' . $e->getMessage()]);
                }
                }
                // Otros métodos...
                public function aprobar($id)
                {
                    try {
                        DB::beginTransaction();

                        $cotizacion = Cotizacion::findOrFail($id);
                        $cotizacion->status = request('status');
                        $cotizacion->save();

                        // Verificar si el estado es "Aprobado" antes de enviar el correo
                        if ($cotizacion->status === 'Aprobado') {
                            $this->enviarCorreoCotizacionAprobada($cotizacion);
                        }

                        DB::commit();

                        return redirect()
                            ->route('cotizacion.show', $cotizacion->id_cotizacion)
                            ->with('success', 'Cotización aprobada correctamente.');
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Error al aprobar cotización:', ['error' => $e->getMessage()]);
                        return back()->withErrors([
                            'error' => 'Hubo un problema al aprobar la cotización: ' . $e->getMessage()
                        ]);
                    }
                }
                       /**
                     * Método para enviar correo de cotización creada al Coordinador Comercial.
                     */
                     /**
                     * Envía correo cuando se aprueba una cotización
                     */
                    private function enviarCorreoCotizacionAprobada($cotizacion)
                    {
                        if ($cotizacion->Auditlog) {
                            try {
                                Mail::to($cotizacion->Auditlog)->send(new CotizacionAprobada($cotizacion));
                            } catch (\Exception $e) {
                                Log::error('Error enviando correo de cotización aprobada:', ['error' => $e->getMessage()]);
                            }
                        }
                    }
            
                    public function show($id)
                    {
                        $cotizacion = Cotizacion::with(['coti_respel'])
                            ->where('id_cotizacion', $id)
                            ->firstOrFail();

                        return view('cotizacion.show', compact('cotizacion'));
                    }
                    
                    /**
                     * Genera y descarga un PDF de la cotización especificada.
                     *
                     * @param  int  $id
                     * @return \Illuminate\Http\Response
                     */
                    public function downloadPDF($id)
                    {
                        // Cargar la cotización con las relaciones necesarias
                        $cotizacion = Cotizacion::with(['coti_respel.respel', 'coti_respel.tratamiento'])->findOrFail($id);
                        // Usar la firma del comercial creador (Auditlog). Si no existe, usar el usuario autenticado.
                        $usuario = User::where('email', $cotizacion->Auditlog)->first() ?? Auth::user();

                        //return $usuario;
                        // Generar el PDF utilizando la vista 'cotizacion.pdf' y pasando los datos necesarios
                        //$pdf = PDF::loadView('cotizacion.pdf', compact('cotizacion', 'usuario'));

                        $pdf = PDF::setPaper('letter', 'portrait')->loadView('cotizacion.pdf', compact('cotizacion', 'usuario'));
                        $nombre = $cotizacion->id_cotizacion . '.pdf';

                        // Guardar SOLO cuando la cotización esté aprobada (para no publicar previas en /storage)
                        if (($cotizacion->Status ?? null) === 'Aprobado') {
                            $path = 'public/cotizacion/' . sprintf("%0s", $nombre);
                            Storage::put($path, $pdf->output(), 'public');
                        }

                        // Vista previa inmediata en el navegador
                        return $pdf->stream($nombre);

                        // Descargar el PDF con un nombre específico
                        //return $pdf->stream('cotizacion_' . $cotizacion->id_cotizacion . '.pdf');
                    }

                    /**
                     * Permite a comerciales actualizar su firma (usada en PDF de cotizaciones).
                     */
                    public function updateFirma(Request $request)
                    {
                        $user = Auth::user();

                        $rolesComerciales = ['Comercial', 'Comercialap', 'Ejecutivo Comercial'];
                        $esComercial = in_array($user->UsRol, $rolesComerciales) || in_array($user->UsRol2, $rolesComerciales);
                        if (!$esComercial) {
                            abort(403, 'No tiene permiso para actualizar la firma.');
                        }

                        $request->validate([
                            'firma' => 'nullable|image|mimes:png|max:2048',
                        ]);

                        if (!$request->hasFile('firma')) {
                            return redirect()->back()->with('success', 'No se realizaron cambios en la firma.');
                        }

                        $file = $request->file('firma');
                        if (!$file || !$file->isValid()) {
                            $mensaje = $file ? $file->getErrorMessage() : 'No se recibió el archivo de firma.';
                            return redirect()->back()->withErrors(['firma' => 'No se pudo cargar la firma. ' . $mensaje]);
                        }

                        $destDir = public_path('img');
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0755, true);
                        }

                        // Guardar por ID (usado en cotización/pdf.blade.php)
                        $destById = $destDir . DIRECTORY_SEPARATOR . $user->id . '.png';
                        if (file_exists($destById)) {
                            @unlink($destById);
                        }
                        // Usar move() para evitar problemas con rutas temporales vacías
                        $file->move($destDir, $user->id . '.png');

                        // Guardar también por slug (usado en otros documentos)
                        if (!empty($user->UsSlug)) {
                            $destBySlug = $destDir . DIRECTORY_SEPARATOR . $user->UsSlug . '.png';
                            if (file_exists($destBySlug)) {
                                @unlink($destBySlug);
                            }
                            // Copiar desde el archivo por ID ya guardado
                            @copy($destById, $destBySlug);
                        }

                        return redirect()->back()->with('success', 'Firma actualizada correctamente.');
                    }
        }
    ?>