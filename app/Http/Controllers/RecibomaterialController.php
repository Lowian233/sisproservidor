<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\SolServStoreRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\userController;
use App\Cliente;
use App\FirmasServicios;
use App\Permisos;
use PDF;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

class RecibomaterialController extends Controller
{
    /**
     * Estados de solicitud que indican que el servicio fue realizado (material recibido/aprobado).
     * Excluye Programado, Notificado, etc. para no mostrar firmas de servicios no ejecutados.
     */
    private const STATUS_RECIBO_VALIDOS = [
        'Recepcionado', 'Completado', 'Corregido',
        'Conciliado', 'No Conciliado', 'Tratado', 'Certificacion', 'Facturado'
    ];

    private function baseQueryRecibosMaterial()
    {
        // Subconsulta para obtener una sola fecha de recepci��n por servicio (evita duplicados por progvehiculos)
        $subProg = DB::table('progvehiculos')
            ->select('FK_ProgServi', DB::raw('MIN(ProgVehEntrada) as ProgVehEntrada'))
            ->where('ProgVehDelete', 0)
            ->groupBy('FK_ProgServi');

        return DB::table('firmas_servicio')
            ->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
            ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
            ->leftJoinSub($subProg, 'progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
            ->whereIn('solicitud_servicios.SolSerStatus', self::STATUS_RECIBO_VALIDOS)
            ->where('clientes.CliCategoria', '!=', 'ClientePrepago')
            ->select(
                'firmas_servicio.ID_Firmas',
                'firmas_servicio.FK_SolSer',
                'firmas_servicio.FK_SGener',
                'firmas_servicio.FK_Gener',
                'firmas_servicio.SlugFirmas',
                'firmas_servicio.FirmaCliente',
                'firmas_servicio.FirmaConductor',
                'firmas_servicio.FirmaPDA',
                'firmas_servicio.created_at',
                'firmas_servicio.updated_at',
                'solicitud_servicios.SolSerSlug',
                'clientes.CliName',
                DB::raw('COALESCE(progvehiculos.ProgVehEntrada, firmas_servicio.created_at) as ProgVehFecha')
            );
    }

    /**
     * Deduplica recibos por servicio: una fila por FK_SolSer.
     * Prioriza el SlugFirmas cuyo archivo PDF existe; si ninguno existe, preferir UUID sobre hash.
     */
    private function deduplicarRecibosPorServicio($rms)
    {
        $porServicio = [];
        foreach ($rms as $rm) {
            $idSer = $rm->FK_SolSer;
            $slug = $rm->SlugFirmas ?? '';
            $existe = !empty($slug) && Storage::disk('public')->exists('RecibosdeMaterial/' . $slug . '.pdf');
            $esUuid = $slug && strpos($slug, '-') !== false;

            if (!isset($porServicio[$idSer])) {
                $porServicio[$idSer] = $rm;
                continue;
            }
            $actual = $porServicio[$idSer];
            $actualExiste = !empty($actual->SlugFirmas) && Storage::disk('public')->exists('RecibosdeMaterial/' . $actual->SlugFirmas . '.pdf');
            $actualEsUuid = $actual->SlugFirmas && strpos($actual->SlugFirmas, '-') !== false;

            if ($existe && !$actualExiste) {
                $porServicio[$idSer] = $rm;
            } elseif ($existe && $actualExiste && $esUuid && !$actualEsUuid) {
                $porServicio[$idSer] = $rm;
            } elseif (!$existe && !$actualExiste && $esUuid && !$actualEsUuid) {
                $porServicio[$idSer] = $rm;
            }
        }
        return collect(array_values($porServicio));
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
        // Ahora el index es selector por a�0�9o (para optimizar rendimiento)
        $query = $this->baseQueryRecibosMaterial();

        // Restringir por permisos del cliente (cuando aplica)
        if (Auth::user()->UsRol == 'Cliente') {
            $clienteID = userController::IDClienteSegunUsuario();
            if ($clienteID === null) {
                abort(403, "El usuario no tiene un personal asignado. Contacte al administrador del sistema.");
            }
            $UserSedeID = $clienteID;

            if (Schema::hasColumn('solicitud_servicios', 'Fk_SolSerTransportador')) {
                $query = $query->leftJoin('sedes as sedes_transp', 'sedes_transp.ID_Sede', '=', 'solicitud_servicios.Fk_SolSerTransportador')
                    ->where(function($q) use ($UserSedeID) {
                        $q->where('solicitud_servicios.FK_SolSerCliente', $UserSedeID)
                          ->orWhere('sedes_transp.FK_SedeCli', $UserSedeID);
                    });
            } else {
                $query = $query->where('solicitud_servicios.FK_SolSerCliente', $UserSedeID);
            }
        }

        // A�0�9os disponibles por fecha de recepci��n (o created_at si no hay recepci��n)
        $years = (clone $query)
            ->select(DB::raw('DISTINCT YEAR(COALESCE(progvehiculos.ProgVehEntrada, firmas_servicio.created_at)) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        return view('recibomaterial.ano', ['years' => $years]);
    }

    /**
     * Listado por a�0�9o de recepci��n
     */
    public function indexYear($year)
    {
        $year = (int) $year;
        if ($year < 2000 || $year > 2100) {
            abort(404);
        }

        $query = $this->baseQueryRecibosMaterial();

        if (Auth::user()->UsRol == 'Cliente') {
            $clienteID = userController::IDClienteSegunUsuario();
            if ($clienteID === null) {
                abort(403, "El usuario no tiene un personal asignado. Contacte al administrador del sistema.");
            }
            $UserSedeID = $clienteID;

            if (Schema::hasColumn('solicitud_servicios', 'Fk_SolSerTransportador')) {
                $query = $query->leftJoin('sedes as sedes_transp', 'sedes_transp.ID_Sede', '=', 'solicitud_servicios.Fk_SolSerTransportador')
                    ->where(function($q) use ($UserSedeID) {
                        $q->where('solicitud_servicios.FK_SolSerCliente', $UserSedeID)
                          ->orWhere('sedes_transp.FK_SedeCli', $UserSedeID);
                    });
            } else {
                $query = $query->where('solicitud_servicios.FK_SolSerCliente', $UserSedeID);
            }
        }

        $rms = $query
            ->whereYear(DB::raw('COALESCE(progvehiculos.ProgVehEntrada, firmas_servicio.created_at)'), $year)
            ->orderBy('firmas_servicio.created_at', 'desc')
            ->get();

        return view('recibomaterial.index', compact('rms', 'year'));
    }
    /**
     * �0�1ndice de Recibos de Material Express (misma l��gica que index, pero vista express)
     */
    public function indexExpress()
    {
        // Una fecha por servicio para evitar duplicados por múltiples programaciones
        $subProg = DB::table('progvehiculos')
            ->select('FK_ProgServi', DB::raw('MIN(ProgVehEntrada) as ProgVehEntrada'))
            ->where('ProgVehDelete', 0)
            ->groupBy('FK_ProgServi');

        $query = DB::table('firmas_servicio')
            ->join('solicitud_servicios', 'solicitud_servicios.ID_SolSer', '=', 'firmas_servicio.FK_SolSer')
            ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
            ->leftJoinSub($subProg, 'progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
            ->select(
                'firmas_servicio.*',
                'solicitud_servicios.SolSerSlug',
                'clientes.CliName',
                DB::raw('COALESCE(progvehiculos.ProgVehEntrada, firmas_servicio.created_at) as ProgVehFecha')
            )
            // En Express listamos por documento generado (SlugFirmas), no por FirmaCliente.
            ->whereNotNull('firmas_servicio.SlugFirmas')
            ->orderBy('firmas_servicio.created_at', 'desc');

        // Si es usuario cliente, restringir a su cliente
        if (Auth::user()->UsRol == 'Cliente') {
            $clienteID = userController::IDClienteSegunUsuario();
            if ($clienteID !== null) {
                $query->where('solicitud_servicios.FK_SolSerCliente', $clienteID);
            }
        }

        $rms = $query->get()->filter(function ($rm) {
            $slug = $rm->SlugFirmas ?? null;
            if (empty($slug)) {
                return false;
            }

            return Storage::disk('public')->exists('RecibosMaterialExpress/' . $slug . '.pdf')
                || Storage::disk('public')->exists('RecibosdeMaterialExpress/' . $slug . '.pdf');
        })->values();

        return view('recibomaterialexpress.index', compact('rms'));
    }
     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Mostrar PDF de recibo de material sin depender del symlink /storage.
     */
    public function file($slugFirmas)
    {
        $nombre = trim((string) $slugFirmas) . '.pdf';

        // Ruta canónica (disco public de Laravel)
        $canonical = storage_path('app/public/RecibosdeMaterial/' . $nombre);
        if (file_exists($canonical)) {
            return response()->file($canonical, ['Content-Type' => 'application/pdf']);
        }

        // Fallback legacy (si existe copia directa en public)
        $legacy = public_path('storage/RecibosdeMaterial/' . $nombre);
        if (file_exists($legacy)) {
            return response()->file($legacy, ['Content-Type' => 'application/pdf']);
        }

        abort(404, 'Recibo de material no encontrado');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Tarifa  $tarifa
     * @return \Illuminate\Http\Response
     */
   /*  public function show(Tarifa $tarifa)
    {
    } */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Tarifa  $tarifa
     * @return \Illuminate\Http\Response
     */
    /* public function edit(Tarifa $tarifa)
    {
    }
 */
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Tarifa  $tarifa
     * @return \Illuminate\Http\Response
     */
    /* public function update(Request $request, Tarifa $tarifa)
    {
    }
 */
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Tarifa  $tarifa
     * @return \Illuminate\Http\Response
     */
    /* public function destroy(Tarifa $tarifa)
    {
    } */

	/**
	 * Actualizar solo el archivo del recibo de material (sin cambiar slug)
	 * Para usuarios de conciliaciones - requiere aprobaci��n
	 */
	public function updateFile(Request $request, $slugFirmas)
	{
		// Verificar que el usuario tiene permiso (AREALOGISTICA)
		if (!in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) && !in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA)) {
			abort(403, 'No tiene permiso para actualizar archivos de recibos de material');
		}

		$firmas = FirmasServicios::where('SlugFirmas', $slugFirmas)->first();
		if (!$firmas) {
			abort(404, 'Recibo de material no encontrado');
		}

		$request->validate([
			'ReciboPdf' => 'required|file|mimes:pdf|max:10240'
		]);

		$nombre = $firmas->SlugFirmas . '.pdf';
		$path = storage_path('app/public/RecibosdeMaterial/');

		// Crear directorio si no existe
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}

		// Eliminar archivo anterior si existe
		if (file_exists($path . $nombre)) {
			unlink($path . $nombre);
		}

		// Guardar nuevo archivo
		$request->file('ReciboPdf')->move($path, $nombre);

		// Actualizar fecha de actualizaci��n
		// Nota: evitamos $firmas->touch() porque algunos entornos est��n resolviendo
		// una PK incorrecta (ID_Firma) y falla el update. Actualizamos por SlugFirmas.
		DB::table('firmas_servicio')
			->where('SlugFirmas', $firmas->SlugFirmas)
			->update(['updated_at' => now()]);

		// Registrar en audit
		$log = new \App\audit();
		$log->AuditTabla = "firmas_servicio";
		$log->AuditType = "archivo recibo actualizado (pendiente aprobaci��n)";
		$log->AuditRegistro = $firmas->ID_Firmas;
		$log->AuditUser = Auth::user()->email;
		$log->Auditlog = json_encode(['SlugFirmas' => $firmas->SlugFirmas, 'FK_SolSer' => $firmas->FK_SolSer]);
		$log->save();

		return redirect()->route('recibomaterial.index')
			->with('success', 'Archivo de recibo actualizado correctamente. Pendiente de aprobaci��n.');
	}

	/**
	 * Aprobar archivo de recibo actualizado (JefeLogistica o AdministradorPlanta)
	 */
	public function approveFile($slugFirmas)
	{
		$firmas = FirmasServicios::where('SlugFirmas', $slugFirmas)->first();
		if (!$firmas) {
			abort(404, 'Recibo de material no encontrado');
		}

		// Verificar que el usuario tiene permiso para aprobar
		$canApprove = false;
		if (Auth::user()->UsRol == 'JefeLogistica' || Auth::user()->UsRol2 == 'JefeLogistica') {
			$canApprove = true;
		}
		if (Auth::user()->UsRol == 'AdministradorPlanta' || Auth::user()->UsRol2 == 'AdministradorPlanta') {
			$canApprove = true;
		}

		if (!$canApprove) {
			abort(403, 'No tiene permiso para aprobar archivos');
		}

		// Actualizar fecha de actualizaci��n
		DB::table('firmas_servicio')
			->where('SlugFirmas', $firmas->SlugFirmas)
			->update(['updated_at' => now()]);

		// Registrar en audit
		$log = new \App\audit();
		$log->AuditTabla = "firmas_servicio";
		$log->AuditType = "archivo recibo aprobado";
		$log->AuditRegistro = $firmas->ID_Firmas;
		$log->AuditUser = Auth::user()->email;
		$log->Auditlog = json_encode(['SlugFirmas' => $firmas->SlugFirmas, 'FK_SolSer' => $firmas->FK_SolSer]);
		$log->save();

		return redirect()->route('recibomaterial.index')
			->with('success', 'Archivo de recibo aprobado correctamente.');
	}

}
