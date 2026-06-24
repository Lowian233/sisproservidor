<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\CertUpdated;
use App\Mail\CertUpdatedComercial;
use App\Certificado;
use App\Cliente;
use App\Personal;
use App\Generador;
use App\Tratamiento;
use App\audit;
use App\Certdato;
use App\Permisos;
use App\SolicitudServicio;
use App\SolicitudResiduo;
use App\Http\Requests\CertificadoUpdateRequest;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;


class CertificadoController extends Controller
{
    private const MAIL_CERTIFICACIONES_INTERNO = 'certificaciones@prosarc.com.co';

    /**
     * Valida que el usuario tenga un cliente asignado
     */
    private function validateClientAccess()
    {
        $clienteID = userController::IDClienteSegunUsuario();
        if ($clienteID === null && Auth::user()->UsRol == 'Cliente') {
            abort(403, "El usuario no tiene un personal asignado. Contacte al administrador del sistema.");
        }
        $clienteStatus = Cliente::where('ID_Cli', $clienteID)->first('CliStatus');
        if ($clienteStatus && $clienteStatus->CliStatus == 'Bloqueado') {
            abort(403, "el acceso a la lista de certificados se encuentra bloqueado, comuniquese con su asesor comercial en PROSARC S.A. ESP");
        }
        return $clienteID;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       return view('certificados.año');

    }

    /**
     * Certificados optimizados por año: filtros mes, cliente y tipo de documento.
     * Carga diferida: solo ejecuta query cuando el usuario hace Buscar o Ver todos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $anio
     * @return \Illuminate\Http\Response
     */
    protected function certPorAnio(Request $request, $anio)
    {
        $clienteID = $this->validateClientAccess();

        $query = Certificado::where(function($query) use ($clienteID) {
            switch (Auth::user()->UsRol) {
                case 'Cliente':
                    $UserSedeID = $clienteID;
                    $servicioscertificadosdelcliente = SolicitudServicio::where('FK_SolSerCliente', $UserSedeID)
                        ->whereNotNull('SolServCertStatus')
                        ->where('SolServCertStatus', '=', 2)
                        ->where('SolSerStatus', '=', 'Certificacion')
                        ->pluck('ID_SolSer');

                    if ($servicioscertificadosdelcliente->isNotEmpty()) {
                        $query->whereIn('FK_CertSolser', $servicioscertificadosdelcliente);
                    } else {
                        $query->where('ID_Cert', 0);
                    }
                    break;

                case 'Comercial':
                    $clientes = Cliente::where('CliDelete', 0)
                        ->where('CliCategoria', 'Cliente')
                        ->where('CliComercial', Auth::user()->FK_UserPers)
                        ->pluck('ID_Cli');
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;

                case 'Prosarc':
                    $clientes = Cliente::where('CliDelete', 0)
                        ->where('CliCategoria', 'Cliente')
                        ->pluck('ID_Cli');
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;

                default:
                    $clientes = Cliente::where('CliDelete', 0)
                        ->where('CliCategoria', 'Cliente')
                        ->pluck('ID_Cli');
                    $query->whereIn('FK_CertCliente', $clientes);
                    break;
            }
        })
        ->with(['tratamiento', 'SolicitudServicio', 'sedegenerador'])
        ->whereHas('SolicitudServicio.programacionesrecibidas', function ($q) use ($anio, $request) {
            $q->whereYear('ProgVehEntrada', $anio);
            if ($request->filled('mes')) {
                $mes = (int) $request->mes;
                if ($mes >= 1 && $mes <= 12) {
                    $q->whereMonth('ProgVehEntrada', $mes);
                }
            }
        });

        if ($request->filled('cliente')) {
            $query->where('FK_CertCliente', $request->cliente);
        }

        if ($request->filled('tipo') && $request->tipo !== '' && $request->tipo !== 'todos') {
            $tipo = (int) $request->tipo;
            if (in_array($tipo, [0, 1, 2])) {
                $query->where('CertType', $tipo);
            }
        }

        $buscar = $request->has('buscar') && $request->buscar == '1';
        $verTodos = $request->has('ver') && $request->ver === 'todos';
        $esCliente = Auth::user()->UsRol === 'Cliente';
        // Los clientes ven solo sus certificados: cargar automáticamente sin filtros
        $certificados = ($buscar || $verTodos || $esCliente) ? $query->get() : collect();

        $certificados->map(function ($certificado) {
            $fecharecepcionenplanta = $certificado->SolicitudServicio
                ->programacionesrecibidas()
                ->orderBy('ProgVehEntrada', 'asc')
                ->first(['ProgVehEntrada']);
            if ($fecharecepcionenplanta != null) {
                $certificado->recepcion = $fecharecepcionenplanta->ProgVehEntrada;
            } else {
                $certificado->recepcion = $certificado->created_at ?? '';
            }
            $certificado->cliente = $certificado->SolicitudServicio->cliente()->first('CliName')->CliName ?? '';
            $certificado->SolSerStatus = $certificado->SolicitudServicio()->first('SolSerStatus')->SolSerStatus ?? '';
            return $certificado;
        });

        $certificados = $certificados->sortByDesc(function ($c) {
            $r = $c->recepcion ?? '';
            return $r ? (is_object($r) && method_exists($r, 'format') ? $r->format('Y-m-d H:i:s') : (string) $r) : '0000-00-00';
        })->values();

        $qClientes = Certificado::whereHas('SolicitudServicio.programacionesrecibidas', function ($q) use ($anio) {
            $q->whereYear('ProgVehEntrada', $anio);
        })
        ->whereHas('SolicitudServicio.cliente', function ($q) {
            $q->where('CliCategoria', 'Cliente')->where('CliDelete', 0);
        })
        ->select('FK_CertCliente')
        ->distinct()
        ->pluck('FK_CertCliente');

        $clientesQuery = Cliente::whereIn('ID_Cli', $qClientes)->orderBy('CliName');
        if (in_array(Auth::user()->UsRol, ['Comercial'])) {
            $clientesQuery->where('CliComercial', Auth::user()->FK_UserPers);
        }
        $clientesFiltro = $clientesQuery->get(['ID_Cli', 'CliName']);

        $nombresMes = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $mesesFiltro = collect();
        for ($m = 1; $m <= 12; $m++) {
            $mesesFiltro->push((object)['valor' => $m, 'label' => $nombresMes[$m - 1]]);
        }

        $tiposFiltro = collect([
            (object)['valor' => '0', 'label' => 'Certificado'],
            (object)['valor' => '1', 'label' => 'Manifiesto'],
            (object)['valor' => '2', 'label' => 'Certificado de terceros'],
        ]);

        return view('certificados.anioFiltrado', compact('certificados', 'clientesFiltro', 'mesesFiltro', 'tiposFiltro', 'anio'));
    }

     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cert2020(Request $request)
    {
        return $this->certPorAnio($request, 2020);
    }

    public function cert2021(Request $request)
    {
        return $this->certPorAnio($request, 2021);
    }

    public function cert2022(Request $request)
    {
        return $this->certPorAnio($request, 2022);
    }

    public function cert2023(Request $request)
    {
        return $this->certPorAnio($request, 2023);
    }

    public function cert2024(Request $request)
    {
        return $this->certPorAnio($request, 2024);
    }

    public function cert2025(Request $request)
    {
        return $this->certPorAnio($request, 2025);
    }

    public function cert2026(Request $request)
    {
        return $this->certPorAnio($request, 2026);
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


        // return view('certificados.edit', compact('SolicitudServicio'));

        // return redirect()->route('solicitud-servicio.solservdocindex', compact('id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
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
        $query = Certificado::with(['SolicitudServicio' => function ($query){
            $query->with(['SolicitudResiduo' => function ($query){
                $query->where('SolResKgConciliado', '>', 0);
                $query->orWhere('SolResCantiUnidadConciliada', '>', 0);
                $query->with('generespel.respels');
                $query->with('requerimiento');
            }]);

        }, 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento','certdato.solres'])
        ->where('CertSlug', $id);

        // Si el usuario es Cliente, aplicar filtro de liberación y validar que sea su certificado
        // Los usuarios de Prosarc pueden ver todos los certificados, incluso los no liberados
        if (Auth::user()->UsRol == 'Cliente') {
            $UserSedeID = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->where('clientes.CliStatus', 'Autorizado')
                ->where('clientes.CliCategoria', 'Cliente')
                ->value('clientes.ID_Cli');

            // Aplicar filtro de liberación SOLO para clientes: solo certificados con las tres autorizaciones != 0
            $query->where('CertAuthJo', '!=', 0);
            $query->where('CertAuthJl', '!=', 0);
            $query->where('CertAuthDp', '!=', 0);
            
            // Validar que el certificado pertenezca al cliente
            $query->where('FK_CertCliente', $UserSedeID);
        }
        // Los usuarios de Prosarc no tienen restricción de liberación, pueden ver todos los certificados

        $certificado = $query->first();

        // Si no se encuentra el certificado o no cumple los filtros, abortar
        if (!$certificado) {
            abort(404, 'Certificado no encontrado o no disponible');
        }

        $certificado->SolicitudServicio->SolicitudResiduo = $certificado->SolicitudServicio->SolicitudResiduo->map(function ($item) {
			$rm = SolicitudResiduo::where('SolResSlug', $item->SolResSlug)->first('SolResRM');
	        $item->SolResRM2 = $rm->SolResRM;
		  	return $item;
		});

        // return $certificado;
        return view('certificados.show', compact('certificado'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function edit($id)
    {
        if (in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT) || in_array(Auth::user()->UsRol2, Permisos::EDITMANIFCERT)) {
            $certificado = Certificado::with(['SolicitudServicio' => function ($query) {
                $query->with(['SolicitudResiduo' => function ($query) {
                    $query->where('SolResKgConciliado', '>', 0)
                          ->orWhere('SolResCantiUnidadConciliada', '>', 0)
                          ->with('generespel.respels', 'requerimiento');
                }]);
            }, 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento'])
            ->where('CertSlug', $id)
            ->first();

            $ultimoCertificado = Certificado::whereNotNull('CertNumero')->orderBy('CertNumero', 'desc')->first('CertNumero');
            $proximoCertificado = $ultimoCertificado ? $ultimoCertificado->CertNumero + 1 : 1;

            $ultimoManif = Certificado::whereNotNull('CertManifNumero')->orderBy('CertManifNumero', 'desc')->first('CertManifNumero');
            $proximoManif = $ultimoManif ? $ultimoManif->CertManifNumero + 1 : 1;

            $certificado->SolicitudServicio->SolicitudResiduo = $certificado->SolicitudServicio->SolicitudResiduo->map(function ($item) {
                $rm = SolicitudResiduo::where('SolResSlug', $item->SolResSlug)->first('SolResRM');
                $item->SolResRM2 = $rm->SolResRM;
                return $item;
            });

            // Generación del código QR con una sola inicialización
            $url = match ($certificado->CertType) {
                '0' => 'https://sispro.prosarc.com/storage/certificadoRegular/' . $certificado->CertSlug . '.pdf',
                '1' => 'https://sispro.prosarc.com/storage/manifiestosRegular/' . $certificado->CertSlug . '.pdf',
                default => 'https://sispro.prosarc.com/storage/certificadoRegular/' . $certificado->CertSlug . '.pdf'
            };

            $qrCode = new QrCode($url);
            $qrCode->setLogoPath(public_path('img/LogoQR.png')); // Mejor usar public_path para asegurar la ruta
            $qrCode->setLogoSize(30, 30);
            $qrCode->setSize(150);
            $qrCode->setMargin(0);
            $qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK);
            $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));


            // Pasar datos a la vista sin withHeaders
            return view('certificados.edit', compact('certificado', 'proximoCertificado', 'proximoManif', 'qrCode'));
        } else {
            abort(404, "No posee permisos para la edición de certificados");
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
        $certificado = Certificado::with('certdato')->where('CertSlug', $id)->first();
        if (!$certificado) {
            abort(404);
        }

        // Si en el edit el usuario elige "certificado de terceros" (tipo 2) Y el certificado actual es manifiesto (tipo 1):
        // crear línea adicional y conservar la del manifiesto.
        // Si el certificado actual YA es tipo 2, actualizar la misma línea (no crear adicional).
        if ($request->input('CertType') == 2 && $certificado->CertType != 2) {
            $nuevoSlug = hash('sha256', rand() . time());
            $hoja = $nuevoSlug . '.pdf';

            if ($request->hasFile('CertSrc') && $request->file('CertSrc')->isValid()) {
                try {
                    Storage::disk('public')->makeDirectory('certificadoExt');
                    Storage::disk('public')->putFileAs('certificadoExt', $request->file('CertSrc'), $hoja);
                } catch (\Throwable $e) {
                    Log::error('Error guardando certificado de terceros', ['msg' => $e->getMessage(), 'path' => 'certificadoExt']);
                    return redirect()->back()->withInput()->with('error', 'No se pudo guardar el archivo. Verifique permisos en storage/app/public/certificadoExt o contacte al administrador.');
                }
            } else {
                $hoja = 'CertificadoDefault.pdf';
            }

            $certificadoNuevo = new Certificado;
            $certificadoNuevo->CertType = 2;
            $certificadoNuevo->CertNumeroExt = $request->input('CertNumero');
            $certificadoNuevo->CertObservacion = 'Certificado de terceros';
            $certificadoNuevo->CertiEspName = $request->input('CertiEspName');
            $certificadoNuevo->CertiEspValue = $request->input('CertiEspValue');
            $certificadoNuevo->CertNumRm = $request->input('CertNumRm');
            $certificadoNuevo->CertSlug = $nuevoSlug;
            $certificadoNuevo->CertSrc = $hoja;
            $certificadoNuevo->CertSrcManif = 'CertificadoDefault.pdf';
            $certificadoNuevo->CertSrcExt = $hoja;
            $certificadoNuevo->CertNumero = 0;
            $certificadoNuevo->CertManifNumero = 0;
            $certificadoNuevo->CertManifPrepend = '';
            $certificadoNuevo->CertAuthHseq = $certificado->CertAuthHseq;
            $certificadoNuevo->CertAuthJo = $certificado->CertAuthJo;
            $certificadoNuevo->CertAuthJl = $certificado->CertAuthJl;
            $certificadoNuevo->CertAuthDp = $certificado->CertAuthDp;
            $certificadoNuevo->CertAnexo = $certificado->CertAnexo;
            $certificadoNuevo->FK_CertSolser = $certificado->FK_CertSolser;
            $certificadoNuevo->FK_CertCliente = $certificado->FK_CertCliente;
            $certificadoNuevo->FK_CertGenerSede = $certificado->FK_CertGenerSede;
            $certificadoNuevo->FK_CertGestor = $certificado->FK_CertGestor;
            $certificadoNuevo->FK_CertTrat = $certificado->FK_CertTrat;
            $certificadoNuevo->FK_CertTransp = $certificado->FK_CertTransp;
            $certificadoNuevo->save();

            foreach ($certificado->certdato as $dato) {
                $nuevoDato = new Certdato;
                $nuevoDato->FK_DatoCert = $certificadoNuevo->ID_Cert;
                $nuevoDato->FK_DatoCertSolRes = $dato->FK_DatoCertSolRes;
                $nuevoDato->save();
            }

            $log = new audit();
            $log->AuditTabla = 'certificados';
            $log->AuditType = 'certificado de terceros agregado desde edit (línea nueva, se conserva manifiesto)';
            $log->AuditRegistro = $certificadoNuevo->ID_Cert;
            $log->AuditUser = Auth::user()->email;
            $log->Auditlog = json_encode(['CertSlug' => $certificadoNuevo->CertSlug, 'origen_CertSlug' => $certificado->CertSlug]);
            $log->save();

            if ($request->hasFile('CertSrc')) {
                $servicio = SolicitudServicio::where('ID_SolSer', $certificadoNuevo->FK_CertSolser)->first();
                $cliente = Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first();
                Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdated($certificadoNuevo, $servicio, $cliente));
            }

            return redirect()->route('certificados.index')->with('success', 'Se agregó la línea de certificado de terceros. Se conserva la línea del manifiesto.');
        }

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
                        $hoja = $certificado->CertSlug . '.pdf';
                        $file1->move(public_path() . '/storage/certificadoRegular/', $hoja);
                    } else {
                        $hoja = $certificado->CertSlug . '.pdf';
                        $fileanterior = public_path() . '/storage/certificadoRegular/' . $hoja;
                        if (file_exists($fileanterior)) {
                            unlink($fileanterior);
                        }
                        $fileanterior2 = storage_path() . '/app/public/certificadoRegular/' . $hoja;
                        if (file_exists($fileanterior2)) {
                            unlink($fileanterior2);
                        }
                        $file1 = $request['CertSrc'];
                        $file1->move(storage_path() . '/app/public/certificadoRegular/', $hoja);
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
                $certificado->save();
                break;

            case 1:
                $certificado->CertManifNumero = $request->input('CertNumero');
                $certificado->CertNumero = 0;
                if ($request->hasFile('CertSrc') && $request->file('CertSrc')->isValid()) {
                    // Al subir documento en el edit del manifiesto: crear línea nueva (cert. terceros) y no reemplazar el manifiesto
                    try {
                        $nuevoSlug = hash('sha256', rand() . time());
                        $hoja = $nuevoSlug . '.pdf';
                        Storage::disk('public')->makeDirectory('certificadoExt');
                        Storage::disk('public')->putFileAs('certificadoExt', $request->file('CertSrc'), $hoja);
                    } catch (\Throwable $e) {
                        Log::error('Error guardando cert. terceros desde manifiesto', ['msg' => $e->getMessage()]);
                        return redirect()->back()->withInput()->with('error', 'No se pudo guardar el archivo. Verifique permisos en storage o contacte al administrador.');
                    }

                    $certificadoNuevo = new Certificado;
                    $certificadoNuevo->CertType = 2;
                    $certificadoNuevo->CertNumeroExt = $request->input('CertNumero');
                    $certificadoNuevo->CertObservacion = 'Certificado de terceros';
                    $certificadoNuevo->CertiEspName = $certificado->CertiEspName;
                    $certificadoNuevo->CertiEspValue = $certificado->CertiEspValue;
                    $certificadoNuevo->CertNumRm = $certificado->CertNumRm;
                    $certificadoNuevo->CertSlug = $nuevoSlug;
                    $certificadoNuevo->CertSrc = $hoja;
                    $certificadoNuevo->CertSrcManif = 'CertificadoDefault.pdf';
                    $certificadoNuevo->CertSrcExt = $hoja;
                    $certificadoNuevo->CertNumero = 0;
                    $certificadoNuevo->CertManifNumero = 0;
                    $certificadoNuevo->CertManifPrepend = '';
                    $certificadoNuevo->CertAuthHseq = $certificado->CertAuthHseq;
                    $certificadoNuevo->CertAuthJo = $certificado->CertAuthJo;
                    $certificadoNuevo->CertAuthJl = $certificado->CertAuthJl;
                    $certificadoNuevo->CertAuthDp = $certificado->CertAuthDp;
                    $certificadoNuevo->CertAnexo = $certificado->CertAnexo;
                    $certificadoNuevo->FK_CertSolser = $certificado->FK_CertSolser;
                    $certificadoNuevo->FK_CertCliente = $certificado->FK_CertCliente;
                    $certificadoNuevo->FK_CertGenerSede = $certificado->FK_CertGenerSede;
                    $certificadoNuevo->FK_CertGestor = $certificado->FK_CertGestor;
                    $certificadoNuevo->FK_CertTrat = $certificado->FK_CertTrat;
                    $certificadoNuevo->FK_CertTransp = $certificado->FK_CertTransp;
                    $certificadoNuevo->save();

                    foreach ($certificado->certdato as $dato) {
                        $nuevoDato = new Certdato;
                        $nuevoDato->FK_DatoCert = $certificadoNuevo->ID_Cert;
                        $nuevoDato->FK_DatoCertSolRes = $dato->FK_DatoCertSolRes;
                        $nuevoDato->save();
                    }

                    $log = new audit();
                    $log->AuditTabla = 'certificados';
                    $log->AuditType = 'certificado de terceros agregado desde edit manifiesto (línea nueva)';
                    $log->AuditRegistro = $certificadoNuevo->ID_Cert;
                    $log->AuditUser = Auth::user()->email;
                    $log->Auditlog = json_encode(['CertSlug' => $certificadoNuevo->CertSlug, 'manifiesto_CertSlug' => $certificado->CertSlug]);
                    $log->save();

                    if ($request->hasFile('CertSrc')) {
                        $servicio = SolicitudServicio::where('ID_SolSer', $certificadoNuevo->FK_CertSolser)->first();
                        $cliente = Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first();
                        Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdated($certificadoNuevo, $servicio, $cliente));
                    }

                    return redirect()->route('certificados.index')->with('success', 'Se agregó la línea de certificado de terceros. El manifiesto se mantiene sin cambios.');
                }
                if ($certificado->CertSrc == 'CertificadoDefault.pdf') {
                    $hoja = 'CertificadoDefault.pdf';
                } else {
                    $hoja = $certificado->CertSrc;
                }
                $certificado->CertSrc = $hoja;
                $certificado->save();
                break;

            // CertType 2 se maneja al inicio: crea línea nueva y conserva manifiesto (no se reemplaza aquí)
            case 2:
                $certificado->CertNumeroExt = $request->input('CertNumero');
                if ($request->hasFile('CertSrc') && $request->file('CertSrc')->isValid()) {
                    try {
                        $hoja = $certificado->CertSlug . '.pdf';
                        if ($certificado->CertSrcExt != 'CertificadoDefault.pdf') {
                            Storage::disk('public')->delete('certificadoExt/' . $certificado->CertSrcExt);
                        }
                        Storage::disk('public')->makeDirectory('certificadoExt');
                        Storage::disk('public')->putFileAs('certificadoExt', $request->file('CertSrc'), $hoja);
                    } catch (\Throwable $e) {
                        Log::error('Error actualizando archivo cert. terceros', ['msg' => $e->getMessage(), 'CertSlug' => $certificado->CertSlug]);
                        return redirect()->back()->withInput()->with('error', 'No se pudo guardar el archivo. Verifique permisos en storage o contacte al administrador.');
                    }
                    // Asignar el valor de $hoja a la columna CertSrcExt
                    $certificado->CertSrcExt = $hoja;

                    // Asignar otros valores de autenticaci��n
                    $certificado->CertAuthHseq = 0;
                    $certificado->CertAuthJo = 0;
                    $certificado->CertAuthJl = 0;
                    $certificado->CertAuthDp = 0;
                }
                $certificado->CertNumeroExt = $request->input('CertNumero');
                $certificado->CertiEspName = $request->input('CertiEspName');
                $certificado->CertiEspValue = $request->input('CertiEspValue');
                $certificado->CertNumRm = $request->input('CertNumRm');
                $certificado->save();
                break;
        }

        if ($request->hasFile('CertSrc')) {
            $servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
            $cliente = Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first();
            Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdated($certificado, $servicio, $cliente));
        }


        $log = new audit();
        $log->AuditTabla="certificados";
        $log->AuditType="actualizado";
        $log->AuditRegistro=$certificado->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($id);
        $log->save();

        // return view('certificados.edit', compact('certificado'));
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
        $certificado = Certificado::where('CertSlug', $id)->first();
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
                    // Completar firmas faltantes: Dirección (1), Logística (2), Operaciones (3)
                    // Similar a DireccionTecnica para que queden los 3 chulitos
                    if ($certificado->CertAuthDp == 0) { $certificado->CertAuthDp = 1; }
                    if ($certificado->CertAuthJl == 0) { $certificado->CertAuthJl = 2; }
                    if ($certificado->CertAuthJo == 0) { $certificado->CertAuthJo = 3; }
                    break;

                case 'DireccionTecnica':
                    // Completar firmas faltantes: Dirección (1), Logística (2), Operaciones (3)
                    if ($certificado->CertAuthDp == 0) {
                        $certificado->CertAuthDp = 1;
                    }
                    if ($certificado->CertAuthJl == 0) {
                        $certificado->CertAuthJl = 2;
                    }
                    if ($certificado->CertAuthJo == 0) {
                        $certificado->CertAuthJo = 3;
                    }
                    break;

                case 'Supervisor':
                    // Completar firmas faltantes: Dirección (4), Logística (4), Operaciones (4)
                    // Similar a DireccionTecnica para que queden los 3 chulitos
                    // Nota: Supervisor de Turno usa código 4 en las firmas
                    if ($certificado->CertAuthDp == 0) { $certificado->CertAuthDp = 1; }
                    if ($certificado->CertAuthJl == 0) { $certificado->CertAuthJl = 2; }
                    if ($certificado->CertAuthJo == 0) { $certificado->CertAuthJo = 3; }
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
                    // Completar firmas faltantes: Dirección (1), Logística (2), Operaciones (3)
                    // Similar a DireccionTecnica para que queden los 3 chulitos
                    if ($certificado->CertAuthDp == 0) { $certificado->CertAuthDp = 1; }
                    if ($certificado->CertAuthJl == 0) { $certificado->CertAuthJl = 2; }
                    if ($certificado->CertAuthJo == 0) { $certificado->CertAuthJo = 3; }
                    break;

                case 'DireccionTecnica':
                    // Completar firmas faltantes: Dirección (1), Logística (2), Operaciones (3)
                    if ($certificado->CertAuthDp == 0) {
                        $certificado->CertAuthDp = 1;
                    }
                    if ($certificado->CertAuthJl == 0) {
                        $certificado->CertAuthJl = 2;
                    }
                    if ($certificado->CertAuthJo == 0) {
                        $certificado->CertAuthJo = 3;
                    }
                    break;

                case 'Supervisor':
                    // Completar firmas faltantes: Dirección (4), Logística (4), Operaciones (4)
                    // Similar a DireccionTecnica para que queden los 3 chulitos
                    if ($certificado->CertAuthDp == 0) { $certificado->CertAuthDp = 1; }
                    if ($certificado->CertAuthJl == 0) { $certificado->CertAuthJl = 2; }
                    if ($certificado->CertAuthJo == 0) { $certificado->CertAuthJo = 3; }
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
        $log->AuditTabla="certificado";
        $log->AuditType="firmado";
        $log->AuditRegistro=$certificado->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($id);
        $log->save();

        if ($certificado->CertAuthJo != 0 && $certificado->CertAuthJl != 0 && $certificado->CertAuthDp != 0 ) {
            $servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
            $cliente = $servicio ? Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first() : null;
            if ($servicio && $cliente) {
                Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdatedComercial($certificado, $servicio, $cliente));
            }
        }


        return response()->json([
            'code' => 200,
            'message' => 'Documento firmado correctamente',
            'Documento' => $certificado,
            'newtoken' => csrf_token(),
        ]);
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
        $certificado = Certificado::where('CertSlug', $id)->first();
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
        $log->AuditTabla="certificado";
        $log->AuditType="firmado";
        $log->AuditRegistro=$certificado->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($id);
        $log->save();

        if ($certificado->CertAuthJo != 0 && $certificado->CertAuthJl != 0 && $certificado->CertAuthDp != 0 ) {
            $servicio = SolicitudServicio::where('ID_SolSer', $certificado->FK_CertSolser)->first();
            $cliente = $servicio ? Cliente::where('ID_Cli', $servicio->FK_SolSerCliente)->first() : null;
            if ($servicio && $cliente) {
                Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new CertUpdatedComercial($certificado, $servicio, $cliente));
            }
        }

        return response()->json([
            'code' => 200,
            'message' => 'Documento firmado correctamente',
            'Documento' => $certificado,
            'newtoken' => csrf_token(),
        ]);
    }


        /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function wordtemplate($id)
    {
        $certificado = Certificado::with(['SolicitudServicio' => function ($query){
            $query->with(['SolicitudResiduo' => function ($query){
                $query->where('SolResKgConciliado', '>', 0);
                $query->orWhere('SolResCantiUnidadConciliada', '>', 0);
                $query->with('generespel.respels');
                $query->with('requerimiento');
            }]);

        }, 'cliente.sedes.Municipios.Departamento', 'sedegenerador.generadors', 'sedegenerador.municipio.Departamento', 'gestor.sedes.Municipios.Departamento', 'tratamiento', 'transportador.sedes.Municipios.Departamento','certdato.solres'])
        ->where('CertSlug', $id)
        ->first();

        // Validar que el certificado existe
        if (!$certificado) {
            return response()->json(['error' => 'Certificado no encontrado'], 404);
        }

        // Validar que tiene SolicitudServicio
        if (!$certificado->SolicitudServicio) {
            return response()->json(['error' => 'Solicitud de servicio no encontrada'], 404);
        }

        // Validar que tiene tratamiento
        if (!$certificado->tratamiento) {
            return response()->json(['error' => 'Tratamiento no encontrado'], 404);
        }

        $fecharecepcionenplanta = $certificado->SolicitudServicio->programacionesrecibidas()->first('ProgVehSalida');
        if ($fecharecepcionenplanta != null) {
            $fechaLlegadaPlanta = $fecharecepcionenplanta->ProgVehSalida;
        }else{
            $certificado->recepcion = "";
        }

        // return $certificado;
        try {
            switch ($certificado->tratamiento->TratName) {
                case 'TermoDestrucción':
                    return view('certificados.imprimible', compact('certificado', 'fechaLlegadaPlanta'));
                    break;
                case 'Posconsumo luminarias':
                    return view('certificados.luminarias', compact('certificado', 'fechaLlegadaPlanta'));
                    break;
                default:
                    return view('certificados.manifiesto', compact('certificado', 'fechaLlegadaPlanta'));
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error en wordtemplate Certificado: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error al cargar el template: ' . $e->getMessage()], 500);
        }
    }


    public function independiente(Request $request, $id)
	{
        $certificadoOld = Certificado::where('ID_Cert', $id)->first();

        $certificadoNew = $certificadoOld->replicate()->fill([
            'CertSlug' => hash('sha256', rand().time()),
            'created_at' => now(),
            'updated_at' => now(),
            'CertNumero' => 0,
            'CertManifNumero' => 0,
            'CertObservacion' => 'Documento para residuos independientes',
            'CertSrc' => 'CertificadoDefault.pdf',
            'CertSrcManif' => 'CertificadoDefault.pdf',
            'CertAuthHseq' => 0,
            'CertAuthJo' => 0,
            'CertAuthJl' => 0,
            'CertAuthDp' => 0,
        ]);
        $certificadoNew->save();

        foreach ($request->input('residuos') as $key => $value) {
            $certdato = Certdato::where('ID_CertDato', $value)->first();
            $certdato->FK_DatoCert = $certificadoNew->ID_Cert;
            $certdato->save();
        }

        $log = new audit();
        $log->AuditTabla="certificados";
        $log->AuditType="generado Cert independiente";
        $log->AuditRegistro=$certificadoOld->ID_Cert;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($request);
        $log->save();

        return redirect()->route('certificados.show', ['certificado' => $certificadoNew->CertSlug]);
	}

	/**
	 * Actualizar solo el archivo del certificado (sin cambiar slug ni estado)
	 * Para usuarios de conciliaciones - requiere aprobación
	 */
	public function updateFile(Request $request, $id)
	{
		// Verificar que el usuario tiene permiso (AREALOGISTICA)
		if (!in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) && !in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA)) {
			abort(403, 'No tiene permiso para actualizar archivos de certificados');
		}

		$certificado = Certificado::with('certdato')->where('CertSlug', $id)->first();
		if (!$certificado) {
			abort(404);
		}

		$request->validate([
			'CertSrc' => 'required|file|mimes:pdf|max:10240',
			'CertType' => 'required|integer|in:0,1,2'
		]);

		switch ($request->input('CertType')) {
			case 0: // Certificado regular
				$hoja = $certificado->CertSlug . '.pdf';
				$path = storage_path('app/public/certificadoRegular/');
				
				// Crear directorio si no existe
				if (!file_exists($path)) {
					mkdir($path, 0755, true);
				}
				
				// Eliminar archivo anterior si existe
				if ($certificado->CertSrc != 'CertificadoDefault.pdf' && file_exists($path . $hoja)) {
					unlink($path . $hoja);
				}
				
				// Guardar nuevo archivo
				$request->file('CertSrc')->move($path, $hoja);
				$certificado->CertSrc = $hoja;
				break;

			case 1: // Manifiesto (igual que certificados: storage, no public)
				$hoja = $certificado->CertSlug . '.pdf';
				$path = storage_path('app/public/manifiestosRegular/');
				if (!file_exists($path)) {
					mkdir($path, 0755, true);
				}
				
				// Eliminar archivo anterior si existe
				if ($certificado->CertSrcManif != 'CertificadoDefault.pdf' && file_exists($path . $certificado->CertSrcManif)) {
					unlink($path . $certificado->CertSrcManif);
				}
				
				// Guardar nuevo archivo
				$request->file('CertSrc')->move($path, $hoja);
				$certificado->CertSrcManif = $hoja;
				$certificado->CertSrc = $hoja; // Para manifiestos 2024+ el show usa CertSrc
				break;

			case 2: // Certificado de terceros: crear línea adicional, no reemplazar la del manifiesto
				try {
					$nuevoSlug = hash('sha256', rand() . time());
					$hoja = $nuevoSlug . '.pdf';
					Storage::disk('public')->makeDirectory('certificadoExt');
					Storage::disk('public')->putFileAs('certificadoExt', $request->file('CertSrc'), $hoja);
				} catch (\Throwable $e) {
					Log::error('Error en updateFile cert. terceros', ['msg' => $e->getMessage()]);
					return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifique permisos en storage.');
				}

				$certificadoNuevo = new Certificado;
				$certificadoNuevo->CertType = 2;
				$certificadoNuevo->CertObservacion = 'Certificado de terceros';
				$certificadoNuevo->CertNumero = 0;
				$certificadoNuevo->CertManifNumero = 0;
				$certificadoNuevo->CertManifPrepend = '';
				$certificadoNuevo->CertiEspName = $certificado->CertiEspName;
				$certificadoNuevo->CertiEspValue = $certificado->CertiEspValue;
				$certificadoNuevo->CertSlug = $nuevoSlug;
				$certificadoNuevo->CertSrc = $hoja;
				$certificadoNuevo->CertSrcManif = 'CertificadoDefault.pdf';
				$certificadoNuevo->CertSrcExt = $hoja;
				$certificadoNuevo->CertAuthHseq = $certificado->CertAuthHseq;
				$certificadoNuevo->CertAuthJo = $certificado->CertAuthJo;
				$certificadoNuevo->CertAuthJl = $certificado->CertAuthJl;
				$certificadoNuevo->CertAuthDp = $certificado->CertAuthDp;
				$certificadoNuevo->CertAnexo = $certificado->CertAnexo;
				$certificadoNuevo->FK_CertSolser = $certificado->FK_CertSolser;
				$certificadoNuevo->FK_CertCliente = $certificado->FK_CertCliente;
				$certificadoNuevo->FK_CertGenerSede = $certificado->FK_CertGenerSede;
				$certificadoNuevo->FK_CertGestor = $certificado->FK_CertGestor;
				$certificadoNuevo->FK_CertTrat = $certificado->FK_CertTrat;
				$certificadoNuevo->FK_CertTransp = $certificado->FK_CertTransp;
				$certificadoNuevo->save();

				foreach ($certificado->certdato as $dato) {
					$nuevoDato = new Certdato;
					$nuevoDato->FK_DatoCert = $certificadoNuevo->ID_Cert;
					$nuevoDato->FK_DatoCertSolRes = $dato->FK_DatoCertSolRes;
					$nuevoDato->save();
				}

				$certificado = $certificadoNuevo;
				break;
		}

		if ($request->input('CertType') != 2) {
			// Resetear autorizaciones solo cuando se actualiza el mismo certificado (casos 0 y 1)
			$certificado->CertAuthHseq = 0;
			$certificado->CertAuthJo = 0;
			$certificado->CertAuthJl = 0;
			$certificado->CertAuthDp = 0;
			$certificado->save();
		}

		// Registrar en audit
		$log = new audit();
		$log->AuditTabla = "certificados";
		$log->AuditType = $request->input('CertType') == 2 ? "certificado de terceros subido (línea nueva)" : "archivo actualizado (pendiente aprobación)";
		$log->AuditRegistro = $certificado->ID_Cert;
		$log->AuditUser = Auth::user()->email;
		$log->Auditlog = json_encode(['CertSlug' => $certificado->CertSlug, 'CertType' => $request->input('CertType')]);
		$log->save();

		return redirect()->route('certificados.show', ['certificado' => $certificado->CertSlug])
			->with('success', $request->input('CertType') == 2 ? 'Certificado de terceros agregado correctamente (nueva línea).' : 'Archivo actualizado correctamente. Pendiente de aprobación.');
	}

	/**
	 * Aprobar archivo actualizado (JefeLogistica o AdministradorPlanta)
	 */
	public function approveFile($id)
	{
		$certificado = Certificado::where('CertSlug', $id)->first();
		if (!$certificado) {
			abort(404);
		}

		// Verificar que el usuario tiene permiso para aprobar
		$canApprove = false;
		if (Auth::user()->UsRol == 'JefeLogistica' || Auth::user()->UsRol2 == 'JefeLogistica') {
			$certificado->CertAuthJl = 2;
			$canApprove = true;
		}
		if (Auth::user()->UsRol == 'AdministradorPlanta' || Auth::user()->UsRol2 == 'AdministradorPlanta') {
			$certificado->CertAuthDp = 1;
			$canApprove = true;
		}

		if (!$canApprove) {
			abort(403, 'No tiene permiso para aprobar archivos');
		}

		$certificado->save();

		// Registrar en audit
		$log = new audit();
		$log->AuditTabla = "certificados";
		$log->AuditType = "archivo aprobado";
		$log->AuditRegistro = $certificado->ID_Cert;
		$log->AuditUser = Auth::user()->email;
		$log->Auditlog = json_encode(['CertSlug' => $certificado->CertSlug]);
		$log->save();

		return redirect()->route('certificados.show', ['certificado' => $certificado->CertSlug])
			->with('success', 'Archivo aprobado correctamente.');
	}

}