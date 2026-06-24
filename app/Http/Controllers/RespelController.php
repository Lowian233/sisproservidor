<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;
use App\Http\Requests\RespelStoreRequest;
use App\Http\Requests\RespelUpdateRequest;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\RespelMail;
use App\Mail\incompleteRespel;
use App\Mail\ResiduoNuevo;
use App\Mail\RespelCorregido;
use App\audit;
use App\Respel;
use App\Sede;
use App\Cotizacions;
use App\Tratamiento;
use App\Pretratamiento;
use App\Clasificacion;
use App\User;
use App\Requerimiento;
use App\Rango;
use App\ResiduosGener;
use App\SolicitudResiduo;
use App\Permisos;
use App\Tarifa;
use App\Personal;
use App\Categoryrespelpublic;
use App\Subcategoryrespelpublic;

use App\Role;
use App\Cliente;
use App\RespelComentario;

use Illuminate\Support\Arr;

class RespelController extends Controller
{
    /** Buzón interno único para notificaciones de residuos / RESPel. */
    private const MAIL_RESIDUOS_INTERNO = 'residuos@prosarc.com.co';

     /**
     * Display a listing of the resource.
     * Filtros: nombre, cliente, año (optimización para listas grandes).
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
            $query = DB::table('respels')
                ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
                ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
                ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                ->join('personals', 'personals.ID_Pers', '=', 'clientes.CliComercial')
                ->select('respels.*', 'clientes.CliName', 'clientes.CliComercial', 'clientes.CliCategoria', 'personals.PersEmail', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersCellphone')
                ->where(function($q){
                    switch (Auth::user()->UsRol) {
                        case 'Cliente':
                            $UserSedeID = DB::table('personals')
                                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                                ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                                ->value('clientes.ID_Cli');

                            $q->where('respels.RespelDelete', 0)
                                  ->where('respels.RespelPublic', 0)
                                  ->where('clientes.ID_Cli', $UserSedeID);
                            break;

                        case 'Comercial':
                            $ComercialAsignado = DB::table('personals')
                                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                                ->value('personals.ID_Pers');

                            $q->where('respels.RespelDelete', 0)
                                  ->where('respels.RespelPublic', 0)
                                  ->where('clientes.CliComercial', $ComercialAsignado);
                            break;

                        default:
                            $q->where('respels.RespelDelete', 0)
                                  ->where('respels.RespelPublic', 0);
                            break;
                    }
                })
                ->where('clientes.CliCategoria', 'Cliente');

            // Filtro por nombre
            if ($request->filled('nombre')) {
                $query->where('respels.RespelName', 'like', '%' . $request->nombre . '%');
            }
            // Filtro por cliente
            if ($request->filled('cliente')) {
                $query->where('clientes.ID_Cli', $request->cliente);
            }
            // Filtro por año (created_at)
            if ($request->filled('anio')) {
                $query->whereYear('respels.created_at', $request->anio);
            }

            // Carga diferida: solo ejecutar query pesada cuando el usuario hace clic en Buscar o Ver todos
            $buscar = $request->has('buscar') && $request->buscar == '1';
            $verTodos = $request->has('ver') && $request->ver === 'todos';
            $Respels = ($buscar || $verTodos) ? $query->get() : collect();

            foreach ($Respels as $key => $value) {
                $requerimiento = Requerimiento::where('FK_ReqRespel', $Respels[$key]->ID_Respel)
                    ->where('forevaluation', 1)
                    ->where('ofertado', 1)
                    ->first();

                if (isset($requerimiento->FK_ReqTrata) && $requerimiento->ofertado == 1) {
                    $tratamiento = Tratamiento::where('ID_Trat', $requerimiento->FK_ReqTrata)->first('TratName');
                    if (isset($tratamiento->TratName)) {
                        $Respels[$key]->TratName = $tratamiento->TratName;
                    } else {
                        $Respels[$key]->TratName = '';
                    }
                } else {
                    $Respels[$key]->TratName = '';
                }
            }



        foreach ($Respels as $respel) {
            if (!isset($respel->TratName)) {
                $respel->TratName = '';
            }
        }

        // Opciones para filtros
        $clientesFiltro = collect();
        $aniosFiltro = collect();
        if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::COMERCIALAP)) {
            $qClientes = DB::table('respels')
                ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
                ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
                ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                ->where('respels.RespelDelete', 0)
                ->where('respels.RespelPublic', 0)
                ->where('clientes.CliCategoria', 'Cliente');
            if (Auth::user()->UsRol == 'Comercial') {
                $ComercialAsignado = DB::table('personals')->where('personals.ID_Pers', Auth::user()->FK_UserPers)->value('personals.ID_Pers');
                $qClientes->where('clientes.CliComercial', $ComercialAsignado);
            }
            $clientesFiltro = $qClientes->select('clientes.ID_Cli', 'clientes.CliName')
                ->distinct()
                ->orderBy('clientes.CliName')
                ->get();
        }
        $aniosFiltro = DB::table('respels')
            ->where('respels.RespelDelete', 0)
            ->where('respels.RespelPublic', 0)
            ->select(DB::raw('DISTINCT YEAR(created_at) as anio'))
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->filter();

        return view('respels.index', compact('Respels', 'clientesFiltro', 'aniosFiltro'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Bloquear acceso a clientes
        if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
            abort(403, 'Los clientes no pueden crear residuos directamente. Por favor envíe la información por correo electrónico para evaluación técnica.');
        }

        // Permitir a usuarios de conciliaciones, PDA y gerente de planta crear residuos
        $rolesPermitidos = array_merge(
            Permisos::RESPELPUBLIC,
            Permisos::INGDETURNO,
            Permisos::PROGRAMADOR,
            Permisos::JEFELOGISTICA,
            Permisos::ASISTENTELOGISTICA,
            Permisos::ProgVehic1,
            Permisos::COMERCIALEINGRURNO,
            Permisos::ADMINISTRADORPLANTA,
            Permisos::DIRECCIONTECNICA
        );
        if(in_array(Auth::user()->UsRol, $rolesPermitidos) || in_array(Auth::user()->UsRol2, $rolesPermitidos)){
            // Listar TODOS los generadores con el encadenado correcto: FK_GenerCli → Sede → Cliente
            $Generadores = DB::table('generadors as g')
                ->leftJoin('sedes as s', 's.ID_Sede', '=', 'g.FK_GenerCli')      // FK_GenerCli → Sede
                ->leftJoin('clientes as c', 'c.ID_Cli', '=', 's.FK_SedeCli')     // Sede → Cliente
                ->select(
                    'g.ID_Gener',
                    'g.GenerName',
                    's.ID_Sede',
                    's.SedeName',
                    'c.ID_Cli',
                    'c.CliName'
                )
                ->where(function($q){
                    $q->where('g.GenerDelete', 0)->orWhereNull('g.GenerDelete'); // tolera NULL=activo
                })
                ->orderBy('g.GenerName')
                ->get();
            
            // Obtener tratamientos con su gestor asociado
            $tratamientos = DB::table('tratamientos')
                ->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->select(
                    'tratamientos.*',
                    'clientes.CliShortname',
                    'clientes.CliName'
                )
                ->where('tratamientos.TratDelete', 0)
                ->orderBy('tratamientos.TratName')
                ->get();            
            // ID del tratamiento de termodestrucción (ID 1 según la base de datos)
            $termodestruccionId = 1;
            
            $categories = Categoryrespelpublic::all();
            return view('respels.create', compact('Generadores', 'categories', 'tratamientos', 'termodestruccionId'));
        }else{
            abort(403);
        }
    }
     /**
     * store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(RespelStoreRequest $request)
    {

        // Permitir a usuarios de conciliaciones, PDA y gerente de planta crear residuos
        $rolesPermitidos = array_merge(
            Permisos::RESPELPUBLIC,
            Permisos::INGDETURNO,
            Permisos::PROGRAMADOR,
            Permisos::JEFELOGISTICA,
            Permisos::ASISTENTELOGISTICA,
            Permisos::ProgVehic1,
            Permisos::COMERCIALEINGRURNO,
            Permisos::ADMINISTRADORPLANTA,
            Permisos::DIRECCIONTECNICA
        );
        if(!(in_array(Auth::user()->UsRol, $rolesPermitidos) || in_array(Auth::user()->UsRol2, $rolesPermitidos))){
            abort(403);
        }else{
            $UserSedeID = $request->input('Sede');

            // Validar que la sede existe
            $sedeExiste = DB::table('sedes')->where('ID_Sede', $UserSedeID)->exists();
            if (!$sedeExiste) {
                return redirect()->back()->with('error', 'La sede seleccionada no existe. Por favor, seleccione una sede válida.');
            }
            
            // Validar que Dirección Técnica proporcione comentario obligatorio para aprobación automática
            if (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                if (!$request->filled('respel_comentario_aprobacion')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El comentario de aprobación es obligatorio para Dirección Técnica. Por favor, proporcione una justificación de la aprobación automática.');
                }
                
                // Validar longitud mínima del comentario (al menos 10 caracteres)
                if (strlen(trim($request->input('respel_comentario_aprobacion'))) < 10) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El comentario de aprobación debe tener al menos 10 caracteres. Por favor, proporcione una justificación detallada.');
                }
            }
        }
        //return $request['FK_SubCategoryRP'];
        if ($request['FK_SubCategoryRP'] == 2) {
            $subcategoria = Subcategoryrespelpublic::where('ID_SubCategoryRP',  $request['FK_SubCategoryRP'])->first();
        }else if ($request['FK_SubCategoryRP'] == 3) {
            $subcategoria = Subcategoryrespelpublic::where('ID_SubCategoryRP',  $request['FK_SubCategoryRP'])->first();
            $subcategoria->SubCategoryRpName = '';
        } else {
            $subcategoria = new Subcategoryrespelpublic();
            $subcategoria->SubCategoryRpName = 'No Definida';
        }


        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)||$subcategoria->SubCategoryRpName == 'Agregado-Manual') {
            /*se crea un nueva cotizacion solo si el cliente no tiene cotizaciones pendientes*/
            $Cotizacion = new Cotizacions();
            $Cotizacion->CotiNumero = 7;
            $Cotizacion->CotiFechaSolicitud = now();
            $Cotizacion->CotiDelete = 0;
            $Cotizacion->CotiStatus = "Aprobada";
            $Cotizacion->FK_CotiSede = $UserSedeID;
            $Cotizacion->save();
        } else if (in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)||$subcategoria->SubCategoryRpName == 'No Definida'){
            /*se crea un nueva cotizacion solo si el cliente no tiene cotizaciones pendientes*/
            $Cotizacion = new Cotizacions();
            $Cotizacion->CotiNumero = 7;
            $Cotizacion->CotiFechaSolicitud = now();
            $Cotizacion->CotiDelete = 0;
            $Cotizacion->CotiStatus = "Aprobada";
            $Cotizacion->FK_CotiSede = $UserSedeID;
            $Cotizacion->save();
        } else {

        }



        for ($x=0; $x < count($request['RespelName']); $x++) {
            /*validar si el formulario incluye archivos de tarjeta de emergencia u hoja de seguridad*/
            $respel = new Respel();

            if (isset($request['RespelHojaSeguridad'][$x])) {
                $file1 = $request['RespelHojaSeguridad'][$x];
                $hoja = hash('sha256', rand().time().$file1->getClientOriginalName()).'.pdf';

                $file1->move(public_path().'/img/HojaSeguridad/',$hoja);
         }
            else{
                $hoja = 'RespelHojaDefault.pdf';
            }

             /*verificar si se cargo un documento en este campo*/
            if (isset($request['RespelTarj'][$x])) {
                $file2 = $request['RespelTarj'][$x];
                $tarj = hash('sha256', rand().time().$file2->getClientOriginalName()).'.pdf';
                $file2->move(public_path().'/img/TarjetaEmergencia/',$tarj);
            }else{
                $tarj = 'RespelTarjetaDefault.pdf';
            }

             /*verificar si se cargo un documento en este campo*/
            if (isset($request['RespelFoto'][$x])) {
                $file3 = $request['RespelFoto'][$x];
                $foto = hash('sha256', rand().time().$file3->getClientOriginalName()).'.png';
                $file3->move(public_path().'/img/fotoRespelCreate/',$foto);
            }else{
                $foto = 'RespelFotoDefault.png';
            }

            /*verificar si se cargo un documento en este campo*/
            if (isset($request['SustanciaControladaDocumento'][$x])) {
                $file4 = $request['SustanciaControladaDocumento'][$x];
                $ctrlDoc = hash('sha256', rand().time().$file4->getClientOriginalName()).'.pdf';
                $file4->move(public_path().'/img/SustanciaControlDoc/',$ctrlDoc);
            }else{
                $ctrlDoc = 'SustanciaControlDocDefault.pdf';
            }

            /*se verifica el rol de usuario para asignar un status al residuo*/
            // FALTA antes: Programador y cliente

            // if (in_array(Auth::user()->UsRol, Permisos::CLIENTEYADMINS)) {
            //     $statusinicial="Pendiente";
            // }
            $respel->RespelName = $request['RespelName'][$x];
            $respel->RespelDescrip = $request['RespelDescrip'][$x];
            $respel->RespelIgrosidad = $request['RespelIgrosidad'][$x];
            $respel->YRespelClasf4741 = $request['YRespelClasf4741'][$x];
            $respel->ARespelClasf4741 = $request['ARespelClasf4741'][$x];
            $respel->RespelEstado = $request['RespelEstado'][$x];

            // se verifica si el residuo esta marcada como aceite usado
            if(isset($request['AceiteUsado'][$x])&&($request['AceiteUsado'][$x]==1)){
                $respel->AceiteUsado = $request['AceiteUsado'][$x];

            }else {
                $respel->AceiteUsado = 0;
            }



            // se verifica si la sustancia esta marcada como controlada
            if (isset($request['SustanciaControlada'][$x])&&($request['SustanciaControlada'][$x]==1)) {
                $respel->SustanciaControlada = $request['SustanciaControlada'][$x];
                $respel->SustanciaControladaTipo = $request['SustanciaControladaTipo'][$x];
                $respel->SustanciaControladaNombre = $request['SustanciaControladaNombre'][$x];
                $respel->SustanciaControladaDocumento = $ctrlDoc;
            }else{
                $respel->SustanciaControlada = 0;
            }
            // Determinar el status del residuo
            if ($request['FK_SubCategoryRP'] == 'Agregado-Manual') {
                $respel->RespelStatus = "Aprobado";
            } elseif (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                // Auto-aprobación para Dirección Técnica
                $respel->RespelStatus = "Aprobado";
                // Guardar el comentario de aprobación en RespelStatusDescription
                if ($request->filled('respel_comentario_aprobacion')) {
                    $respel->RespelStatusDescription = "Aprobado automáticamente por Dirección Técnica (" . Auth::user()->email . "):\n\n" 
                                                      . $request->input('respel_comentario_aprobacion');
                } else {
                    $respel->RespelStatusDescription = "Aprobado automáticamente por Dirección Técnica (" . Auth::user()->email . ")";
                }
            } else {
                $respel->RespelStatus = "Pendiente";
            }
            // $respel->RespelStatus = $statusinicial;
            $respel->RespelHojaSeguridad = $hoja;
            $respel->RespelTarj = $tarj;
            $respel->RespelFoto = $foto;
            // Resolver FK de cotización de forma segura (última por sede) o fallback a 1
            $fkCoti = DB::table('cotizacions')
                ->where('FK_CotiSede', $UserSedeID)
                ->orderBy('ID_Coti', 'desc')
                ->value('ID_Coti');
            if (!$fkCoti) { $fkCoti = 1; }
            if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)||$subcategoria->SubCategoryRpName == 'Agregado-Manual') {
                $respel->FK_RespelCoti = $fkCoti;
                $respel->RespelPublic = 0;
                $respel->FK_SubCategoryRP = 1;
            } else if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)||$subcategoria->SubCategoryRpName == 'Comun'){
                $respel->FK_RespelCoti = 1;
                $respel->RespelPublic = 1;
                $respel->FK_SubCategoryRP = $request['FK_SubCategoryRP'];
            }else{
                $respel->FK_RespelCoti = $fkCoti;
                $respel->RespelPublic = 0;
            }
            $respel->RespelSlug = hash('sha256', rand().time().$respel->RespelName);
            $respel->RespelDelete = 0;
            $respel->RespelDeclaracion = $request['RespelDeclaracion'][$x];
            $respel->save();

            $requerimiento = new Requerimiento();
            $requerimiento->ofertado=1;
            $requerimiento->FK_ReqRespel=$respel->ID_Respel;
            $requerimiento->forevaluation=1;
            // Soporte para múltiples tratamientos (array) o tratamiento único
            $tratamientoId = null;
            if (isset($request['RespelTratamiento'])) {
                if (is_array($request['RespelTratamiento'])) {
                    // Es un array, obtener el elemento en la posición $x
                    $tratamientoId = isset($request['RespelTratamiento'][$x]) 
                        ? $request['RespelTratamiento'][$x] 
                        : $request['RespelTratamiento'][0];
                } else {
                    // Es un valor único, usarlo directamente
                    $tratamientoId = $request['RespelTratamiento'];
                }
            }
            
            if (!$tratamientoId) {
                throw new \Exception('No se especificó un tratamiento para el residuo.');
            }
            
            $requerimiento->FK_ReqTrata=$tratamientoId;
            $requerimiento->ReqSlug= hash('md5', rand().time().$respel->ID_Respel);
            $requerimiento->save();

            $tratamiento = Tratamiento::where('ID_Trat', $tratamientoId)->first();

            $tarifa = new Tarifa();
            $tarifa->TarifaFrecuencia='Servicio';
            $tarifa->TarifaVencimiento='2025-11-15';
            if ($tratamiento->TratName == 'Posconsumo luminarias') {
                $tarifa->Tarifatipo='Unid';
            }else{
                $tarifa->Tarifatipo='Kg';
            }
            $tarifa->TarifaDelete=0;
            $tarifa->FK_TarifaReq=$requerimiento->ID_Req;
            $tarifa->save();

            $rango = new Rango();
            $rango->TarifaPrecio=1500;
            $rango->TarifaDesde=1;
            $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
            $rango->save();

            // Enviar notificación de creación siempre que se crea un residuo (sin importar si está Pendiente o Aprobado)
            // La notificación de aprobación es diferente y se envía cuando se aprueba un residuo pendiente
            $esDireccionTecnica = in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA);
            
            // Siempre enviar notificación de creación (excepto para Agregado-Manual que se maneja después)
            /*se verifican los datos de las sede y y cliente segun el usuarios que registra el residuo*/
            // Si es Dirección Técnica, obtener datos del cliente desde la sede del request
            $clienteData = null;
            if ($esDireccionTecnica) {
                $clienteData = DB::table('sedes')
                    ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                    ->where('sedes.ID_Sede', $UserSedeID)
                    ->select(['sedes.SedeName', 'clientes.CliName', 'clientes.CliComercial'])
                    ->first();
            } else {
                // Para otros usuarios, obtener desde el personal del usuario actual
                $clienteData = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->select(['sedes.SedeName', 'clientes.CliName', 'clientes.CliComercial'])
                    ->first();
            }
            
            // Solo enviar notificación si se encontró el cliente y no es Agregado-Manual
            if($clienteData){

                // se verifica si el cliente tiene comercial asignado
                // se establece la lista de destinatarios
                $comercial = null;
                if ($clienteData->CliComercial <> null) {
                    $comercial = Personal::where('ID_Pers', $clienteData->CliComercial)->first();
                } else {
                    $comercial = "";
                }

                // Agregar propiedades adicionales al objeto $respel para la notificación
                $respel->cliente = $clienteData;
                $respel->comercial = $comercial;
                $respel->personalcliente = Personal::where('ID_Pers', Auth::user()->FK_UserPers)->first();
                $respel->usuario_creador = Auth::user()->email;
                $respel->usuario_creador_nombre = Auth::user()->UsName ?? Auth::user()->email;

                if (isset($subcategoria->SubCategoryRpName)) {
                    if ($subcategoria->SubCategoryRpName == 'Agregado-Manual') {

                    }else{
                        Mail::to(self::MAIL_RESIDUOS_INTERNO)->send(new ResiduoNuevo($respel));
                    }
                }
                // return new ResiduoNuevo($respel);
            }
        }

        $log = new audit();
        $log->AuditTabla="respels";
        $log->AuditType="Nuevo respel";
        $log->AuditRegistro=$respel->ID_Respel;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($request->all());
        $log->save();

        // Mensaje de éxito diferenciado para auto-aprobación de Dirección Técnica
        $successMessage = 'Residuo creado satisfactoriamente';
        if (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
            $cantidadResiduos = count($request['RespelName']);
            $successMessage = $cantidadResiduos > 1 
                ? "✅ Se crearon y aprobaron automáticamente {$cantidadResiduos} residuos. No requieren evaluación adicional."
                : '✅ Residuo creado y aprobado automáticamente. No requiere evaluación adicional.';
        }

        if (isset($subcategoria->SubCategoryRpName)) {
            if ($subcategoria->SubCategoryRpName == 'Agregado-Manual') {
                return redirect()->route('respels.indexExpress')->with('success', 'Residuo creado satisfactoriamente');
            }else if($subcategoria->SubCategoryRpName == 'No Definida'){
                return redirect()->route('respels.index')->with('success', 'Residuo creado satisfactoriamente');
            } else {
                return redirect()->route('respels.index')->with('success', 'Residuo creado satisfactoriamente');
            }
        }else{
            return redirect()->route('respels.index')->with('success', 'Residuo creado satisfactoriamente');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Respels = Respel::where('RespelSlug', $id)->first();

        if ($Respels->RespelDelete == 1) {
            abort(404);
        }

        /*se  verifica si el residuo tiene alguna registro hijo o dependiente*/
        $ResiduoConDependencia1 = ResiduosGener::where('FK_Respel', $Respels->ID_Respel)->first();
        $ResiduoConDependencia2 = Requerimiento::where('FK_ReqRespel', $Respels->ID_Respel)->first();

        if ($Respels->RespelStatus=='Rechazado'||$Respels->RespelStatus=='Incompleto'||$Respels->RespelStatus=='Pendiente') {
            $deleteButton = 'borrable';
        }else{
            $deleteButton = 'No borrable';
        }

        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE))
            if ($Respels->RespelStatus=='Rechazado'||$Respels->RespelStatus=='Incompleto'||$Respels->RespelStatus=='Falta TDE'||$Respels->RespelStatus=='Pendiente'||$Respels->RespelStatus=='TDE actualizada') {
                $editButton = 'Editable';
            }else{
                $editButton = 'No editable';
            }
        else{
            $editButton = 'Editable';
        }

        //consultar cuales son los tratamientos viabiizados por jefe de operaciones
        $requerimientos = Requerimiento::with(['pretratamientosSelected'])
        ->where('FK_ReqRespel', '=', $Respels->ID_Respel)
        ->where('forevaluation', '=', 1)
        ->get();



        // se incorporan las tarifas al array
        foreach ($requerimientos as $requerimiento) {
            $tarifas = Tarifa::with(['rangos' => function ($query) {
                return $query->orderBy('TarifaDesde','ASC');
            }])
            ->where('FK_TarifaReq', '=', $requerimiento->ID_Req)
            ->get();
            $requerimiento['tarifas'] = $tarifas;
            $requerimiento['tratamientos'] = Tratamiento::with(['pretratamientos'])
            ->where('ID_Trat', '=', $requerimiento['FK_ReqTrata'] )
            ->get();
        }

        return view('respels.show', compact('Respels', 'requerimientos', 'editButton', 'deleteButton'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        /*se verifican el rol del usuario para dar acceso a la edicion de respel o evaluacion de respel*/
        if(in_array(Auth::user()->UsRol, Permisos::GrupoEdicionRespel) || in_array(Auth::user()->UsRol2, Permisos::GrupoEvaluacionRespel)){

            $Respels = Respel::where('RespelSlug', $id)->first();

           
            // El historial de cambios solo está disponible para Dirección Técnica
            $auditHistory = null;
            if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                $auditHistory = \App\audit::where('AuditRegistro', $Respels->ID_Respel)
                    ->where('AuditTabla', 'respels')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            //Tabla tratamientos con su respectivo gestor
            $tratamientos = DB::table('tratamientos')
                ->join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
                ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                ->select('sedes.*', 'clientes.*', 'tratamientos.*')
                ->where('TratDelete', 0)
                ->get();


            if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)){
                /*se valida que el residuo no este eliminado*/
                if ($Respels->RespelDelete == 1) {
                    abort(404);
                }
                // se verifica el rol y el status del residuo para saber si se puede editar
                $statusRespel = $Respels->RespelStatus;

                /*se  verifica si el residuo tiene alguna registro hijo o dependiente*/
                $ResiduoConDependencia1 = ResiduosGener::where('FK_Respel', $Respels->ID_Respel)->first();
                $ResiduoConDependencia2 = Requerimiento::where('FK_ReqRespel', $Respels->ID_Respel)->first();

                if ($ResiduoConDependencia1||$ResiduoConDependencia2) {
                    $deleteButton = 'No borrable';
                }else{
                    $deleteButton = 'borrable';
                }

                $Sede = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->select('sedes.ID_Sede')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->get();

                /*el Cliente solo puede editar pendientes e incompletos*/
                switch ($statusRespel) {
                    case 'Aprobado':
                        return view('respels.edit', compact('Respels', 'Sede','tratamientos', 'auditHistory'));
                        break;
                    case 'Pendiente':
                        return view('respels.edit', compact('Respels', 'Sede', 'tratamientos', 'auditHistory'));
                        break;
                    case 'Incompleto':
                        return view('respels.edit', compact('Respels', 'Sede', 'tratamientos', 'auditHistory'));
                        break;
                    case 'Falta TDE':
                        return view('respels.editTDE', compact('Respels', 'Sede', 'tratamientos', 'auditHistory'));
                        break;
                    case 'TDE actualizada':
                        return view('respels.editTDE', compact('Respels', 'Sede', 'tratamientos', 'auditHistory'));
                        break;
                    default:
                        abort(403);
                        break;
                }
            }else{
                // Obtener tratamientos con su gestor asociado (sin usar tabla clasificacion que no existe en producción)
                $tratamientos = DB::table('tratamientos')
                    ->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
                    ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                    ->select(
                        'tratamientos.*',
                        'clientes.CliShortname',
                        'clientes.CliName'
                    )
                    ->where('tratamientos.TratDelete', 0)
                    ->orderBy('tratamientos.TratName')
                    ->get();

                // Crear estructura compatible con la vista que espera tratamientosConGestor
                // La vista usa doble foreach: tratamientosViables -> tratamientosConGestor
                $tratamientosViables = collect([
                    (object)[
                        'tratamientosConGestor' => $tratamientos
                    ]
                ]);

                // return $tratamientosViables;
                //consultar cuales son los tratamientos viabilizados por jefe de operaciones
                $requerimientos = Requerimiento::with(['pretratamientosSelected'])
                ->where('FK_ReqRespel', '=', $Respels->ID_Respel)
                ->where('forevaluation', '=', 1)
                ->get();
                // se incorporan las tarifas al array
                foreach ($requerimientos as $requerimiento) {
                    // adjuntar tarifas relacionadas
                    $requerimiento['tarifas'] = Tarifa::with(['rangos' => function ($query) {
                        return $query->orderBy('TarifaDesde','ASC');
                    }])
                    ->where('FK_TarifaReq', '=', $requerimiento->ID_Req)
                    ->get();

                    // adjuntar tratamientos relacionadas
                    $requerimiento['tratamientos'] = Tratamiento::with(['pretratamientos'])
                    ->where('ID_Trat', '=', $requerimiento['FK_ReqTrata'] )
                    ->get();

                    // validar si el requerimiento se encuentra en uso
                    $usado = SolicitudResiduo::where('FK_SolResRequerimiento', '=', $requerimiento->ID_Req)
                    ->get('FK_SolResRequerimiento');
                    // return $usado;

                    // if (count($usado)>0) {
                    //     return $usado;
                    // }
                    if (count($usado)>0) {
                        $requerimiento['en_uso'] = 1;
                    }else{
                        $requerimiento['en_uso'] = 0;
                    }
                }

                // return $requerimientos;
                $Sedes = DB::table('clientes')
                    ->join('sedes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                    ->select('sedes.ID_Sede', 'sedes.SedeName')
                    ->where('clientes.ID_Cli', '<>', 1)
                    ->get();

                return view('respels.edit', compact('Respels', 'Sedes', 'requerimientos', 'tratamientos', 'tratamientosViables', 'auditHistory'));
            }
        }else{
            abort(403);
        }
    }

        /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editADP($id)
    {
        /*se verifican el rol del usuario para dar acceso a la edicion de respel o evaluacion de respel*/
        if (in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones) || in_array(Auth::user()->UsRol, Permisos::GrupoEdicionRespel)) {

            $Respels = Respel::where('RespelSlug', $id)->first();

            //Tabla tratamientos con su respectivo gestor
            $tratamientos = DB::table('tratamientos')
                ->join('sedes', 'sedes.ID_Sede', '=', 'tratamientos.FK_TratProv')
                ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                ->select(
                    'tratamientos.*',
                    'clientes.CliShortname',
                    'clientes.CliName'
                )
                ->where('tratamientos.TratDelete', 0)
                ->orderBy('tratamientos.TratName')
                ->get();

            /*se valida que el residuo no este eliminado*/
            if ($Respels->RespelDelete == 1) {
                abort(404);
            }
            // se verifica el rol y el status del residuo para saber si se puede editar
            $statusRespel = $Respels->RespelStatus;

            /*se  verifica si el residuo tiene alguna registro hijo o dependiente*/
            $ResiduoConDependencia1 = ResiduosGener::where('FK_Respel', $Respels->ID_Respel)->first();
            $ResiduoConDependencia2 = Requerimiento::where('FK_ReqRespel', $Respels->ID_Respel)->first();

            if ($ResiduoConDependencia1 || $ResiduoConDependencia2) {
                $deleteButton = 'No borrable';
            } else {
                $deleteButton = 'borrable';
            }

            $Sede = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->select('sedes.ID_Sede')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->get();
            $cliente = null;
            if ($Respels && $Respels->FK_RespelCoti) {
                $cotizacion = \App\Cotizacion::find($Respels->FK_RespelCoti);
                if ($cotizacion && $cotizacion->FK_CotiSede) {
                    $sede = \App\Sede::find($cotizacion->FK_CotiSede);
                    if ($sede && $sede->FK_SedeCli) {
                        $cliente = \App\Cliente::find($sede->FK_SedeCli);
                    }
                }
            }
            // El historial de cambios solo está disponible para Dirección Técnica
            $auditHistory = null;
            if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                $auditHistory = \App\audit::where('AuditRegistro', $Respels->ID_Respel)
                    ->where('AuditTabla', 'respels')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return view('respels.editADP', compact('Respels', 'Sede', 'cliente', 'tratamientos', 'auditHistory'));


        } else {
            abort(403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RespelUpdateRequest $request, $id)
    {
        // return $request;
        $respel = Respel::where('RespelSlug', $id)->first();

        if (!$respel) {
            abort(404);
        }

        $originalAttributes = $respel->getOriginal();
        // $originalUsername = $originalAttributes['username'];

        if (isset($request['RespelHojaSeguridad'])) {
            if($respel->RespelHojaSeguridad <> null && file_exists(public_path().'/img/HojaSeguridad/'.$respel->RespelHojaSeguridad)){
                unlink(public_path().'/img/HojaSeguridad/'.$respel->RespelHojaSeguridad);
            }
            $file1 = $request['RespelHojaSeguridad'];
            $hoja = hash('sha256', rand().time().$file1->getClientOriginalName()).'.pdf';
            $file1->move(public_path().'/img/HojaSeguridad/',$hoja);
        }
        else{
            $hoja = $respel->RespelHojaSeguridad;
        }

            /*verificar si se cargo un documento en este campo*/
        if (isset($request['RespelTarj'])) {
            if($respel->RespelTarj <> null && file_exists(public_path().'/img/TarjetaEmergencia/'.$respel->RespelTarj)){
                unlink(public_path().'/img/TarjetaEmergencia/'.$respel->RespelTarj);
            }
            $file2 = $request['RespelTarj'];
            $tarj = hash('sha256', rand().time().$file2->getClientOriginalName()).'.pdf';
            $file2->move(public_path().'/img/TarjetaEmergencia/',$tarj);
        }else{
            $tarj = $respel->RespelTarj;
        }

            /*verificar si se cargo un documento en este campo*/
        if (isset($request['RespelFoto'])) {
            if($respel->RespelFoto <> null && file_exists(public_path().'/img/fotoRespelCreate/'.$respel->RespelFoto)){
                unlink(public_path().'/img/fotoRespelCreate/'.$respel->RespelFoto);
            }
            $file3 = $request['RespelFoto'];
            $foto = hash('sha256', rand().time().$file3->getClientOriginalName()).'.png';
            $file3->move(public_path().'/img/fotoRespelCreate/',$foto);
        }else{
            $foto = $respel->RespelFoto;
        }

        /*verificar si se cargo un documento en este campo*/
        if (isset($request['SustanciaControladaDocumento'])) {
            if($respel->SustanciaControladaDocumento <> null && file_exists(public_path().'/img/SustanciaControlDoc/'.$respel->SustanciaControladaDocumento)){
                unlink(public_path().'/img/SustanciaControlDoc/'.$respel->SustanciaControladaDocumento);
            }
            $file4 = $request['SustanciaControladaDocumento'];
            $ctrlDoc = hash('sha256', rand().time().$file4->getClientOriginalName()).'.pdf';
            $file4->move(public_path().'/img/SustanciaControlDoc/',$ctrlDoc);
        }else{
            $ctrlDoc = $respel->SustanciaControladaDocumento;
        }

        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
            $respel->RespelStatus = "Pendiente";
        }else {
            // Auto-aprobación para Dirección Técnica al editar en cualquier vista (edit, editADP)
            if (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                $respel->RespelStatus = "Aprobado";
            } else {
                // Fallback: si RespelStatus no viene en el request (ej. editADP), mantener el actual
                $respel->RespelStatus = $request->filled('RespelStatus') ? $request['RespelStatus'] : $respel->RespelStatus;
            }
        }

        $respel->RespelName = $request['RespelName'];
        $respel->RespelDescrip = $request['RespelDescrip'];
        $respel->RespelIgrosidad = $request['RespelIgrosidad'];

        if ($request['RespelIgrosidad'] == 'No peligroso') {
            $respel->YRespelClasf4741 = null;
            $respel->ARespelClasf4741 = null;
        }else{
            $respel->YRespelClasf4741 = $request['YRespelClasf4741'];
            $respel->ARespelClasf4741 = $request['ARespelClasf4741'];
        }

        $respel->RespelEstado = $request['RespelEstado'];
        $respel->SustanciaControlada = $request['SustanciaControlada'];
        $respel->SustanciaControladaTipo = $request['SustanciaControladaTipo'];
        $respel->SustanciaControladaNombre = $request['SustanciaControladaNombre'];
        $respel->AceiteUsado = $request['AceiteUsado'];
        $respel->RespelHojaSeguridad = $hoja;
        $respel->RespelTarj = $tarj;
        $respel->RespelFoto = $foto;
        $respel->SustanciaControladaDocumento = $ctrlDoc;
        $respel->RespelDeclaracion = $request['RespelDeclaracion'];
        $respel->update();

        if (isset($request['RespelTratamiento'])) {
            $requerimiento = Requerimiento::where('FK_ReqRespel', $respel->ID_Respel)
            ->where('ofertado', 1)
            ->where('forevaluation', 1)
            ->first();
            
            if ($requerimiento) {
                $requerimiento->FK_ReqTrata = $request['RespelTratamiento'];
                $requerimiento->update();
            }
        }

        // Preparar datos detallados para la auditoría
        $auditData = [
            'user' => [
                'email' => Auth::user()->email,
                'role' => Auth::user()->UsRol,
                'role2' => Auth::user()->UsRol2 ?? null
            ],
            'changes' => [],
            'context' => [
                'from_view' => $request->from_view ?? 'standard',
                'previous_status' => $originalAttributes['RespelStatus'] ?? null,
                'new_status' => $respel->RespelStatus,
                'timestamp' => now()->toDateTimeString(),
                'client_info' => isset($cliente) ? [
                    'name' => $cliente->CliName ?? null,
                    'sede' => isset($sede) ? $sede->SedeName : null
                ] : null
            ],
            'files_updated' => [
                'hoja_seguridad' => $hoja !== $respel->getOriginal('RespelHojaSeguridad'),
                'tarjeta_emergencia' => $tarj !== $respel->getOriginal('RespelTarj'),
                'foto' => $foto !== $respel->getOriginal('RespelFoto'),
                'doc_controlada' => $ctrlDoc !== $respel->getOriginal('SustanciaControladaDocumento')
            ]
        ];

        // Registrar cambios específicos en campos importantes
        $fieldsToTrack = [
            'RespelName' => 'Nombre del residuo',
            'RespelDescrip' => 'Descripción',
            'RespelIgrosidad' => 'Peligrosidad',
            'YRespelClasf4741' => 'Clasificación Y',
            'ARespelClasf4741' => 'Clasificación A',
            'RespelEstado' => 'Estado físico',
            'SustanciaControlada' => 'Es sustancia controlada',
            'SustanciaControladaTipo' => 'Tipo de sustancia controlada',
            'SustanciaControladaNombre' => 'Nombre de sustancia controlada',
            'AceiteUsado' => 'Es aceite usado',
            'RespelDeclaracion' => 'Declaración'
        ];

        foreach ($fieldsToTrack as $field => $label) {
            if (isset($originalAttributes[$field]) && $respel->$field !== $originalAttributes[$field]) {
                $auditData['changes'][$label] = [
                    'old' => $originalAttributes[$field],
                    'new' => $respel->$field
                ];
            }
        }
        
        // Manejar tratamiento por separado ya que no es campo directo de Respel
        if (isset($request['RespelTratamiento'])) {
            $requerimientoAnterior = Requerimiento::where('FK_ReqRespel', $respel->ID_Respel)
                ->where('ofertado', 1)
                ->where('forevaluation', 1)
                ->first();
            
            if ($requerimientoAnterior && $requerimientoAnterior->FK_ReqTrata != $request['RespelTratamiento']) {
                $auditData['changes']['Tratamiento asociado'] = [
                    'old' => $requerimientoAnterior->FK_ReqTrata,
                    'new' => $request['RespelTratamiento']
                ];
            }
        }

        // Registrar el comentario si existe
        if ($request->filled('respel_comentario')) {
            $auditData['comentario'] = [
                'mensaje' => $request->respel_comentario,
                'tipo' => in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) ? 'prosarc' : 'cliente'
            ];
        }

        // Construir auditoría con cambios campo a campo (formato esperado por la vista)
        $fieldsToTrack = [
            'RespelName',
            'RespelDescrip',
            'RespelIgrosidad',
            'YRespelClasf4741',
            'ARespelClasf4741',
            'RespelEstado',
            'RespelStatus',
            'RespelStatusDescription',
            'SustanciaControlada',
            'SustanciaControladaTipo',
            'SustanciaControladaNombre',
            'AceiteUsado',
            'RespelDeclaracion',
            // archivos
            'RespelHojaSeguridad',
            'RespelTarj',
            'RespelFoto',
            'SustanciaControladaDocumento',
        ];

        $auditChanges = [];
        foreach ($fieldsToTrack as $field) {
            $old = $originalAttributes[$field] ?? null;
            $new = $respel->$field ?? null;
            if ($old !== $new) {
                $auditChanges[$field] = [
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }
        
        // Manejar tratamiento por separado ya que no es campo directo de Respel
        if (isset($request['RespelTratamiento'])) {
            $requerimientoAnterior = Requerimiento::where('FK_ReqRespel', $respel->ID_Respel)
                ->where('ofertado', 1)
                ->where('forevaluation', 1)
                ->first();
            
            if ($requerimientoAnterior && $requerimientoAnterior->FK_ReqTrata != $request['RespelTratamiento']) {
                $auditChanges['Tratamiento'] = [
                    'old' => $requerimientoAnterior->FK_ReqTrata,
                    'new' => $request['RespelTratamiento']
                ];
            }
        }

        $log = new audit();
        $log->AuditTabla = "respels";
        $log->AuditType = "Modificado"; // mantener el tipo que la vista reconoce
        $log->AuditRegistro = $respel->ID_Respel;
        $log->AuditUser = Auth::user()->email;
        // La vista busca arrays con keys de campos y subkeys old/new, y un flag opcional 'comentario_agregado'
        $auditPayload = $auditChanges;
        $auditPayload['comentario_agregado'] = false;
        $log->Auditlog = json_encode($auditPayload);
        $log->save();

        // Persist comment to dedicated table and mark audit when a comment was provided
        if ($request->filled('respel_comentario')) {
            try {
                $coment = new RespelComentario();
                $coment->FK_ComentRespel= $respel->ID_Respel;
                // Tipo según rol: clientes -> 'cliente', resto -> 'prosarc'
                $coment->ComentTipo = (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) ? 'cliente' : 'prosarc';
                $coment->ComentMensaje = $request->respel_comentario;
                $coment->ComentUser = Auth::user()->email ?? Auth::user()->name ?? 'system';
                $coment->ComentUserRol = Auth::user()->UsRol ?? '';
                $coment->ComentDate = now();
                $coment->save();

                // update audit record to include a flag used by the view
                $auditData = is_string($log->Auditlog) ? json_decode($log->Auditlog, true) : (array)$log->Auditlog;
                if (!is_array($auditData)) { $auditData = []; }
                $auditData['comentario_agregado'] = true;
                $log->Auditlog = json_encode($auditData);
                $log->save();
            } catch (\Exception $e) {
                // don't break the update if comment persistence fails; log for later inspection
                // using Laravel logger would be ideal but keep silent here to avoid side effects
            }
        }

        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO) && $originalAttributes['RespelStatus'] === 'Incompleto' && $respel->RespelStatus === 'Pendiente') {
            /*se verifican los datos de las sede y y cliente segun el usuarios que registra el residuo*/
            $respel['cliente'] = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->select(['sedes.SedeName', 'clientes.CliName', 'clientes.CliComercial'])
                ->first();

            if ($respel['cliente']->CliComercial <> null) {
                $comercial = Personal::where('ID_Pers', $respel['cliente']->CliComercial)->first();
            } else {
                $comercial = "";
            }

            $respel['comercial'] = $comercial;
            $respel['personalcliente'] = Personal::where('ID_Pers', Auth::user()->FK_UserPers)->first();

            Mail::to(self::MAIL_RESIDUOS_INTERNO)->send(new RespelCorregido($respel));
        }

        return redirect()->route('respels.index', [$respel->RespelSlug]);

    }
   /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Respels = Respel::where('RespelSlug', $id)->first();
        if (!$Respels) {
            abort(404);
        }
        switch (Auth::user()->UsRol) {
            case 'Programador':
            case 'DireccionTecnica':
                if ($Respels->RespelDelete == 0) {
                    $Respels->RespelDelete = 1;
                }
                else{
                    $Respels->RespelDelete = 0;
                }
                break;
            default:
                if ($Respels->RespelDelete == 0) {
                    $Respels->RespelDelete = 1;
                }
                else{
                    abort(403);
                }
                break;
        }
        $Respels->save();

        $log = new audit();
        $log->AuditTabla="respels";
        $log->AuditType="Eliminado";
        $log->AuditRegistro=$Respels->ID_Respel;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=$Respels->RespelDelete;
        $log->save();

        return redirect()->route('respels.index');
    }
      /**
     * actualiza el status del residuo .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatusRespel(Request $request, $id)
    {
        // return $request;
        $respel = Respel::where('RespelSlug', $id)->first();
        $opciones = $request->Opcion;

         if (
            in_array(Auth::user()->UsRol, Permisos::JefeOperaciones) ||
            in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones) ||
            in_array(Auth::user()->UsRol, Permisos::SUPERVISOR) ||
            in_array(Auth::user()->UsRol2, Permisos::SUPERVISOR) ||
            in_array(Auth::user()->UsRol, Permisos::COMERCIALAP) ||
            in_array(Auth::user()->UsRol2, Permisos::COMERCIALAP) ||
            in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) ||
            in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA) ||
            in_array(Auth::user()->UsRol, Permisos::ADMINISTRADORPLANTA) ||
            in_array(Auth::user()->UsRol2, Permisos::ADMINISTRADORPLANTA)
        ) {
            /*se eliminan los requerimientos relacionados*/
            $requerimientosparaBorrar = Requerimiento::where('FK_ReqRespel', $respel->ID_Respel)
            ->where('forevaluation', 1)
            ->get();
            foreach ($requerimientosparaBorrar as $key => $value) {
                $value->pretratamientosSelected()->detach();
                $deletedRequerimientos = Requerimiento::where('ID_Req', $value['ID_Req'])->delete();
            }

            if ($opciones) {
                foreach ($opciones as $key => $value) {
                    if (isset($opciones[$key])) {
                        if (isset($opciones[$key]['ReqSlug'])&&($opciones[$key]['ReqSlug'])!="") {
                            // se actualiza el requerimiento segun corresponda
                            $requerimientoparaActualizar = Requerimiento::where('ReqSlug', $opciones[$key]['ReqSlug'])->first();
                            if (isset($opciones[$key]['Tratamiento'])) {

                                if (isset($opciones[$key]['ReqFotoDescargue'])) {
                                    $requerimientoparaActualizar->ReqFotoDescargue=$opciones[$key]['ReqFotoDescargue'];
                                }
                                if (isset($opciones[$key]['ReqFotoDestruccion'])) {
                                    $requerimientoparaActualizar->ReqFotoDestruccion=$opciones[$key]['ReqFotoDestruccion'];
                                }
                                if (isset($opciones[$key]['ReqVideoDescargue'])) {
                                    $requerimientoparaActualizar->ReqVideoDescargue=$opciones[$key]['ReqVideoDescargue'];
                                }
                                if (isset($opciones[$key]['ReqVideoDestruccion'])) {
                                     $requerimientoparaActualizar->ReqVideoDestruccion=$opciones[$key]['ReqVideoDestruccion'];
                                }
                                if (isset($opciones[$key]['ReqDevolucion'])) {
                                    $requerimientoparaActualizar->ReqDevolucion=$opciones[$key]['ReqDevolucion'];
                                }
                                if (isset($opciones[$key]['ReqAuditoria'])) {
                                    $requerimientoparaActualizar->ReqAuditoria=$opciones[$key]['ReqAuditoria'];
                                }
                                // requerimietnos automaticos?
                                if (isset($opciones[$key]['auto_ReqFotoDescargue'])) {
                                    $requerimientoparaActualizar->auto_ReqFotoDescargue=$opciones[$key]['auto_ReqFotoDescargue'];
                                }
                                if (isset($opciones[$key]['auto_ReqFotoDestruccion'])) {
                                    $requerimientoparaActualizar->auto_ReqFotoDestruccion=$opciones[$key]['auto_ReqFotoDestruccion'];
                                }
                                if (isset($opciones[$key]['auto_ReqVideoDescargue'])) {
                                    $requerimientoparaActualizar->auto_ReqVideoDescargue=$opciones[$key]['auto_ReqVideoDescargue'];
                                }
                                if (isset($opciones[$key]['auto_ReqVideoDestruccion'])) {
                                     $requerimientoparaActualizar->auto_ReqVideoDestruccion=$opciones[$key]['auto_ReqVideoDestruccion'];
                                }
                                if (isset($opciones[$key]['auto_ReqDevolucion'])) {
                                    $requerimientoparaActualizar->auto_ReqDevolucion=$opciones[$key]['auto_ReqDevolucion'];
                                }
                                if (isset($opciones[$key]['auto_ReqAuditoria'])) {
                                    $requerimientoparaActualizar->auto_ReqAuditoria=$opciones[$key]['auto_ReqAuditoria'];
                                }

                                /*se adjunta los elementos relacionados al requerimiento*/
                                if (isset($request->TratOfertado) && $key == $request->TratOfertado) {
                                    $requerimientoparaActualizar->ofertado=1;
                                }else{
                                    $requerimientoparaActualizar->ofertado=0;
                                }
                                $requerimientoparaActualizar->FK_ReqRespel=$respel->ID_Respel;
                                $requerimientoparaActualizar->forevaluation=1;
                                $requerimientoparaActualizar->FK_ReqTrata=$opciones[$key]['Tratamiento'];
                                // $requerimientoparaActualizar->ReqSlug= hash('md5', rand().time().$respel->ID_Respel);
                                $requerimientoparaActualizar->save();

                                if (isset($opciones[$key]['Pretratamientos'])) {
                                    $requerimientoparaActualizar->pretratamientosSelected()->sync($opciones[$key]['Pretratamientos']);
                                }
                                /*se verifica que las tarifas no esten disabled en la vista*/
                                if (isset($opciones[$key]['TarifaFrecuencia'])) {
                                    // $tarifa = new Tarifa();

                                    $tarifa = Tarifa::where('FK_TarifaReq', $requerimientoparaActualizar->ID_Req)->first();
                                    $tarifa->TarifaFrecuencia=$opciones[$key]['TarifaFrecuencia'];
                                    $tarifa->TarifaVencimiento=$opciones[$key]['TarifaVencimiento'];
                                    $tarifa->Tarifatipo=$opciones[$key]['Tarifatipo'];
                                    $tarifa->TarifaDelete=0;
                                    $tarifa->FK_TarifaReq=$requerimientoparaActualizar->ID_Req;
                                    if (isset($opciones[$key]['TarifaSpecial'])) {
                                        $tarifa->TarifaSpecial=$opciones[$key]['TarifaSpecial'];

                                        $log = new audit();
                                        $log->AuditTabla="tarifas y rangos";
                                        $log->AuditType="rangos Updated";
                                        $log->AuditRegistro=$respel->ID_Respel;
                                        $log->AuditUser=Auth::user()->email;
                                        $log->Auditlog=$opciones[$key];
                                        $log->save();
                                    }
                                    $tarifa->save();

                                    foreach ($opciones[$key]['TarifaDesde'] as $key2 => $value2) {
                                       if ($opciones[$key]['TarifaPrecio'][$key2] != null) {
                                            if (isset($opciones[$key]['ID_Rango'][$key2])) {
                                                // $rango = new Rango();
                                               $rango = Rango::where('ID_Rango', $opciones[$key]['ID_Rango'][$key2])->first();
                                               $rango->TarifaPrecio=$opciones[$key]['TarifaPrecio'][$key2];
                                               $rango->TarifaDesde=$opciones[$key]['TarifaDesde'][$key2];
                                               $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
                                               $rango->save();
                                            }else{
                                               $rango = new Rango();
                                               $rango->TarifaPrecio=$opciones[$key]['TarifaPrecio'][$key2];
                                               $rango->TarifaDesde=$opciones[$key]['TarifaDesde'][$key2];
                                               $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
                                               $rango->save();
                                            }
                                       }else{
                                            if (isset($opciones[$key]['ID_Rango'][$key2])) {
                                                // $rango = new Rango();
                                               $rango = Rango::where('ID_Rango', $opciones[$key]['ID_Rango'][$key2])->first();
                                               // $rango->TarifaPrecio=0;
                                               // $rango->TarifaDesde=0;
                                               // $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
                                               $rango->delete();
                                            }
                                       }
                                    }
                                }
                            }
                        }else{
                            // se crea un requerimiento por cada opcion
                            if (isset($opciones[$key]['Tratamiento'])) {

                                $requerimiento = new Requerimiento();
                                if (isset($opciones[$key]['ReqFotoDescargue'])) {
                                    $requerimiento->ReqFotoDescargue=$opciones[$key]['ReqFotoDescargue'];
                                }
                                if (isset($opciones[$key]['ReqFotoDestruccion'])) {
                                    $requerimiento->ReqFotoDestruccion=$opciones[$key]['ReqFotoDestruccion'];
                                }
                                if (isset($opciones[$key]['ReqVideoDescargue'])) {
                                    $requerimiento->ReqVideoDescargue=$opciones[$key]['ReqVideoDescargue'];
                                }
                                if (isset($opciones[$key]['ReqVideoDestruccion'])) {
                                     $requerimiento->ReqVideoDestruccion=$opciones[$key]['ReqVideoDestruccion'];
                                }
                                if (isset($opciones[$key]['ReqDevolucion'])) {
                                    $requerimiento->ReqDevolucion=$opciones[$key]['ReqDevolucion'];
                                }
                                if (isset($opciones[$key]['ReqAuditoria'])) {
                                    $requerimiento->ReqAuditoria=$opciones[$key]['ReqAuditoria'];
                                }

                                if (isset($opciones[$key]['auto_ReqFotoDescargue'])) {
                                    $requerimiento->auto_ReqFotoDescargue=$opciones[$key]['auto_ReqFotoDescargue'];
                                }
                                if (isset($opciones[$key]['auto_ReqFotoDestruccion'])) {
                                    $requerimiento->auto_ReqFotoDestruccion=$opciones[$key]['auto_ReqFotoDestruccion'];
                                }
                                if (isset($opciones[$key]['auto_ReqVideoDescargue'])) {
                                    $requerimiento->auto_ReqVideoDescargue=$opciones[$key]['auto_ReqVideoDescargue'];
                                }
                                if (isset($opciones[$key]['auto_ReqVideoDestruccion'])) {
                                     $requerimiento->auto_ReqVideoDestruccion=$opciones[$key]['auto_ReqVideoDestruccion'];
                                }
                                if (isset($opciones[$key]['auto_ReqDevolucion'])) {
                                    $requerimiento->auto_ReqDevolucion=$opciones[$key]['auto_ReqDevolucion'];
                                }
                                if (isset($opciones[$key]['auto_ReqAuditoria'])) {
                                    $requerimiento->auto_ReqAuditoria=$opciones[$key]['auto_ReqAuditoria'];
                                }

                                /*se adjunta los elementos relacionados al requerimiento*/
                                if (isset($request->TratOfertado) && $key == $request->TratOfertado) {
                                    $requerimiento->ofertado=1;
                                }else{
                                    $requerimiento->ofertado=0;
                                }
                                $requerimiento->FK_ReqRespel=$respel->ID_Respel;
                                $requerimiento->forevaluation=1;
                                $requerimiento->FK_ReqTrata=$opciones[$key]['Tratamiento'];
                                $requerimiento->ReqSlug= hash('md5', rand().time().$respel->ID_Respel);
                                $requerimiento->save();

                                if (isset($opciones[$key]['Pretratamientos'])) {
                                    $requerimiento->pretratamientosSelected()->attach($opciones[$key]['Pretratamientos']);
                                }
                                /*se verifica que las tarifas no esten disabled en la vista*/
                                if (isset($opciones[$key]['TarifaFrecuencia'])) {
                                    $tarifa = new Tarifa();
                                    $tarifa->TarifaFrecuencia=$opciones[$key]['TarifaFrecuencia'];
                                    $tarifa->TarifaVencimiento=$opciones[$key]['TarifaVencimiento'];
                                    $tarifa->Tarifatipo=$opciones[$key]['Tarifatipo'];
                                    $tarifa->TarifaDelete=0;
                                    $tarifa->FK_TarifaReq=$requerimiento->ID_Req;
                                    if (isset($opciones[$key]['TarifaSpecial'])) {
                                        $tarifa->TarifaSpecial=$opciones[$key]['TarifaSpecial'];

                                        $log = new audit();
                                        $log->AuditTabla="tarifas y rangos";
                                        $log->AuditType="rangos Updated";
                                        $log->AuditRegistro=$respel->ID_Respel;
                                        $log->AuditUser=Auth::user()->email;
                                        $log->Auditlog=$opciones[$key];
                                        $log->save();
                                    }
                                    $tarifa->save();

                                    foreach ($opciones[$key]['TarifaDesde'] as $key2 => $value2) {
                                       if ($opciones[$key]['TarifaPrecio'][$key2] != null) {
                                           $rango = new Rango();
                                           $rango->TarifaPrecio=$opciones[$key]['TarifaPrecio'][$key2];
                                           $rango->TarifaDesde=$opciones[$key]['TarifaDesde'][$key2];
                                           $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
                                           $rango->save();
                                       }
                                    }
                                }else{
                                    $tarifa = new Tarifa();
                                    $tarifa->TarifaFrecuencia='Servicio';
                                    $tarifa->TarifaVencimiento= now()->subYear()->format('Y-m-d');
                                    $tarifa->Tarifatipo='Kg';
                                    $tarifa->TarifaDelete=0;
                                    $tarifa->FK_TarifaReq=$requerimiento->ID_Req;
                                    $tarifa->save();

                                    $rango = new Rango();
                                    $rango->TarifaPrecio=1500;
                                    $rango->TarifaDesde=1;
                                    $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
                                    $rango->save();
                                }
                            }
                        }
                    }
                }
            }
            // Auto-aprobación para Dirección Técnica al editar (edit o editADP)
            if (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                $respel->RespelStatus = 'Aprobado';
            }
            $respel->RespelStatus = $request['RespelStatus'];
            
            // Guardar comentario en RespelStatusDescription (observaciones) igual que en create
            if ($request->filled('respel_comentario') && !empty(trim($request->respel_comentario))) {
                // Si es Dirección Técnica, usar el mismo formato que en create
                if (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA)) {
                    $respel->RespelStatusDescription = "Aprobado automáticamente por Dirección Técnica (" . Auth::user()->email . "):\n\n" 
                                                      . $request->input('respel_comentario');
                } else {
                    // Para otros usuarios, guardar el comentario como observación
                    $respel->RespelStatusDescription = $request->input('respel_comentario');
                }
            } else {
                // Si no hay comentario pero hay RespelStatusDescription en el request, mantenerlo
                if ($request->filled('RespelStatusDescription')) {
                    $respel->RespelStatusDescription = $request['RespelStatusDescription'];
                }
            }
            
            $respel->updated_at = now();
            $respel->save();
        }else{
            abort(401, 'No Autorizado');
        }
        /*auditoria de la actualizacion*/
        $log = new audit();
        $log->AuditTabla="respels requerimiento y tarifas";
        $log->AuditType = $request->has('from_view') && $request->from_view == 'editADP' ? "ADP Updated" : "Evaluacion Updated";
        $log->AuditRegistro=$respel->ID_Respel;
        $log->AuditUser=Auth::user()->email;
        $auditData = $request->all();
        $auditData['comentario_agregado'] = $request->has('respel_comentario') && !empty($request->respel_comentario);
        $log->Auditlog = $auditData;
        $log->save();

        if($respel->RespelPublic === 0){
            switch ($respel->RespelStatus) {
                case 'Aprobado':
                    return redirect()->route('email-respel', [$respel->RespelSlug]);
                    break;

                case 'Incompleto':
                    Mail::to(self::MAIL_RESIDUOS_INTERNO)->send(new incompleteRespel($respel));
                    break;

                default:
                    # code...
                    break;
            }
        }

        // return redirect()->route('respels.edit', [$respel->RespelSlug]);
        if ($respel->RespelPublic === 1) {
            return redirect()->route('respelspublic.index');
        }else{
            return redirect()->route('respels.index');
        }
    }

      /**
     * actualiza el status del residuo .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createPublicRespel(Request $request, $id)
    {
        // return $request->Opcion[0]['TarifaFrecuencia'];
        $respel = Respel::where('RespelSlug', $id)->first();
        $opciones = $request->Opcion;
        // return $request;
        // return $tarifasparaBorrar;
        if (in_array(Auth::user()->UsRol, Permisos::SUPERVISOR)) {
            return redirect()->route('respels.show', [$respel->RespelSlug]);
        }
        if (in_array(Auth::user()->UsRol, Permisos::JefeOperaciones)||in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones)||in_array(Auth::user()->UsRol, Permisos::COMERCIAL)||in_array(Auth::user()->UsRol2, Permisos::COMERCIAL)) {
            /*se eliminan los requerimientos relacionados*/
            $requerimientosparaBorrar = Requerimiento::where('FK_ReqRespel', $respel->ID_Respel)->get();
            foreach ($requerimientosparaBorrar as $key => $value) {
                $value->pretratamientosSelected()->detach();
                $deletedRequerimientos = Requerimiento::where('ID_Req', $value['ID_Req'])->delete();
            }
        }

        if (in_array(Auth::user()->UsRol, Permisos::JefeOperaciones)||in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones)||in_array(Auth::user()->UsRol, Permisos::COMERCIAL)||in_array(Auth::user()->UsRol2, Permisos::COMERCIAL)) {
            if ($opciones) {
                foreach ($opciones as $key => $value) {
                    if ($opciones[$key]) {
                        // se crea un requerimiento por cada opcion
                        if (isset($opciones[$key]['Tratamiento'])) {

                            $requerimiento = new Requerimiento();
                            if (isset($opciones[$key]['ReqFotoDescargue'])) {
                                $requerimiento->ReqFotoDescargue=$opciones[$key]['ReqFotoDescargue'];
                            }
                            if (isset($opciones[$key]['ReqFotoDestruccion'])) {
                                $requerimiento->ReqFotoDestruccion=$opciones[$key]['ReqFotoDestruccion'];
                            }
                            if (isset($opciones[$key]['ReqVideoDescargue'])) {
                                $requerimiento->ReqVideoDescargue=$opciones[$key]['ReqVideoDescargue'];
                            }
                            if (isset($opciones[$key]['ReqVideoDestruccion'])) {
                                 $requerimiento->ReqVideoDestruccion=$opciones[$key]['ReqVideoDestruccion'];
                            }
                            if (isset($opciones[$key]['ReqDevolucion'])) {
                                $requerimiento->ReqDevolucion=$opciones[$key]['ReqDevolucion'];
                            }
                            if (isset($opciones[$key]['ReqAuditoria'])) {
                                $requerimiento->ReqAuditoria=$opciones[$key]['ReqAuditoria'];
                            }
                            /*se adjunta los elementos relacionados al requerimiento*/
                            if (isset($request->TratOfertado) && $key == $request->TratOfertado) {
                                $requerimiento->ofertado=1;
                            }else{
                                $requerimiento->ofertado=0;
                            }
                            $requerimiento->FK_ReqRespel=$respel->ID_Respel;
                            $requerimiento->FK_ReqTrata=$opciones[$key]['Tratamiento'];
                            $requerimiento->ReqSlug= hash('md5', rand().time().$respel->ID_Respel);
                            $requerimiento->save();

                            if (isset($opciones[$key]['Pretratamientos'])) {
                                $requerimiento->pretratamientosSelected()->attach($opciones[$key]['Pretratamientos']);
                            }
                            /*se verifica que las tarifas no esten disabled en la vista*/
                            if (isset($opciones[$key]['TarifaFrecuencia'])) {
                                $tarifa = new Tarifa();
                                $tarifa->TarifaFrecuencia=$opciones[$key]['TarifaFrecuencia'];
                                $tarifa->TarifaVencimiento=$opciones[$key]['TarifaVencimiento'];
                                $tarifa->Tarifatipo=$opciones[$key]['Tarifatipo'];
                                $tarifa->TarifaDelete=0;
                                $tarifa->FK_TarifaReq=$requerimiento->ID_Req;
                                $tarifa->save();

                                foreach ($opciones[$key]['TarifaDesde'] as $key2 => $value2) {
                                   if ($opciones[$key]['TarifaPrecio'][$key2] != null) {
                                       $rango = new Rango();
                                       $rango->TarifaPrecio=$opciones[$key]['TarifaPrecio'][$key2];
                                       $rango->TarifaDesde=$opciones[$key]['TarifaDesde'][$key2];
                                       $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
                                       $rango->save();
                                   }
                                }
                            }
                        }
                    }
                }
            }
        }
        $respel->RespelStatus = $request['RespelStatus'];
        $respel->RespelStatusDescription = $request['RespelStatusDescription'];
        $respel->save();

        /*auditoria de la actualizacion*/
        $log = new audit();
        $log->AuditTabla="respels requerimiento y tarifas";
        $log->AuditType="Evaluacion Updated";
        $log->AuditRegistro=$respel->ID_Respel;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($request->all());
        $log->save();

        if($respel->RespelStatus === "Aprobado"){
            // new  RespelMail($slug);
            return redirect()->route('email-respel', [$respel->RespelSlug]);
        }
        return redirect()->route('respels.edit', [$respel->RespelSlug]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function updateTDE(Request $request, $id)
    {
        $respel = Respel::where('RespelSlug', $id)->first();
        if (!$respel) {
            abort(404);
        }
        // return $request;
        /*verificar si se cargo un documento en este campo*/
            if (isset($request['RespelTarj'])) {
                if($respel->RespelTarj <> null && file_exists(public_path().'/img/TarjetaEmergencia/'.$respel->RespelTarj)){
                    // unlink(public_path().'/img/TarjetaEmergencia/'.$respel->RespelTarj);
                }
                $file2 = $request['RespelTarj'];
                $tarj = hash('sha256', rand().time().$file2->getClientOriginalName()).'.pdf';
                $file2->move(public_path().'/img/TarjetaEmergencia/',$tarj);
                // Auto-aprobación para Dirección Técnica al actualizar TDE en editTDE
                $newTDE = (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA))
                    ? "Aprobado"
                    : "TDE actualizada";
            }else{
                $tarj = $respel->RespelTarj;
                $newTDE = $respel->RespelStatus;
            }
        $respel->RespelTarj = $tarj;
        $respel->RespelStatus = $newTDE;
        $respel->save();

        $log = new audit();
        $log->AuditTabla="respels";
        $log->AuditType="Eliminado";
        $log->AuditRegistro=$respel->ID_Respel;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog="actualizada la tarjeta de emergencia";
        $log->save();

        return redirect()->route('respels.index');
    }

    public function vencidos()
    {
        if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)) {
            $user = Auth::user()->UsRol;
            $requerimientos = Requerimiento::with(['respel.cotizacion.sede.clientes', 'tarifa.rangos'])
            ->where('ofertado', '1')->get();
            /*$requerimientos['personal'] = Personal::all();*/
            $personals = Personal::all();
            /*return $personal;*/
            return view ('respels.vencidos', compact('requerimientos', 'user', 'personals'));

        }else{
            abort(403);
        }
    }
    /* Display a listing of the resource (Residuos Express).
     * Filtros: nombre, cliente, año (igual que residuos regulares).
     *
     * @return \Illuminate\Http\Response
     */
    public function indexExpress(Request $request)
    {
        $baseQuery = DB::table('respels')
            ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
            ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
            ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
            ->join('personals', 'personals.ID_Pers', '=', 'clientes.CliComercial')
            ->select('respels.*', 'clientes.CliName', 'clientes.CliComercial', 'clientes.CliCategoria', 'personals.PersEmail', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersCellphone')
            ->where(function ($q) {
                switch (Auth::user()->UsRol) {
                    case 'Cliente':
                        $UserSedeID = DB::table('personals')
                            ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                            ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                            ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                            ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                            ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                            ->value('clientes.ID_Cli');
                        $q->where('respels.RespelDelete', 0)
                            ->where('respels.RespelPublic', 0)
                            ->where('clientes.ID_Cli', $UserSedeID)
                            ->where('clientes.CliDelete', 0);
                        break;
                    case 'Comercial':
                        $ComercialAsignado = DB::table('personals')
                            ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                            ->value('personals.ID_Pers');
                        $q->where('respels.RespelDelete', 0)
                            ->where('respels.RespelPublic', 0)
                            ->where('clientes.CliComercial', $ComercialAsignado)
                            ->where('clientes.CliDelete', 0);
                        break;
                    default:
                        $q->where('respels.RespelDelete', 0)
                            ->where('respels.RespelPublic', 0)
                            ->where('clientes.CliDelete', 0);
                        break;
                }
            })
            ->where('clientes.CliCategoria', 'ClientePrepago');

        if ($request->filled('nombre')) {
            $baseQuery->where('respels.RespelName', 'like', '%' . $request->nombre . '%');
        }
        if ($request->filled('cliente')) {
            $baseQuery->where('clientes.ID_Cli', $request->cliente);
        }
        if ($request->filled('anio')) {
            $baseQuery->whereYear('respels.created_at', $request->anio);
        }

        $buscar = $request->has('buscar') && $request->buscar == '1';
        $verTodos = $request->has('ver') && $request->ver === 'todos';
        $Respels = ($buscar || $verTodos) ? $baseQuery->orderBy('respels.updated_at', 'desc')->get() : collect();

        foreach ($Respels as $key => $value) {
            $requerimiento = Requerimiento::where('FK_ReqRespel', $Respels[$key]->ID_Respel)
                ->where('forevaluation', 1)
                ->where('ofertado', 1)
                ->first();
            if (isset($requerimiento->FK_ReqTrata) && $requerimiento->ofertado == 1) {
                $tratamiento = Tratamiento::where('ID_Trat', $requerimiento->FK_ReqTrata)->first();
                $Respels[$key]->TratName = $tratamiento ? $tratamiento->TratName : '';
            } else {
                $Respels[$key]->TratName = '';
            }
        }

        foreach ($Respels as $respel) {
            if (!isset($respel->TratName)) {
                $respel->TratName = '';
            }
        }

        $clientesFiltro = collect();
        $aniosFiltro = collect();
        if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol, Permisos::COMERCIALAP) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALAP)) {
            $qClientes = DB::table('respels')
                ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
                ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
                ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
                ->where('respels.RespelDelete', 0)
                ->where('respels.RespelPublic', 0)
                ->where('clientes.CliCategoria', 'ClientePrepago')
                ->where('clientes.CliDelete', 0);
            if (Auth::user()->UsRol == 'Comercial') {
                $ComercialAsignado = DB::table('personals')->where('personals.ID_Pers', Auth::user()->FK_UserPers)->value('personals.ID_Pers');
                $qClientes->where('clientes.CliComercial', $ComercialAsignado);
            }
            $clientesFiltro = $qClientes->select('clientes.ID_Cli', 'clientes.CliName')
                ->distinct()
                ->orderBy('clientes.CliName')
                ->get();
        }
        $aniosFiltro = DB::table('respels')
            ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
            ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
            ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
            ->where('respels.RespelDelete', 0)
            ->where('respels.RespelPublic', 0)
            ->where('clientes.CliCategoria', 'ClientePrepago')
            ->where('clientes.CliDelete', 0)
            ->select(DB::raw('DISTINCT YEAR(respels.created_at) as anio'))
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->filter();

        return view('respels.indexExpress', compact('Respels', 'clientesFiltro', 'aniosFiltro'));
    }

    // Método auxiliar para cargar residuos Express por año con paginación (legacy, rutas por año)
    private function getRespelsByYear($year, $perPage = 50) {
        // Construir la consulta base con eager loading optimizado
        $query = Respel::with([
            'requerimientos' => function($q) {
                $q->where('forevaluation', 1)
                  ->where('ofertado', 1)
                  ->with('tratamiento:ID_Trat,TratName');
            }
        ])
        ->join('cotizacions', 'cotizacions.ID_Coti', '=', 'respels.FK_RespelCoti')
        ->join('sedes', 'sedes.ID_Sede', '=', 'cotizacions.FK_CotiSede')
        ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
        ->join('personals', 'personals.ID_Pers', '=', 'clientes.CliComercial')
        ->select('respels.*', 'clientes.CliName', 'clientes.CliComercial', 'clientes.CliCategoria', 'personals.PersEmail', 'personals.PersFirstName', 'personals.PersLastName', 'personals.PersCellphone')
        ->where(function($query){
            switch (Auth::user()->UsRol) {
                case 'Cliente':
                    $UserSedeID = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->value('clientes.ID_Cli');
                    $query->where('respels.RespelDelete',0);
                    $query->where('respels.RespelPublic',0);
                    $query->where('clientes.ID_Cli', $UserSedeID);
                    $query->where('clientes.CliDelete', 0);
                    break;

                case 'Comercial':
                    $ComercialAsignado = DB::table('personals')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->value('personals.ID_Pers');
                    $query->where('respels.RespelDelete',0);
                    $query->where('respels.RespelPublic',0);
                    $query->where('clientes.CliComercial', $ComercialAsignado);
                    $query->where('clientes.CliDelete', 0);
                    break;

                default:
                    $query->where('respels.RespelDelete',0);
                    $query->where('respels.RespelPublic',0);
                    $query->where('clientes.CliDelete', 0);
                    break;
            }
        })
        ->where('clientes.CliCategoria', 'ClientePrepago')
        ->whereYear('respels.created_at', $year)
        ->orderBy('respels.updated_at', 'desc');

        // Aplicar paginación
        $Respels = $query->paginate($perPage);

        // Procesar TratName usando las relaciones cargadas
        $Respels->getCollection()->transform(function ($respel) {
            $requerimiento = $respel->requerimientos->first();
            if ($requerimiento && $requerimiento->tratamiento) {
                $respel->TratName = $requerimiento->tratamiento->TratName;
            } else {
                $respel->TratName = '';
            }
            return $respel;
        });

        return $Respels;
    }

    public function respelExpress2020() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2020]);
    }
    public function respelExpress2021() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2021]);
    }
    public function respelExpress2022() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2022]);
    }
    public function respelExpress2023() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2023]);
    }
    public function respelExpress2024() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2024]);
    }
    public function respelExpress2025() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2025]);
    }
    public function respelExpress2026() {
        return redirect()->route('respels.indexExpress', ['buscar' => 1, 'anio' => 2026]);
    }

  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function createrespelcliente()
     {

        if(in_array(Auth::user()->UsRol, Permisos::CLIENTE)||in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA)){
           $Sede = DB::table('clientes')
           ->join('sedes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
           ->select('sedes.ID_Sede', 'sedes.SedeName','clientes.CliName')
           ->where('clientes.ID_Cli', '<>', 1)
           ->get();

           // Obtener tratamientos con su gestor asociado
           $tratamientos = DB::table('tratamientos')
               ->join('sedes', 'tratamientos.FK_TratProv', '=', 'sedes.ID_Sede')
               ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
               ->select(
                   'tratamientos.*',
                   'clientes.CliShortname',
                   'clientes.CliName'
               )
               ->where('tratamientos.TratDelete', 0)
               ->orderBy('tratamientos.TratName')
               ->get();
           // ID del tratamiento de termodestrucción (ID 1 según la base de datos)
           $termodestruccionId = 1;
           
           $categories = Categoryrespelpublic::all();
           //return $Sede;
           return view('solicitud-serv.Createrespel', compact('Sede', 'tratamientos', 'categories', 'termodestruccionId'));
        }
 }



/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

public function storenewrespel(RespelStoreRequest $request)
    {


        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)) {
            $UserSedeID = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->value('sedes.ID_Sede');

        }else{
            $UserSedeID = $request->input('Sede');
        }
        // return $request;
        if ($request['FK_SubCategoryRP'] != 1) {
            $subcategoria = Subcategoryrespelpublic::where('ID_SubCategoryRP',  $request['FK_SubCategoryRP'])->first();
            if (!$subcategoria) {
                $subcategoria = new Subcategoryrespelpublic();
                $subcategoria->SubCategoryRpName = '0';
            }
        }else{
            $subcategoria = new Subcategoryrespelpublic();
            $subcategoria->SubCategoryRpName = '0';
        }



        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)|| in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA)){
            /*se crea un nueva cotizacion solo si el cliente no tiene cotizaciones pendientes*/
            $Cotizacion = new Cotizacions();
            $Cotizacion->CotiNumero = 7;
            $Cotizacion->CotiFechaSolicitud = now();
            $Cotizacion->CotiDelete = 0;
            $Cotizacion->CotiStatus = "Aprobada";
            $Cotizacion->FK_CotiSede = $UserSedeID;
            $Cotizacion->save();
        }


        for ($x=0; $x < count($request['RespelName']); $x++) {
            /*validar si el formulario incluye archivos de tarjeta de emergencia u hoja de seguridad*/
            $respel = new Respel();

            if (isset($request['RespelHojaSeguridad'][$x])) {
                $file1 = $request['RespelHojaSeguridad'][$x];
                $hoja = hash('sha256', rand().time().$file1->getClientOriginalName()).'.pdf';

                $file1->move(public_path().'/img/HojaSeguridad/',$hoja);
            }
            else{
                $hoja = 'RespelHojaDefault.pdf';
            }

             /*verificar si se cargo un documento en este campo*/
            if (isset($request['RespelTarj'][$x])) {
                $file2 = $request['RespelTarj'][$x];
                $tarj = hash('sha256', rand().time().$file2->getClientOriginalName()).'.pdf';
                $file2->move(public_path().'/img/TarjetaEmergencia/',$tarj);
            }else{
                $tarj = 'RespelTarjetaDefault.pdf';
            }

             /*verificar si se cargo un documento en este campo*/
            if (isset($request['RespelFoto'][$x])) {
                $file3 = $request['RespelFoto'][$x];
                $foto = hash('sha256', rand().time().$file3->getClientOriginalName()).'.png';
                $file3->move(public_path().'/img/fotoRespelCreate/',$foto);
            }else{
                $foto = 'RespelFotoDefault.png';
            }

            /*verificar si se cargo un documento en este campo*/
            if (isset($request['SustanciaControladaDocumento'][$x])) {
                $file4 = $request['SustanciaControladaDocumento'][$x];
                $ctrlDoc = hash('sha256', rand().time().$file4->getClientOriginalName()).'.pdf';
                $file4->move(public_path().'/img/SustanciaControlDoc/',$ctrlDoc);
            }else{
                $ctrlDoc = 'SustanciaControlDocDefault.pdf';
            }

            /*se verifica el rol de usuario para asignar un status al residuo*/
            // FALTA antes: Programador y cliente

            // if (in_array(Auth::user()->UsRol, Permisos::CLIENTEYADMINS)) {
            //     $statusinicial="Pendiente";
            // }
            $respel->RespelName = $request['RespelName'][$x];
            $respel->RespelDescrip = $request['RespelDescrip'][$x];
            $respel->RespelIgrosidad = $request['RespelIgrosidad'][$x];
            $respel->YRespelClasf4741 = $request['YRespelClasf4741'][$x];
            $respel->ARespelClasf4741 = $request['ARespelClasf4741'][$x];
            $respel->RespelEstado = $request['RespelEstado'][$x];

            // se verifica si el residuo esta marcada como aceite usado
            if(isset($request['AceiteUsado'][$x])&&($request['AceiteUsado'][$x]==1)){
                $respel->AceiteUsado = $request['AceiteUsado'][$x];

            }else {
                $respel->AceiteUsado = 0;
            }



            // se verifica si la sustancia esta marcada como controlada
            if (isset($request['SustanciaControlada'][$x])&&($request['SustanciaControlada'][$x]==1)) {
                $respel->SustanciaControlada = $request['SustanciaControlada'][$x];
                $respel->SustanciaControladaTipo = $request['SustanciaControladaTipo'][$x];
                $respel->SustanciaControladaNombre = $request['SustanciaControladaNombre'][$x];
                $respel->SustanciaControladaDocumento = $ctrlDoc;
            }else{
                $respel->SustanciaControlada = 0;
            }
            if ($request['FK_SubCategoryRP'] == 'Agregado-Manual') {
                $respel->RespelStatus = "Aprobado";
            }else{
                $respel->RespelStatus = "Aprobado";
            }
            // $respel->RespelStatus = $statusinicial;
            $respel->RespelHojaSeguridad = $hoja;
            $respel->RespelTarj = $tarj;
            $respel->RespelFoto = $foto;
            if (in_array(Auth::user()->UsRol, Permisos::CLIENTE)|| in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA)){
                $respel->FK_RespelCoti = $Cotizacion->ID_Coti;
                $respel->RespelPublic = 0;
            }else{
                $respel->FK_RespelCoti = 1;
                $respel->RespelPublic = 1;
                $respel->FK_SubCategoryRP = $request['FK_SubCategoryRP'];
            }
            $respel->RespelSlug = hash('sha256', rand().time().$respel->RespelName);
            $respel->RespelDelete = 0;
            $respel->RespelDeclaracion = $request['RespelDeclaracion'][$x];
            $respel->save();

            $requerimiento = new Requerimiento();
            $requerimiento->ofertado=1;
            $requerimiento->FK_ReqRespel=$respel->ID_Respel;
            $requerimiento->forevaluation=1;
            $requerimiento->FK_ReqTrata=$request['RespelTratamiento'];
            $requerimiento->ReqSlug= hash('md5', rand().time().$respel->ID_Respel);
            $requerimiento->save();

            $tratamiento = Tratamiento::where('ID_Trat', $request['RespelTratamiento'])->first();

            $tarifa = new Tarifa();
            $tarifa->TarifaFrecuencia='Servicio';
            $tarifa->TarifaVencimiento='2025-11-15';
            if ($tratamiento->TratName == 'Posconsumo luminarias') {
                $tarifa->Tarifatipo='Unid';
            }else{
                $tarifa->Tarifatipo='Kg';
            }
            $tarifa->TarifaDelete=0;
            $tarifa->FK_TarifaReq=$requerimiento->ID_Req;
            $tarifa->save();

            $rango = new Rango();
            $rango->TarifaPrecio=1500;
            $rango->TarifaDesde=1;
            $rango->FK_RangoTarifa=$tarifa->ID_Tarifa;
            $rango->save();

            if($respel->RespelStatus === "Pendiente"){
                /*se verifican los datos de las sede y y cliente segun el usuarios que registra el residuo*/
                $respel['cliente'] = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->select(['sedes.SedeName', 'clientes.CliName', 'clientes.CliComercial'])
                    ->first();

                if ($respel['cliente']->CliComercial <> null) {
                    $comercial = Personal::where('ID_Pers', $respel['cliente']->CliComercial)->first();
                } else {
                    $comercial = "";
                }

                $respel['comercial'] = $comercial;
                $respel['personalcliente'] = Personal::where('ID_Pers', Auth::user()->FK_UserPers)->first();


                // se envia un correo por cada residuo registrado
               // Mail::to(self::MAIL_RESIDUOS_INTERNO)->send(new ResiduoNuevo($respel));
                // return new ResiduoNuevo($respel);
            }
        }

        $log = new audit();
        $log->AuditTabla="respels";
        $log->AuditType="Nuevo respel";
        $log->AuditRegistro=$respel->ID_Respel;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($request->all());
        $log->save();

        if (isset($subcategoria->SubCategoryRpName)) {
            if ($subcategoria->SubCategoryRpName == 'Agregado-Manual') {
                return redirect()->route('respels.index')->with('success', 'Residuo creado satisfactoriamente');
            }else{
                return redirect()->route('respels.index')->with('success', 'Residuo creado satisfactoriamente');
            }
        }else{
            return redirect()->route('respels.index')->with('success', 'Residuo creado satisfactoriamente');
        }
    }
}

