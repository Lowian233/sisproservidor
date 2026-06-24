<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\auditController;
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteExpressUpdateRequest;
use App\Http\Controllers\userController;
use App\AuditRequest;
use App\Departamento;
use App\Municipio;
use App\Cliente;
use App\Generador;
use App\GenerSede;
use App\audit;
use App\Sede;
use App\Area;
use App\Cargo;
use App\Personal;
use App\User;
use App\Permisos;
use App\RequerimientosCliente;


class ClientController extends Controller
{
    protected $table = 'clientes';

    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        switch (true) {
            case (in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)):
                // $clientes = Cliente::where('CliCategoria', 'Cliente')->get();
                $clientes = DB::table('clientes')
                    ->leftjoin('personals', 'clientes.CliComercial', '=', 'personals.ID_Pers')
                    ->select('clientes.*', 'personals.PersFirstName','personals.PersLastName', DB::raw('COALESCE(clientes.provedor, 0) as provedor'))
                    ->where('clientes.CliDelete', 0)
                    ->where('clientes.CliCategoria', 'Cliente')
                    ->whereRaw("(clientes.CliNit IS NULL OR clientes.CliNit NOT LIKE 'PEND%')")
                    ->get();
                 $personals = DB::table('personals')
                        ->rightjoin('users', 'personals.ID_Pers', '=', 'users.FK_UserPers')
                        ->select('personals.*')
                        ->where('personals.PersDelete', 0)
                        ->where('users.UsRol', 'Comercial')
                        ->orWhere('users.UsRol2', 'Comercial')
                        ->orWhere('users.UsRol2', 'Comercialap')
                        ->orWhere('users.UsRol2', 'Programador')
                        ->get();
                return view('clientes.index', compact('clientes', 'personals'));
                break;

            case (in_array(Auth::user()->UsRol, Permisos::CLIENTE)):
                return redirect()->route('home');
                break;
            case (in_array(Auth::user()->UsRol, Permisos::COMERCIAL)):
                $clientes = Cliente::where('CliDelete', 0)->whereIn('CliCategoria', ['Cliente', 'ClientePrepago'])->where('CliComercial', Auth::user()->FK_UserPers)->excluirProspectos()->get();
                return view('clientes.index', compact('clientes'));
                break;
            case (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)):
                $clientes = DB::table('clientes')
                    ->leftjoin('personals', 'clientes.CliComercial', '=', 'personals.ID_Pers')
                    ->select('clientes.*', 'personals.PersFirstName','personals.PersLastName', DB::raw('COALESCE(clientes.provedor, 0) as provedor'))
                    ->where('clientes.CliDelete', 0)
                    ->where('clientes.CliCategoria', 'Cliente')
                    ->whereRaw("(clientes.CliNit IS NULL OR clientes.CliNit NOT LIKE 'PEND%')")
                    ->get();
                $personals = '';
                if(in_array(Auth::user()->UsRol, Permisos::AsigComercial) || in_array(Auth::user()->UsRol2, Permisos::AsigComercial)){
                    $personals = DB::table('personals')
                        ->rightjoin('users', 'personals.ID_Pers', '=', 'users.FK_UserPers')
                        ->select('personals.*')
                        ->where('personals.PersDelete', 0)
                        ->where('users.UsRol', 'Comercial')
                        ->orWhere('users.UsRol2', 'Comercialap')
                        ->orWhere('users.UsRol2', 'Programador')
                        ->get();
                }
                return view('clientes.index', compact('clientes', 'personals'));
                break;
            /*case (in_array(Auth::user()->UsRol, Permisos::AdministradorPlanta)):   
                $clientes = DB::table('clientes')
                    ->leftjoin('personals', 'clientes.CliComercial', '=', 'personals.ID_Pers')
                    ->select('clientes.*', 'personals.PersFirstName','personals.PersLastName')
                    ->where('CliDelete', 0)
                    ->where('CliCategoria', 'Cliente')
                    ->get();

                $personals = DB::table('personals')
                        ->rightjoin('users', 'personals.ID_Pers', '=', 'users.FK_UserPers')
                        ->select('personals.*')
                        ->where('personals.PersDelete', 0)
                        ->where('users.UsRol', 'Comercial')
                        ->orWhere('users.UsRol2', 'Comercial')
                        ->get();
                return view('clientes.index', compact('clientes', 'personals'));
                break;    
                    */    
            default:
                abort(403);
        }
    }
    public function changeComercial(Request $request, $id){
        $cliente = Cliente::where('CliSlug', $id)->first();
        $cliente->CliComercial = $request->input('Comercial');
        $cliente->CliShortname = $request->input('CliShortname');
        $cliente->save();
        return back();
    }

    /**
     * Toggle provedor status for a cliente
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function toggleProveedor($slug)
    {
        // Solo usuarios de Prosarc pueden cambiar el estado de proveedor
        if (!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $cliente = Cliente::where('CliSlug', $slug)->firstOrFail();
        
        // Toggle: si es 0 pasa a 1, si es 1 pasa a 0
        $cliente->provedor = $cliente->provedor ? 0 : 1;
        $cliente->save();

        // Registrar en auditoría
        $log = new audit();
        $log->AuditTabla = "clientes";
        $log->AuditType = "Modificado";
        $log->AuditRegistro = $cliente->CliSlug;
        $log->AuditUser = Auth::user()->email;
        $log->Auditlog = ['provedor' => $cliente->provedor];
        $log->save();

        return back()->with('success', $cliente->provedor ? 'Cliente marcado como proveedor.' : 'Cliente desmarcado como proveedor.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rol  = trim((string) (Auth::user()->UsRol ?? ''));
        $rol2 = trim((string) (Auth::user()->UsRol2 ?? ''));

        // Permitir acceso a cualquier persona de Prosarc O a clientes sin personal asociado
        $esProSarc = in_array($rol, Permisos::TODOPROSARC) || in_array($rol2, Permisos::TODOPROSARC);
        $esClienteSinPersonal = (in_array($rol, Permisos::CLIENTE) || in_array($rol2, Permisos::CLIENTE)) && Auth::user()->FK_UserPers === NULL;

        if($esProSarc || $esClienteSinPersonal){
            $Departamentos = Departamento::all();
            $comerciales = DB::table('personals')
            ->rightjoin('users', 'personals.ID_Pers', '=', 'users.FK_UserPers')
            ->select('personals.*')
            ->where('personals.PersDelete', 0)
            ->where(function($query) {
                $query->where('users.UsRol', 'Comercial')
                      ->orWhere('users.UsRol2', 'Comercial')
                      ->orWhere('users.UsRol2', 'Comercialap')
                      ->orWhere('users.UsRol2', 'Programador');
            })
            ->get();

            // Solo auto-asignar si el usuario ES comercial
            $comercialAsignado = null;
            $esComercial = in_array($rol, Permisos::COMERCIAL) || in_array($rol2, Permisos::COMERCIAL) ||
                           in_array($rol, Permisos::COMERCIALAP) || in_array($rol2, Permisos::COMERCIALAP);
            if($esComercial && Auth::user()->FK_UserPers !== null){
                $comercialAsignado = Auth::user()->FK_UserPers;
            }

            if (old('FK_SedeMun') !== null){
                $Municipios = Municipio::select()->where('FK_MunCity', old('departamento'))->get();
            }
            return view('clientes.create2', compact('Departamentos', 'comerciales', 'comercialAsignado'));
        }else{
            abort(403, 'No tiene permisos para crear clientes.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClienteStoreRequest $request)
    {
        $rol  = trim((string) (Auth::user()->UsRol ?? ''));
        $rol2 = trim((string) (Auth::user()->UsRol2 ?? ''));

        // Permitir acceso a cualquier persona de Prosarc O a clientes sin personal asociado
        $esProSarc = in_array($rol, Permisos::TODOPROSARC) || in_array($rol2, Permisos::TODOPROSARC);
        $esClienteSinPersonal = (in_array($rol, Permisos::CLIENTE) || in_array($rol2, Permisos::CLIENTE)) && Auth::user()->FK_UserPers === NULL;

        if($esProSarc || $esClienteSinPersonal){
            $Cliente = new Cliente();
            $Cliente->CliNit = $request->input('CliNit');
            $Cliente->CliName = $request->input('CliName');
            // $Cliente->CliShortname = $request->input('CliName');
            $Cliente->CliCategoria = 'Cliente';
            $Cliente->CliSlug = hash('sha256', rand().time().$Cliente->CliShortname);
            $Cliente->CliDelete = 0;
            // Solo auto-asignar si el usuario ES comercial
            $esComercial = in_array($rol, Permisos::COMERCIAL) || in_array($rol2, Permisos::COMERCIAL) ||
                           in_array($rol, Permisos::COMERCIALAP) || in_array($rol2, Permisos::COMERCIALAP);
            if($esComercial && Auth::user()->FK_UserPers !== null){
                $Cliente->CliComercial = Auth::user()->FK_UserPers;
            } else {
                $Cliente->CliComercial = $request->input('CliComercial');
            }
            $Cliente->CliShortname = $request->input('CliName');
            $Cliente->CliStatus = 'Autorizado';
            $Cliente->TipoFacturacion = 'Credito';
            $Cliente->CliProcedencia = $request->input('CliProcedencia');
            // $Folder = $request->input('CliShortname');
            // if ($request->hasfile('CliRut')){
            //     $Rut = 'Rut - '.date('j-m-y').hash('sha256', rand().time().$request->CliRut->getClientOriginalName()).'.'.$request->CliRut->extension();
            //     $request->CliRut->move(public_path('/img/DatosClientes/').$Folder,$Rut);
            //     $Cliente->CliRut = $Folder.'/'.$Rut;
            // }
            // if ($request->hasfile('CliCamaraComercio')){
            //     $CamaraComercio = 'Camara de Comercio - '.date('j-m-y').hash('sha256', rand().time().$request->CliCamaraComercio->getClientOriginalName()).'.'.$request->CliCamaraComercio->extension();
            //     $request->CliCamaraComercio->move(public_path('/img/DatosClientes/').$Folder,$CamaraComercio);
            //     $Cliente->CliCamaraComercio = $Folder.'/'.$CamaraComercio;
            // }
            // if ($request->hasfile('CliRepresentanteLegal')){
            //     $RepresentanteLegal = 'Representante Legal - '.date('j-m-y').hash('sha256', rand().time().$request->CliRepresentanteLegal->getClientOriginalName()).'.'.$request->CliRepresentanteLegal->extension();
            //     $request->CliRepresentanteLegal->move(public_path('/img/DatosClientes/').$Folder,$RepresentanteLegal);
            //     $Cliente->CliRepresentanteLegal = $Folder.'/'.$RepresentanteLegal;
            // }
            // if ($request->hasfile('CliCertificaionComercial')){
            //     $CertificacionComercial = 'Certificacion Comercial - '.date('j-m-y').hash('sha256', rand().time().$request->CliCertificaionComercial->getClientOriginalName()).'.'.$request->CliCertificaionComercial->extension();
            //     $request->CliCertificaionComercial->move(public_path('/img/DatosClientes/').$Folder,$CertificacionComercial);
            //     $Cliente->CliCertificaionComercial = $Folder.'/'.$CertificacionComercial;
            // }
            // if ($request->hasfile('CliCertificaionComercial2')){
            //     $CertificacionComercial2 = 'Certificacion Comercial - '.date('j-m-y').hash('sha256', rand().time().$request->CliCertificaionComercial2->getClientOriginalName()).'.'.$request->CliCertificaionComercial2->extension();
            //     $request->CliCertificaionComercial2->move(public_path('/img/DatosClientes/').$Folder,$CertificacionComercial2);
            //     $Cliente->CliCertificaionComercial2 = $Folder.'/'.$CertificacionComercial2;
            // }
            // if ($request->hasfile('CliCertificaionBancaria')){
            //     $CertificacionBancaria = 'Certificacion Bancaria - '.date('j-m-y').hash('sha256', rand().time().$request->CliCertificaionBancaria->getClientOriginalName()).'.'.$request->CliCertificaionBancaria->extension();
            //     $request->CliCertificaionBancaria->move(public_path('/img/DatosClientes/').$Folder,$CertificacionBancaria);
            //     $Cliente->CliCertificaionBancaria = $Folder.'/'.$CertificacionBancaria;
            // }
            $Cliente->save();

            $Sede = new Sede();
            $Sede->SedeName = $request->input('SedeName');
            $Sede->SedeAddress = $request->input('SedeAddress');
            $Sede->SedePhone1 = $request->input('SedePhone1');
            if($request->input('SedePhone1') === null && $request->input('SedePhone2') !== null){
                $Sede->SedeExt1 = $request->input('SedeExt2');
                $Sede->SedePhone1 = $request->input('SedePhone2');
            }else{
                if($request->input('SedePhone1') === null){
                    $Sede->SedeExt1 = null;
                }else{
                    $Sede->SedePhone1 = $request->input('SedePhone1');
                    $Sede->SedeExt1 = $request->input('SedeExt1');
                }
                if($request->input('SedePhone2') === null){
                    $Sede->SedeExt2 = null;
                }else{
                    $Sede->SedePhone2 = $request->input('SedePhone2');
                    $Sede->SedeExt2 = $request->input('SedeExt2');
                }
            }
            $Sede->SedeEmail = $request->input('SedeEmail');
            $Sede->SedeCelular = $request->input('SedeCelular');
            $Sede->SedeSlug = hash('sha256', rand().time().$Sede->SedeName);
            $Sede->FK_SedeCli = $Cliente->ID_Cli;
            $Sede->FK_SedeMun = $request->input('FK_SedeMun');
            $Sede->SedeDelete = 0;
            $Sede->save();

            $requerimiento = new RequerimientosCliente();
            $requerimiento->RequeCliBascula = 0;
            $requerimiento->RequeCliCapacitacion = 0;
            $requerimiento->RequeCliMasPerson = 0;
            $requerimiento->RequeCliVehicExclusive = 0;
            $requerimiento->RequeCliPlatform = 0;
            $requerimiento->FK_RequeClient = $Cliente->ID_Cli;
            $requerimiento->save();

            $Area = new Area();
            $Area->AreaName = $request->input("AreaName");
            $Area->FK_AreaSede = $Sede->ID_Sede;
            $Area->AreaDelete = 0;
            $Area->AreaSlug = hash('sha256', rand().time().$Area->AreaName);
            $Area->save();

            $Cargo = new Cargo();
            $Cargo->CargName = $request->input("CargName");
            $Cargo->CargArea =  $Area->ID_Area;
            $Cargo->CargDelete =  0;
            $Cargo->CargSlug = hash('sha256', rand().time().$Cargo->CargName);
            $Cargo->save();

            $Personal = new Personal();
            $Personal->PersFirstName = $request->input("PersFirstName");
            $Personal->PersLastName = $request->input("PersLastName");
            $Personal->PersEmail = $request->input("PersEmail");
            $Personal->PersSecondName = $request->input("PersSecondName");
            $Personal->PersDocType = $request->input("PersDocType");
            $Personal->PersDocNumber = $request->input("PersDocNumber");
            $Personal->PersCellphone = $request->input("PersCellphone");
            $Personal->PersType = 1;
            $Personal->PersSlug = hash('sha256', rand().time().$Personal->PersFirstName);
            $Personal->PersDelete = 0;
            $Personal->PersFactura = 1;
            $Personal->PersAdmin = 1;
            $Personal->FK_PersCargo = $Cargo->ID_Carg;
            $Personal->save();

            // Si es un cliente sin personal, asociar el usuario actual con el personal creado
            if($esClienteSinPersonal){
                $user = User::where('id', Auth::user()->id)->first();
                $user->FK_UserPers = $Personal->ID_Pers;
                $user->save();
                
                // Crear generador y sede del generador para cliente sin personal
                $Gener = new Generador();
                $Gener->GenerNit = $Cliente->CliNit;
                $Gener->GenerName = $Cliente->CliName;
                $Gener->GenerSlug = hash('sha256', rand().time().$Gener->GenerName);
                $Gener->FK_GenerCli = $Sede->ID_Sede;
                $Gener->GenerDelete = $Cliente->CliDelete;
                $Gener->save();

                $SGener = new GenerSede();
                $SGener->GSedeName = $Sede->SedeName;
                $SGener->GSedeAddress = $Sede->SedeAddress;
                $SGener->GSedePhone1 = $Sede->SedePhone1;
                $SGener->GSedeEmail = $Personal->PersEmail;
                $SGener->GSedeCelular = $Personal->PersCellphone;
                $SGener->GSedeSlug = hash('sha256', rand().time().$SGener->GSedeName);
                $SGener->FK_GSede = $Gener->ID_Gener;
                $SGener->FK_GSedeMun = $Sede->FK_SedeMun;
                $SGener->GSedeDelete = $Sede->SedeDelete;
                $SGener->save();
                
                return redirect()->route('cliente-show', [$Cliente->CliSlug]);
            } else if($esProSarc){
                // Si es personal de Prosarc, redirigir al formulario para crear usuario, generador y sede del generador
                return redirect()->route('clientes.createUsuarioGenerador', [$Cliente->CliSlug]);
            }
           // return redirect()->route('cliente-show', ['cliente' => $Cliente->CliSlug]);
        }
    }

    /**
     * Show the form for creating usuario and generador after cliente creation.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function createUsuarioGenerador($slug)
    {
        $rol  = trim((string) (Auth::user()->UsRol ?? ''));
        $rol2 = trim((string) (Auth::user()->UsRol2 ?? ''));
        $esProSarc = in_array($rol, Permisos::TODOPROSARC) || in_array($rol2, Permisos::TODOPROSARC);

        if(!$esProSarc){
            abort(403, 'No tiene permisos para acceder a esta función.');
        }

        $cliente = Cliente::where('CliSlug', $slug)->firstOrFail();
        
        // Obtener el personal del cliente
        $personal = Personal::whereHas('cargo.area.sede', function($query) use ($cliente) {
            $query->where('FK_SedeCli', $cliente->ID_Cli);
        })->where('PersDelete', 0)->first();

        if(!$personal){
            return redirect()->route('cliente-show', [$cliente->CliSlug])->with('error', 'No se encontró el personal del cliente.');
        }

        // Verificar que el usuario no exista ya
        $usuarioExistente = User::where('FK_UserPers', $personal->ID_Pers)->first();
        if($usuarioExistente){
            return redirect()->route('cliente-show', [$cliente->CliSlug])->with('info', 'El usuario ya fue creado para este cliente.');
        }

        $sede = Sede::where('FK_SedeCli', $cliente->ID_Cli)->where('SedeDelete', 0)->first();
        
        return view('clientes.createUsuarioGenerador', compact('cliente', 'personal', 'sede'));
    }

    /**
     * Store usuario, generador and sede del generador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function storeUsuarioGenerador(Request $request, $slug)
    {
        $rol  = trim((string) (Auth::user()->UsRol ?? ''));
        $rol2 = trim((string) (Auth::user()->UsRol2 ?? ''));
        $esProSarc = in_array($rol, Permisos::TODOPROSARC) || in_array($rol2, Permisos::TODOPROSARC);

        if(!$esProSarc){
            abort(403, 'No tiene permisos para acceder a esta función.');
        }

        $validate = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $cliente = Cliente::where('CliSlug', $slug)->firstOrFail();
        $sede = Sede::where('FK_SedeCli', $cliente->ID_Cli)->where('SedeDelete', 0)->firstOrFail();
        
        $personal = Personal::whereHas('cargo.area.sede', function($query) use ($cliente) {
            $query->where('FK_SedeCli', $cliente->ID_Cli);
        })->where('PersDelete', 0)->first();

        if(!$personal){
            return redirect()->back()->with('error', 'No se encontró el personal del cliente.');
        }

        // Crear usuario
        $user = new User();
        $user->name = $personal->PersFirstName . ' ' . $personal->PersLastName;
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->UsSlug = hash('sha256', rand().time().$user->email);
        $user->UsRol = "Cliente";
        $user->UsRolDesc = "Usuario General";
        $user->UsRol2 = "Cliente";
        $user->UsRolDesc2 = "Usuario General";
        $user->UsAvatar = "robot400x400.gif";
        $user->FK_UserPers = $personal->ID_Pers;
        $user->UsStatus = __('adminlte::message.userstatusactive');
        $user->save();

        // Actualizar email del personal si es diferente
        if($personal->PersEmail !== $request->input('email')){
            $personal->PersEmail = $request->input('email');
            $personal->save();
        }

        // Crear generador
        $Gener = new Generador();
        $Gener->GenerNit = $cliente->CliNit;
        $Gener->GenerName = $cliente->CliName;
        $Gener->GenerSlug = hash('sha256', rand().time().$Gener->GenerName);
        $Gener->FK_GenerCli = $sede->ID_Sede;
        $Gener->GenerDelete = $cliente->CliDelete;
        $Gener->save();

        // Crear sede del generador
        $SGener = new GenerSede();
        $SGener->GSedeName = $sede->SedeName;
        $SGener->GSedeAddress = $sede->SedeAddress;
        $SGener->GSedePhone1 = $sede->SedePhone1;
        $SGener->GSedeEmail = $personal->PersEmail;
        $SGener->GSedeCelular = $personal->PersCellphone;
        $SGener->GSedeSlug = hash('sha256', rand().time().$SGener->GSedeName);
        $SGener->FK_GSede = $Gener->ID_Gener;
        $SGener->FK_GSedeMun = $sede->FK_SedeMun;
        $SGener->GSedeDelete = $sede->SedeDelete;
        $SGener->save();

        return redirect()->route('cliente-show', [$cliente->CliSlug])->with('success', 'Usuario, generador y sede del generador creados exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $ID_Cli
     * @return \Illuminate\Http\Response
     */
    public function show(Cliente $cliente)
    {
        if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)){
            $Sedes = DB::table('sedes')
                ->join('municipios', 'municipios.ID_Mun', '=', 'sedes.FK_SedeMun')
                ->join('departamentos', 'departamentos.ID_Depart', '=', 'municipios.FK_MunCity')
                ->select('sedes.*', 'municipios.MunName', 'departamentos.DepartName')
                ->where('sedes.FK_SedeCli', $cliente->ID_Cli)
                ->where(function($query){
                    if (in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR)) {
                    }else{
                        $query->where('sedes.SedeDelete', '=', 0);
                    }
                })
                ->get();

            $SedeSlug = userController::IDSedeSegunUsuario();
            $Requerimientos = RequerimientosCliente::where('FK_RequeClient', $cliente->ID_Cli)->first();
          /*  $personal = Cliente::with('sedes.Areas.Cargos.Personal')
            ->where('ID_Cli', $cliente->ID_Cli)
            /*->where('Personal.PersDelete', 0)
            ->first();*/

            $personal_Cliente = DB::table('personals')
				->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
				->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
				->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
				->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
				->select('personals.*', 'cargos.*', 'areas.*', 'sedes.*' , 'clientes.*')
				->where('clientes.ID_Cli', $cliente->ID_Cli)
				->where('personals.PersDelete', 0)
				->get();

           //return $Sedes;
           return view('clientes.show', compact('cliente', 'Sedes', 'SedeSlug', 'Requerimientos'));
          // return view('clientes.show', compact('personal_Cliente','cliente', 'Sedes', 'SedeSlug', 'Requerimientos'));
        }else{
            abort(403);
        }
    }

    // show del menu donde dice mi Empresa
    public function viewClientShow($id)
    {
        $cliente = Cliente::where('CliSlug', $id)->first();
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $ID_Cli
     * @return \Illuminate\Http\Response
     */
    public function edit(Cliente $cliente)
    {
        if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE)){
            return view('clientes.edit', compact('cliente'));
        }else{
            abort(403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $ID_Cli
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validate = $request->validate([
            'CliNit' => ['required','min:13','max:13',Rule::unique('clientes')->where(function ($query) use ($request, $cliente){
            $Cliente = DB::table('clientes')
                ->select('clientes.CliNit')
                ->where('CliNit', $request->input('CliNit'))
                ->where('CliCategoria', 'Cliente')
                ->where('CliDelete', 0)
                ->where('ID_Cli', '<>', $cliente->ID_Cli)
                ->first();
                if(isset($Cliente->CliNit)){
                    $query->where('clientes.CliNit','=', $Cliente->CliNit);
                }else{
                    $query->where('clientes.CliNit','=', null);
                }
            })],
            'CliName'       => 'required|max:255',
            'CliShortname'  => 'alpha_num|required|max:255',
            'CliRut'        => 'mimes:pdf|max:5120|sometimes',
            'CliCamaraComercio'         => 'mimes:pdf|max:5120|sometimes',
            'CliRepresentanteLegal'     => 'mimes:pdf|max:5120|sometimes',
            'CliCertificaionBancaria'   => 'mimes:pdf|max:5120|sometimes',
            'CliCertificaionComercial'  => 'mimes:pdf|max:5120|sometimes',
            'CliCertificaionComercial2' => 'mimes:pdf|max:5120|sometimes',
        ]);

        $cliente = Cliente::where('CliSlug', $cliente->CliSlug)->first();
        $cliente->fill($request->except('CliRut', 'CliCamaraComercio', 'CliRepresentanteLegal', 'CliCertificaionComercial', 'CliCertificaionBancaria'));
        $Folder = $cliente->CliShortname;
        if ($request->hasfile('CliRut')){
            if(isset($cliente->CliRut)  && file_exists(public_path().'/img/DatosClientes/'.$cliente->CliRut)){
                unlink(public_path()."/img/DatosClientes/".$cliente->CliRut);
            }
            $Rut = 'Rut - '.date('j-m-y').hash('sha256', rand().time().$request->CliRut->getClientOriginalName()).'.'.$request->CliRut->extension();
            $request->CliRut->move(public_path('/img/DatosClientes/').$Folder,$Rut);
            $cliente->CliRut = $Folder.'/'.$Rut;
        }
        if ($request->hasfile('CliCamaraComercio')){
            if(isset($cliente->CliCamaraComercio) && file_exists(public_path().'/img/DatosClientes/'.$cliente->CliCamaraComercio)){
                unlink(public_path("img/DatosClientes/$cliente->CliCamaraComercio"));
            }
            $CamaraComercio = 'Camara de Comercio - '.date('j-m-y').hash('sha256', rand().time().$request->CliCamaraComercio->getClientOriginalName()).'.'.$request->CliCamaraComercio->extension();
            $request->CliCamaraComercio->move(public_path('/img/DatosClientes/').$Folder,$CamaraComercio);
            $cliente->CliCamaraComercio = $Folder.'/'.$CamaraComercio;
        }
        if ($request->hasfile('CliRepresentanteLegal')){
            if(isset($cliente->CliRepresentanteLegal)  && file_exists(public_path().'/img/DatosClientes/'.$cliente->CliRepresentanteLegal)) {
                unlink(public_path("img/DatosClientes/$cliente->CliRepresentanteLegal"));
            }
            $RepresentanteLegal = 'Representante Legal - '.date('j-m-y').hash('sha256', rand().time().$request->CliRepresentanteLegal->getClientOriginalName()).'.'.$request->CliRepresentanteLegal->extension();
            $request->CliRepresentanteLegal->move(public_path('/img/DatosClientes/').$Folder,$RepresentanteLegal);
            $cliente->CliRepresentanteLegal = $Folder.'/'.$RepresentanteLegal;
        }
        if ($request->hasfile('CliCertificaionComercial')){
            if(isset($cliente->CliCertificaionComercial) && file_exists(public_path().'/img/DatosClientes/'.$cliente->CliCertificaionComercial)){
                unlink(public_path("img/DatosClientes/$cliente->CliCertificaionComercial"));
            }
            $CertificacionComercial = 'Certificacion Comercial - '.date('j-m-y').hash('sha256', rand().time().$request->CliCertificaionComercial->getClientOriginalName()).'.'.$request->CliCertificaionComercial->extension();
            $request->CliCertificaionComercial->move(public_path('/img/DatosClientes/').$Folder,$CertificacionComercial);
            $cliente->CliCertificaionComercial = $Folder.'/'.$CertificacionComercial;
        }
        if ($request->hasfile('CliCertificaionComercial2')){
            if(isset($cliente->CliCertificaionComercial2) && file_exists(public_path().'/img/DatosClientes/'.$cliente->CliCertificaionComercial2)){
                unlink(public_path("img/DatosClientes/$cliente->CliCertificaionComercial2"));
            }
            $CertificacionComercial2 = 'Certificacion Comercial - '.date('j-m-y').hash('sha256', rand().time().$request->CliCertificaionComercial2->getClientOriginalName()).'.'.$request->CliCertificaionComercial2->extension();
            $request->CliCertificaionComercial2->move(public_path('/img/DatosClientes/').$Folder,$CertificacionComercial2);
            $cliente->CliCertificaionComercial2 = $Folder.'/'.$CertificacionComercial2;
        }
        if ($request->hasfile('CliCertificaionBancaria')){
            if(isset($cliente->CliCertificaionBancaria) && file_exists(public_path().'/img/DatosClientes/'.$cliente->CliCertificaionBancaria)){
                unlink(public_path("img/DatosClientes/$cliente->CliCertificaionBancaria"));
            }
            $CertificacionBancaria = 'Certificacion Bancaria - '.date('j-m-y').hash('sha256', rand().time().$request->CliCertificaionBancaria->getClientOriginalName()).'.'.$request->CliCertificaionBancaria->extension();
            $request->CliCertificaionBancaria->move(public_path('/img/DatosClientes/').$Folder,$CertificacionBancaria);
            $cliente->CliCertificaionBancaria = $Folder.'/'.$CertificacionBancaria;
        }
        $cliente->save();

        AuditRequest::auditUpdate($this->table, $cliente->ID_Cli, json_encode($request->all()));
        return redirect()->route('clientes.show', compact('cliente'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $ID_Cli
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug){
        // if(in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR)){
        //     $Cliente = Cliente::where('CliSlug', $slug)->first();

        //     function OnCascade($ValueOnCascade, $Cliente){
        //         DB::table('clientes')->orderBy('ID_Cli')->chunk(100, function ($clientes) use($Cliente, $ValueOnCascade) {
        //             foreach ($clientes as $cliente) {
        //                 DB::table('clientes')
        //                 ->join('sedes', 'sedes.FK_SedeCli', 'clientes.ID_Cli')
        //                 ->join('generadors', 'generadors.FK_GenerCli', 'sedes.ID_Sede')
        //                 ->join('gener_sedes', 'gener_sedes.FK_GSede', 'generadors.ID_Gener')
        //                 ->join('residuos_geners', 'residuos_geners.FK_SGener', 'gener_sedes.ID_GSede')
        //                 ->where('clientes.ID_Cli', $Cliente->ID_Cli)
        //                 ->update([
        //                     'residuos_geners.DeleteSGenerRes' => $ValueOnCascade,
        //                     'gener_sedes.GSedeDelete' => $ValueOnCascade,
        //                     'generadors.GenerDelete' => $ValueOnCascade,
        //                     'sedes.SedeDelete' => $ValueOnCascade,
        //                     'clientes.CliDelete' => $ValueOnCascade,
        //                 ]);
        //             }
        //         });
        //     }

        //     if ($Cliente->CliDelete == 0) {
        //         $ValueOnCascade = 1;
        //         OnCascade($ValueOnCascade, $Cliente);
        //         // $Cliente->CliDelete = 1;
        //     }
        //     else{
        //         $ValueOnCascade = 0;
        //         OnCascade($ValueOnCascade, $Cliente);
        //         // $Cliente->CliDelete = 0;
        //     }
        //     $Cliente->save();

        //     $log = new audit();
        //     $log->AuditTabla="clientes";
        //     $log->AuditType="Eliminado";
        //     $log->AuditRegistro=$Cliente->ID_Cli;
        //     $log->AuditUser=Auth::user()->email;
        //     $log->Auditlog = $Cliente->CliDelete;
        //     $log->save();

        //     return redirect()->route('clientes.index');
        // }else{
        //     abort(403);
        // }
    }
}
