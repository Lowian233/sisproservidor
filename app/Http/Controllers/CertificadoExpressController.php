<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertUpdated;
use App\Mail\CertUpdatedComercial;
use App\Certificado;
use App\CertificadoExpress;
use App\CertExpressdato;
use App\Cliente;
use App\Personal;
use App\Generador;
use App\Tratamiento;
use App\Audit;
use App\Certdato;
use App\Permisos;
use App\SolicitudServicio;
use App\SolicitudResiduo;
use App\Http\Requests\CertificadoUpdateRequest;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

class CertificadoExpressController extends Controller
{
    private const MAIL_EXPRESS_INTERNO = 'express@prosarc.com.co';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('certificadosExpress.ano');
       //return view('CertificadosExpress.year');
       
                                 
    }

          /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function certex2023(){
        // validacion del status del cliente segun cartera
        $clienteID = userController::IDClienteSegunUsuario();
        $clienteStatus= Cliente::where('ID_Cli', $clienteID)->first('CliStatus');
        if ($clienteStatus->CliStatus == 'Bloqueado') {
            abort(403, "el acceso a la lista de certificados se encuentra bloqueado, comuniquese con su asesor comercial en PROSARC S.A. ESP");
        }      
   
        $certificados = CertificadoExpress::where(function($query){
          $years = DB :: table('certificadosexpress')
              ->select(DB::raw('distinct year(created_at) as year'))
              ->orderBy('year', 'desc')
              ->get(); 

              foreach ($years as $year) {
                $registros = DB::table('certificadosexpress')
                ->whereYear('created_at', $year='2023')
                ->get();
              }
  
            switch (Auth::user()->UsRol) {
                case 'Cliente':
                    /*se define la sede del usuario actual*/
                    $UserSedeID = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->where('clientes.CliStatus', 'Autorizado')
                    ->value('clientes.ID_Cli');
  
                    $servicioscertificadosdelcliente = SolicitudServicio::where('FK_SolSerCliente',$UserSedeID)
                    ->where('SolServCertStatus', 2)
                    ->get('ID_SolSer');
  
                    //$query->whereYear('created_at', $year='2020')
                    $query->where('FK_CertCliente', $UserSedeID);
                    $query->where('CertAuthJo', '!=', 0);
                    $query->where('CertAuthJl', '!=', 0);
                    $query->where('CertAuthDp', '!=', 0);
                    $query->whereIn('FK_CertSolser', $servicioscertificadosdelcliente);
                    break;
  
                case 'Comercial':
                    /*se define la sede del usuario actual*/
                    $clientes = Cliente::where('CliDelete', 0)->where('CliCategoria', 'Cliente')->where('CliComercial', Auth::user()->FK_UserPers)->get('ID_Cli');
  
                    // return $clientes;
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;
  
                default:
                    // $query->where('ID_Cert', '>', 0);
                    break;
            }
        })
        ->with(['tratamiento'])
        ->whereYear('created_at', $year='2023')
        ->get();
        $certificados->map(function ($certificado) {
          
            $fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
            if ($fecharecepcionenplanta != null) {
                $certificado->recepcion = $fecharecepcionenplanta->ProgVehSalida;
            }else{
                $certificado->recepcion = "";
            }
            $certificado->cliente = $certificado->SolicitudServicio->cliente()->first('CliName')->CliName;
            $certificado->SolSerStatus = $certificado->SolicitudServicio()->first('SolSerStatus')->SolSerStatus;
            return $certificado ;
        
      });
           
        // return $registros;
        return view ('certificadosExpress.2023', compact('certificados'));  
        
      }

           /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function certex2024(){
        // validacion del status del cliente segun cartera
        $clienteID = userController::IDClienteSegunUsuario();
        $clienteStatus= Cliente::where('ID_Cli', $clienteID)->first('CliStatus');
        if ($clienteStatus->CliStatus == 'Bloqueado') {
            abort(403, "el acceso a la lista de certificados se encuentra bloqueado, comuniquese con su asesor comercial en PROSARC S.A. ESP");
        }      
   
        $certificados = CertificadoExpress::where(function($query){
          $years = DB :: table('certificadosexpress')
              ->select(DB::raw('distinct year(created_at) as year'))
              ->orderBy('year', 'desc')
              ->get(); 

              foreach ($years as $year) {
                $registros = DB::table('certificadosexpress')
                ->whereYear('created_at', $year='2024')
                ->get();
              }
  
            switch (Auth::user()->UsRol) {
                case 'Cliente':
                    /*se define la sede del usuario actual*/
                    $UserSedeID = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->where('clientes.CliStatus', 'Autorizado')
                    ->value('clientes.ID_Cli');
  
                    $servicioscertificadosdelcliente = SolicitudServicio::where('FK_SolSerCliente',$UserSedeID)
                    ->where('SolServCertStatus', 2)
                    ->get('ID_SolSer');
  
                    //$query->whereYear('created_at', $year='2020')
                    $query->where('FK_CertCliente', $UserSedeID);
                    $query->where('CertAuthJo', '!=', 0);
                    $query->where('CertAuthJl', '!=', 0);
                    $query->where('CertAuthDp', '!=', 0);
                    $query->whereIn('FK_CertSolser', $servicioscertificadosdelcliente);
                    break;
  
                case 'Comercial':
                    /*se define la sede del usuario actual*/
                    $clientes = Cliente::where('CliDelete', 0)->where('CliCategoria', 'Cliente')->where('CliComercial', Auth::user()->FK_UserPers)->get('ID_Cli');
  
                    // return $clientes;
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;
  
                default:
                    // $query->where('ID_Cert', '>', 0);
                    break;
            }
        })
        ->with(['tratamiento'])
        ->whereYear('created_at', $year='2024')
        ->get();
        $certificados->map(function ($certificado) {
          
            $fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
            if ($fecharecepcionenplanta != null) {
                $certificado->recepcion = $fecharecepcionenplanta->ProgVehSalida;
            }else{
                $certificado->recepcion = "";
            }
            $certificado->cliente = $certificado->SolicitudServicio->cliente()->first('CliName')->CliName;
            $certificado->SolSerStatus = $certificado->SolicitudServicio()->first('SolSerStatus')->SolSerStatus;
            $certificado->url_pdf = null;
    switch ($certificado->CertType) {
        case 0:
            $certificado->url_pdf = asset('img/Certificados/' . $certificado->CertSrc);
            break;
        case 1:
            $certificado->url_pdf = asset('img/Manifiestos/' . $certificado->CertSrcManif);
            break;
        case 2:
            $certificado->url_pdf = asset('img/CertificadosEXT/' . $certificado->CertSrcExt);
            break;
    }

    return $certificado;
      });
           
        // return $registros;
        return view ('certificadosExpress.2024', compact('certificados'));  
        
      }
      
      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function certex2025(){
        // validacion del status del cliente segun cartera
        
   
        $certificados = CertificadoExpress::where(function($query){
          $years = DB :: table('certificadosexpress')
              ->select(DB::raw('distinct year(created_at) as year'))
              ->orderBy('year', 'desc')
              ->get(); 

              foreach ($years as $year) {
                $registros = DB::table('certificadosexpress')
                ->whereYear('created_at', $year='2025')
                ->get();
              }
  
            switch (Auth::user()->UsRol) {
                case 'Cliente':
                    /*se define la sede del usuario actual*/
                    $UserSedeID = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->where('clientes.CliStatus', 'Autorizado')
                    ->value('clientes.ID_Cli');
  
                    $servicioscertificadosdelcliente = SolicitudServicio::where('FK_SolSerCliente',$UserSedeID)
                    ->where('SolServCertStatus', 2)
                    ->get('ID_SolSer');
  
                    //$query->whereYear('created_at', $year='2020')
                    $query->where('FK_CertCliente', $UserSedeID);
                    $query->where('CertAuthJo', '!=', 0);
                    $query->where('CertAuthJl', '!=', 0);
                    $query->where('CertAuthDp', '!=', 0);
                    $query->whereIn('FK_CertSolser', $servicioscertificadosdelcliente);
                    break;
  
                case 'Comercial':
                    /*se define la sede del usuario actual*/
                    $clientes = Cliente::where('CliDelete', 0)->where('CliCategoria', 'Cliente')->where('CliComercial', Auth::user()->FK_UserPers)->get('ID_Cli');
  
                    // return $clientes;
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;
  
                default:
                    // $query->where('ID_Cert', '>', 0);
                    break;
            }
        })
        ->with(['tratamiento'])
        ->whereYear('created_at', $year='2025')
        ->get();
        $certificados->map(function ($certificado) {
          
            $fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
            if ($fecharecepcionenplanta != null) {
                $certificado->recepcion = $fecharecepcionenplanta->ProgVehSalida;
            }else{
                $certificado->recepcion = "";
            }
            $certificado->cliente = $certificado->SolicitudServicio->cliente()->first('CliName')->CliName;
            $certificado->SolSerStatus = $certificado->SolicitudServicio()->first('SolSerStatus')->SolSerStatus;
            $certificado->url_pdf = null;
    switch ($certificado->CertType) {
        case 0:
            $certificado->url_pdf = asset('img/Certificados/' . $certificado->CertSrc);
            break;
        case 1:
            $certificado->url_pdf = asset('img/Manifiestos/' . $certificado->CertSrcManif);
            break;
        case 2:
            $certificado->url_pdf = asset('img/CertificadosEXT/' . $certificado->CertSrcExt);
            break;
    }

    return $certificado;
      });
           
        // return $registros;
        return view ('certificadosExpress.2025', compact('certificados'));  
        
      }
      
     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function certex2026(){
        // validacion del status del cliente segun cartera
        
   
        $certificados = CertificadoExpress::where(function($query){
          $years = DB :: table('certificadosexpress')
              ->select(DB::raw('distinct year(created_at) as year'))
              ->orderBy('year', 'desc')
              ->get(); 

              foreach ($years as $year) {
                $registros = DB::table('certificadosexpress')
                ->whereYear('created_at', $year='2025')
                ->get();
              }
  
            switch (Auth::user()->UsRol) {
                case 'Cliente':
                    /*se define la sede del usuario actual*/
                    $UserSedeID = DB::table('personals')
                    ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                    ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                    ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                    ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                    ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                    ->where('clientes.CliStatus', 'Autorizado')
                    ->value('clientes.ID_Cli');
  
                    $servicioscertificadosdelcliente = SolicitudServicio::where('FK_SolSerCliente',$UserSedeID)
                    ->where('SolServCertStatus', 2)
                    ->get('ID_SolSer');
  
                    //$query->whereYear('created_at', $year='2020')
                    $query->where('FK_CertCliente', $UserSedeID);
                    $query->where('CertAuthJo', '!=', 0);
                    $query->where('CertAuthJl', '!=', 0);
                    $query->where('CertAuthDp', '!=', 0);
                    $query->whereIn('FK_CertSolser', $servicioscertificadosdelcliente);
                    break;
  
                case 'Comercial':
                    /*se define la sede del usuario actual*/
                    $clientes = Cliente::where('CliDelete', 0)->where('CliCategoria', 'Cliente')->where('CliComercial', Auth::user()->FK_UserPers)->get('ID_Cli');
  
                    // return $clientes;
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;
  
                default:
                    // $query->where('ID_Cert', '>', 0);
                    break;
            }
        })
        ->with(['tratamiento'])
        ->whereYear('created_at', $year='2026')
        ->get();
        $certificados->map(function ($certificado) {
          
            $fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
            if ($fecharecepcionenplanta != null) {
                $certificado->recepcion = $fecharecepcionenplanta->ProgVehSalida;
            }else{
                $certificado->recepcion = "";
            }
            $certificado->cliente = $certificado->SolicitudServicio->cliente()->first('CliName')->CliName;
            $certificado->SolSerStatus = $certificado->SolicitudServicio()->first('SolSerStatus')->SolSerStatus;
            $certificado->url_pdf = null;
    switch ($certificado->CertType) {
        case 0:
            $certificado->url_pdf = asset('img/Certificados/' . $certificado->CertSrc);
            break;
        case 1:
            $certificado->url_pdf = asset('img/Manifiestos/' . $certificado->CertSrcManif);
            break;
        case 2:
            $certificado->url_pdf = asset('img/CertificadosEXT/' . $certificado->CertSrcExt);
            break;
    }

    return $certificado;
      });
           
        // return $registros;
        return view ('certificadosExpress.2026', compact('certificados'));  
        
      }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        // $SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();
        // if (!$SolicitudServicio) {
        //     abort(404);
        // }
        // $certificado = new Certificado;
        // $certificado->CertNumero = '';
        // $certificado->CertiEspName = '';
        // $certificado->CertiEspValue = '';
        // $certificado->CertObservacion = '';
        // $certificado->CertSrc = '';
        // $certificado->CertAuthJo = '';
        // $certificado->CertAuthJl = '';
        // $certificado->CertAuthDp = '';
        // $certificado->CertAnexo = '';
        // $certificado->FK_CertSolser = $SolicitudServicio->ID_SolSer;
        // $certificado->save();

        // $certificado->CertNumero = $certificado->ID_SolSer;
        // $certificado->update();


        // return view('certificadosExpress.edit', compact('SolicitudServicio')); 

        // return redirect()->route('solicitud-servicio.solservdocindex', compact('id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $SolicitudServicio = SolicitudServicio::where('SolSerSlug', $id)->first();
        if (!$SolicitudServicio) {
            abort(404);
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
        $certificado = CertificadoExpress::with(['SolicitudServicio' => function ($query){
            $query->with(['SolicitudResiduo' => function ($query){
                $query->where('SolResKgConciliado', '>', 0);
                $query->orWhere('SolResCantiUnidadConciliada', '>', 0);
                $query->with('generespel.respels');
                $query->with('requerimiento');
            }]);
            
        }, 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento','certdato.solres'])
        ->where('CertSlug', $id)
        ->first();

        $certificado->SolicitudServicio->SolicitudResiduo = $certificado->SolicitudServicio->SolicitudResiduo->map(function ($item) {
			$rm = SolicitudResiduo::where('SolResSlug', $item->SolResSlug)->first('SolResRM');
	        $item->SolResRM2 = $rm->SolResRM;
		  	return $item;
		});
        
        // return $certificado;
        return view('certificadosExpress.show', compact('certificado')); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT)||in_array(Auth::user()->UsRol2, Permisos::EDITMANIFCERT)) {
            $certificado = CertificadoExpress::with(['SolicitudServicio' => function ($query){
                $query->with(['SolicitudResiduo' => function ($query){
                    $query->where('SolResKgConciliado', '>', 0);
                    $query->orWhere('SolResCantiUnidadConciliada', '>', 0);
                    $query->with('generespel.respels');
                    $query->with('requerimiento');
                }]);
                
            }, 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento'])
            ->where('CertSlug', $id)
            ->first();

            $ultimoCertificado = CertificadoExpress::where('CertNumero', '!=', NULL)->orderBy('CertNumero', 'desc')->first('CertNumero');
            $proximoCertificado = ($ultimoCertificado->CertNumero == NULL) ? 1 : $ultimoCertificado->CertNumero+1 ;
            
            $ultimoManif = CertificadoExpress::where('CertManifNumero', '!=', NULL)->orderBy('CertManifNumero', 'desc')->first('CertManifNumero');
			$proximoManif = ($ultimoManif == NULL) ? 1 : ($ultimoManif->CertManifNumero+1);
            $certificado->SolicitudServicio->SolicitudResiduo = $certificado->SolicitudServicio->SolicitudResiduo->map(function ($item) {
                $rm = SolicitudResiduo::where('SolResSlug', $item->SolResSlug)->first('SolResRM');
                $item->SolResRM2 = $rm->SolResRM;
                return $item;
            });

            $qrCode = new QrCode(route('certificados.show', ['certificado' => $certificado->CertSlug]));
            $qrCode->setLogoPath(asset('img/LogoQR.png'));
            $qrCode->setLogoSize(30, 30);
            $qrCode->setSize(150);
            $qrCode->setMargin(0);
            $qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);

                // return $qrCode->writeDataUri();
            return view('certificadosExpress.edit', compact(['certificado', 'proximoCertificado', 'proximoManif', 'qrCode']))->withHeaders('Content-Type', $qrCode->getContentType()); 
        }else{
            abort(404, "no posee permisos para la edición de certificados");
        }
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function update(CertificadoUpdateRequest $request, $id)
    {
        // return $request;
        $certificado = CertificadoExpress::where('CertSlug', $id)->first();
        $certificado->CertType = $request->input('CertType');
        $certificado->CertiEspName = $request->input('CertiEspName');
        $certificado->CertiEspValue = $request->input('CertiEspValue');
        $certificado->CertObservacion = $request->input('CertObservacion');
        $certificado->CertNumRm = $request->input('CertNumRm');

        switch ($request->input('CertType')) {
            case 0:
                $certificado->CertNumero = $request->input('CertNumero');
                $certificado->CertManifNumero = 0;
                if (isset($request['CertSrc'])) {
                    if ($certificado->CertSrc == 'CertificadoDefault.pdf') {
                        $file1 = $request['CertSrc'];
                        $hoja = 'E-' . str_pad((string)$certificado->ID_Cert, 7, '0', STR_PAD_LEFT) . '.pdf';
                        $file1->move(storage_path('app/public/certificadoExpress/'),$hoja);
                    }else{
                        //se elimina el archivo anterior
                        $hoja = 'E-' . str_pad((string)$certificado->ID_Cert, 7, '0', STR_PAD_LEFT) . '.pdf';
                        $fileanterior =  storage_path('app/public/certificadoExpress/').$hoja;
                        if(file_exists($fileanterior)) {
                            unlink($fileanterior);
                        }
                        //se carga el archivo nuevo que viene del formulario
                        $file1 = $request['CertSrc'];
                        $file1->move(storage_path('app/public/certificadoExpress/'),$hoja);
                    }
                    $certificado->CertAuthHseq = 0;
                    $certificado->CertAuthJo = 0;
                    $certificado->CertAuthJl = 0;
                    $certificado->CertAuthDp = 0;
                }else{
                    if ($certificado->CertSrc == 'CertificadoDefault.pdf') {
                        $hoja = 'CertificadoDefault.pdf';
                    }else{
                        $hoja = $certificado->CertSrc;
                    }
                }
                $certificado->CertSrc = $hoja;
                break;

            case 1:
                $certificado->CertManifNumero = $request->input('CertNumero');
                $certificado->CertNumero = 0;
                if (isset($request['CertSrc'])) {
                    if ($certificado->CertSrcManif == 'CertificadoDefault.pdf') {
                        $file1 = $request['CertSrc'];
                        $hoja = 'E-' . str_pad((string)$certificado->ID_Cert, 7, '0', STR_PAD_LEFT) . '.pdf';
                        $file1->move(public_path().'/img/Manifiestos/',$hoja);
                    }else{
                        //se elimina el archivo anterior
                        $hoja = 'E-' . str_pad((string)$certificado->ID_Cert, 7, '0', STR_PAD_LEFT) . '.pdf';
                        $fileanterior =  public_path().'/img/Manifiestos/'.$hoja;
                        if(file_exists($fileanterior)) {
                            unlink($fileanterior);
                        }
                        //se carga el archivo nuevo que viene del formulario
                        $file1 = $request['CertSrc'];
                        $file1->move(public_path().'/img/Manifiestos/',$hoja);
                    }
                    $certificado->CertAuthHseq = 0;
                    $certificado->CertAuthJo = 0;
                    $certificado->CertAuthJl = 0;
                    $certificado->CertAuthDp = 0;
                }else{
                    if ($certificado->CertSrcManif == 'CertificadoDefault.pdf') {
                        $hoja = 'CertificadoDefault.pdf';
                    }else{
                        $hoja = $certificado->CertSrcManif;
                    }
                }
                $certificado->CertSrcManif = $hoja;
                break;

            case 2:
                $certificado->CertNumeroExt = $request->input('CertNumero');
                if (isset($request['CertSrc'])) {
                    if ($certificado->CertSrcExt == 'CertificadoDefault.pdf') {
                        $file1 = $request['CertSrc'];
                        $hoja = 'E-' . str_pad((string)$certificado->ID_Cert, 7, '0', STR_PAD_LEFT) . '.pdf';
                        $file1->move(public_path().'/img/CertificadosEXT/',$hoja);
                    }else{
                        //se elimina el archivo anterior
                        $hoja = 'E-' . str_pad((string)$certificado->ID_Cert, 7, '0', STR_PAD_LEFT) . '.pdf';
                        $fileanterior =  public_path().'/img/CertificadosEXT/'.$hoja;
                        if(file_exists($fileanterior)) {
                            unlink($fileanterior);
                        }
                        //se carga el archivo nuevo que viene del formulario
                        $file1 = $request['CertSrc'];
                        $file1->move(public_path().'/img/CertificadosEXT/',$hoja);
                    }
                    $certificado->CertAuthHseq = 0;
                    $certificado->CertAuthJo = 0;
                    $certificado->CertAuthJl = 0;
                    $certificado->CertAuthDp = 0;
                }else{
                    if ($certificado->CertSrcExt == 'CertificadoDefault.pdf') {
                        $hoja = 'CertificadoDefault.pdf';
                    }else{
                        $hoja = $certificado->CertSrcExt;
                    }
                }
                $certificado->CertSrcExt = $hoja;
                break;
            
            default:
                $certificado->CertNumero = $request->input('CertNumero');
                break;
        }
        $certificado->save();

        if (isset($request['CertSrc'])) {

            $servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();

            $cliente = Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first();

            Mail::to(self::MAIL_EXPRESS_INTERNO)->send(new CertUpdated($certificado, $servicio, $cliente));

        }
        
        $log = new audit();
        $log->AuditTabla="certificadosexpress";
        $log->AuditType="actualizado";
        $log->AuditRegistro=$certificado->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($id);
        $log->save();
        
           // return view('certificadosExpress.edit', compact('certificado')); 
        // return redirect()->action('CertificadoController@edit', ['CertSlug' => $certificado->CertSlug]);
        return redirect()->route('certificados.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function firmar($id, $servicio)
    {
        /*indice de firmas */
        // 0:Pendiente
        // 1:Director Planta
        // 2:Jefe de Logistica
        // 3:Jefe de Operaciones
        // 4:Supervisor de Turno
        // 5:Ingeniero HSEQ
        // 6:Asistente de Logistica
        // 7:Programador
        $certificado = CertificadoExpress::where('CertSlug', $id)->first();
        if ($certificado->SolicitudServicio->SolSerStatus == 'Certificacion') {
            switch (Auth::user()->UsRol) {
                case 'Hseq':
                    $certificado->CertAuthHseq = 5;
                    break;

                case 'JefeOperaciones':
                    $certificado->CertAuthJo = 3;
                    break;

                case 'JefeLogistica':
                    $certificado->CertAuthJl = 2;
                    break;

                case 'AdministradorPlanta':
                    ($certificado->CertAuthDp == 0) ? $certificado->CertAuthDp = 1 : $certificado->CertAuthDp = 0;
                    break;

                case 'Supervisor':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 4)||($certificado->CertAuthJl == 4)||($certificado->CertAuthJo == 4)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 4;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                case 'AsistenteLogistica':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 6)||($certificado->CertAuthJl == 6)||($certificado->CertAuthJo == 6)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 6;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                    
                case 'Programador':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 7)||($certificado->CertAuthJl == 7)||($certificado->CertAuthJo == 7)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 7;
                            $c=$c+1;
                        }
                    }
                    
                    break;
                
                default:
                    # code...
                    break;
            }
        }else{
            switch (Auth::user()->UsRol) {
                case 'Hseq':
                    ($certificado->CertAuthHseq == 0) ? $certificado->CertAuthHseq = 5 : $certificado->CertAuthHseq = 0;
                    break;

                case 'JefeOperaciones':
                    ($certificado->CertAuthJo == 0) ? $certificado->CertAuthJo = 3 : $certificado->CertAuthJo = 0;
                    break;

                case 'JefeLogistica':
                    ($certificado->CertAuthJl == 0) ? $certificado->CertAuthJl = 2 : $certificado->CertAuthJl = 0;
                    break;

                case 'AdministradorPlanta':
                    ($certificado->CertAuthDp == 0) ? $certificado->CertAuthDp = 1 : $certificado->CertAuthDp = 0;
                    break;

                case 'Supervisor':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 4)||($certificado->CertAuthJl == 4)||($certificado->CertAuthJo == 4)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 4;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                case 'AsistenteLogistica':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 6)||($certificado->CertAuthJl == 6)||($certificado->CertAuthJo == 6)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 6;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                    
                case 'Programador':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 7)||($certificado->CertAuthJl == 7)||($certificado->CertAuthJo == 7)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 7;
                            $c=$c+1;
                        }
                    }
                    
                    break;
                
                default:
                    # code...
                    break;
            }
        }
        $certificado->save();

        $log = new audit();
        $log->AuditTabla="certificadosexpress";
        $log->AuditType="firmado";
        $log->AuditRegistro=$certificado->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($id);
        $log->save();

        if ($certificado->CertAuthJo != 0 && $certificado->CertAuthJl != 0 && $certificado->CertAuthDp != 0 ) {
            $servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
            $cliente = $servicio ? Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first() : null;
            if ($servicio && $cliente) {
                Mail::to(self::MAIL_EXPRESS_INTERNO)->send(new CertUpdatedComercial($certificado, $servicio, $cliente));
            }
        }
                  

        return redirect()->route('solicitud-servicio.documentos', [$servicio]);
    }


    public function firmarindex($id)
    {
        /*indice de firmas */
        // 0:Pendiente
        // 1:Director Planta
        // 2:Jefe de Logistica
        // 3:Jefe de Operaciones
        // 4:Supervisor de Turno
        // 5:Ingeniero HSEQ
        // 6:Asistente de Logistica
        // 7:Programador
        $certificado = CertificadoExpress::where('CertSlug', $id)->first();
        if ($certificado->SolicitudServicio->SolSerStatus == 'Certificacion') {
            switch (Auth::user()->UsRol) {
                case 'Hseq':
                    $certificado->CertAuthHseq = 5;
                    break;

                case 'JefeOperaciones':
                    $certificado->CertAuthJo = 3;
                    break;

                case 'JefeLogistica':
                    $certificado->CertAuthJl = 2;
                    break;

                case 'AdministradorPlanta':
                    ($certificado->CertAuthDp == 0) ? $certificado->CertAuthDp = 1 : $certificado->CertAuthDp = 0;
                    break;

                case 'Supervisor':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 4)||($certificado->CertAuthJl == 4)||($certificado->CertAuthJo == 4)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 4;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                case 'AsistenteLogistica':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 6)||($certificado->CertAuthJl == 6)||($certificado->CertAuthJo == 6)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 6;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                    
                case 'Programador':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 7)||($certificado->CertAuthJl == 7)||($certificado->CertAuthJo == 7)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 7;
                            $c=$c+1;
                        }
                    }
                    
                    break;
                
                default:
                    # code...
                    break;
            }
        }else{
            switch (Auth::user()->UsRol) {
                case 'Hseq':
                    ($certificado->CertAuthHseq == 0) ? $certificado->CertAuthHseq = 5 : $certificado->CertAuthHseq = 0;
                    break;

                case 'JefeOperaciones':
                    ($certificado->CertAuthJo == 0) ? $certificado->CertAuthJo = 3 : $certificado->CertAuthJo = 0;
                    break;

                case 'JefeLogistica':
                    ($certificado->CertAuthJl == 0) ? $certificado->CertAuthJl = 2 : $certificado->CertAuthJl = 0;
                    break;

                case 'AdministradorPlanta':
                    ($certificado->CertAuthDp == 0) ? $certificado->CertAuthDp = 1 : $certificado->CertAuthDp = 0;
                    break;

                case 'Supervisor':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 4)||($certificado->CertAuthJl == 4)||($certificado->CertAuthJo == 4)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 4;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 4;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                case 'AsistenteLogistica':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 6)||($certificado->CertAuthJl == 6)||($certificado->CertAuthJo == 6)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 6;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 6;
                            $c=$c+1;
                        }
                    }
                    
                    break;

                    
                case 'Programador':
                    if (($certificado->CertAuthDp == 0)&&($certificado->CertAuthJl == 0)&&($certificado->CertAuthJo == 0)) {
                        # code...
                    }else{
                        if (($certificado->CertAuthDp == 7)||($certificado->CertAuthJl == 7)||($certificado->CertAuthJo == 7)) {
                            $c=1;
                        }else{
                            $c=0;
                        }
                        if (($certificado->CertAuthDp == 0)&&($c<1)) {
                            $certificado->CertAuthDp = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJl == 0)&&($c<1)) {
                            $certificado->CertAuthJl = 7;
                            $c=$c+1;
                        }
                        if (($certificado->CertAuthJo == 0)&&($c<1)) {
                            $certificado->CertAuthJo = 7;
                            $c=$c+1;
                        }
                    }
                    
                    break;
                
                default:
                    # code...
                    break;
            }
        }

        $certificado->save();

        $log = new Audit();
        $log->AuditTabla="certificadosexpress";
        $log->AuditType="firmado";
        $log->AuditRegistro=$certificado->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($id);
        $log->save();

        if ($certificado->CertAuthJo != 0 && $certificado->CertAuthJl != 0 && $certificado->CertAuthDp != 0 ) {
            $servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
            $cliente = $servicio ? Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first() : null;
            if ($servicio && $cliente) {
                Mail::to(self::MAIL_EXPRESS_INTERNO)->send(new CertUpdatedComercial($certificado, $servicio, $cliente));
            }
        }

        return redirect()->route('certificados.index');
    }


        /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function wordtemplate($id)
    {
        $certificado = CertificadoExpress::with(['SolicitudServicio' => function ($query){
            $query->with(['SolicitudResiduo' => function ($query){
                $query->where('SolResKgConciliado', '>', 0);
                $query->orWhere('SolResCantiUnidadConciliada', '>', 0);
                $query->with('generespel.respels');
                $query->with('requerimiento');
            }]);
            
        }, 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento','certdato.solres'])
        ->where('CertSlug', $id)
        ->first();

        $fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
        if ($fecharecepcionenplanta != null) {
            $certificado->recepcion = $fecharecepcionenplanta->ProgVehSalida;
        }else{
            $certificado->recepcion = "";
        }

        // return $certificado;
        switch ($certificado->tratamiento->TratName) {
            case 'TermoDestrucción':
                return view('certificadosExpress.imprimible', compact('certificado')); 
                break;
            case 'Posconsumo luminarias':
                return view('certificadosExpress.luminarias', compact('certificado')); 
                break;
            default:
                return view('certificadosExpress.manifiesto', compact('certificado')); 
                break;
        }
    }


    public function independiente(Request $request, $id)
	{        
        $certificadoOld = CertificadoExpress::where('ID_Cert', $id)->first();

        $certificadoNew = $certificadoOld->replicate()->fill([
            'CertSlug' => hash('sha256', rand().time()),
            'created_at' => now(),
            'updated_at' => now(),
            'CertNumero' => 0,
            'CertObservacion' => 'certificado con observacion generica',
            'CertSrc' => 'CertificadoDefault.pdf',
            'CertAuthHseq' => 0,
            'CertAuthJo' => 0,
            'CertAuthJl' => 0,
            'CertAuthDp' => 0,
        ]);
        $certificadoNew->save();
        
        foreach ($request->input('residuos') as $key => $value) {
            $certdato = CertExpressdato::where('ID_CertDato', $value)->first();
            $certdato->FK_DatoCert = $certificadoNew->ID_Cert;
            $certdato->save();
        }

        $log = new audit();
        $log->AuditTabla="certificadosexpress";
        $log->AuditType="generado Cert express independiente";
        $log->AuditRegistro=$certificadoOld->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($request);
        $log->save();
        
        return redirect()->route('certificados.show', ['id' => $certificadoNew->CertSlug]);
	}
}
