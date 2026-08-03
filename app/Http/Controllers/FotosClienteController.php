<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Permisos;

class FotosClienteController extends Controller
{
    /**
     * Mostrar fotos de solicitudes del cliente
     */
    public function index(Request $request)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol, Permisos::CLIENTE) &&
            !in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            abort(403, 'Acceso denegado');
        }

        // Inicializar query base
        $fotosQuery = DB::table('recursos as r')
            ->join('solicitud_residuos as sr', 'r.FK_RecSolRes', '=', 'sr.ID_SolRes')
            ->join('solicitud_servicios as ss', 'sr.FK_SolResSolSer', '=', 'ss.ID_SolSer')
            ->join('clientes as c', 'ss.FK_SolSerCliente', '=', 'c.ID_Cli')
            ->select([
                'r.ID_Rec',
                'r.RecSrc',
                'r.RecRmSrc',
                'r.RecTipo',
                'r.RecCarte',
                'r.created_at',
                'sr.ID_SolRes',
                'ss.ID_SolSer',
                'ss.SolSerSlug',
                'ss.SolSerStatus',
                'c.CliName',
                'c.ID_Cli'
            ])
            ->where('r.RecCarte', 'Foto')
            ->where('r.RecTipo', 'Pesaje-Descargue')
            ->whereYear('ss.created_at', '>=', 2024);

        // Si es un cliente, filtrar solo sus fotos
        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            $clienteID = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->value('clientes.ID_Cli');

            if ($clienteID) {
                $fotosQuery->where('c.ID_Cli', $clienteID);
            } else {
                abort(403, 'Cliente no encontrado');
            }
        }

        // Aplicar filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->get('search');
            $fotosQuery->where(function($query) use ($search) {
                // Si es un número, buscar por ID de solicitud
                if (is_numeric($search)) {
                    $query->where('ss.ID_SolSer', $search);
                } else {
                    // Si no es número, buscar por slug o nombre de cliente
                    $query->where('ss.SolSerSlug', 'like', '%' . $search . '%')
                          ->orWhere('c.CliName', 'like', '%' . $search . '%');
                }
            });
        }

        if ($request->filled('cliente')) {
            $fotosQuery->where('c.ID_Cli', $request->get('cliente'));
        }

        if ($request->filled('fecha_desde')) {
            $fotosQuery->whereDate('ss.created_at', '>=', $request->get('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $fotosQuery->whereDate('ss.created_at', '<=', $request->get('fecha_hasta'));
        }

        $fotos = $fotosQuery->orderBy('ss.created_at', 'desc')->paginate(20);

        // Obtener lista de clientes para el filtro (solo para usuarios de PROSARC)
        $clientes = collect();
        if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC)) {
            $clientes = DB::table('clientes')
                ->select('ID_Cli', 'CliName')
                ->where('CliDelete', 0)  // Cambié de CliStatus a CliDelete basándome en otros controladores
                ->where('CliCategoria', 'Cliente')  // Solo clientes, no gestores
                ->orderBy('CliName')
                ->get();
        }

        return view('fotos-cliente.index', compact('fotos', 'clientes'));
    }

    /**
     * Descargar una foto específica
     */
    public function download($id)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol, Permisos::CLIENTE) &&
            !in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            abort(403, 'Acceso denegado');
        }

        // Inicializar query base
        $fotoQuery = DB::table('recursos as r')
            ->join('solicitud_residuos as sr', 'r.FK_RecSolRes', '=', 'sr.ID_SolRes')
            ->join('solicitud_servicios as ss', 'sr.FK_SolResSolSer', '=', 'ss.ID_SolSer')
            ->join('clientes as c', 'ss.FK_SolSerCliente', '=', 'c.ID_Cli')
            ->select([
                'r.ID_Rec',
                'r.RecSrc',
                'r.RecRmSrc',
                'r.RecTipo',
                'r.created_at',
                'ss.ID_SolSer',
                'ss.SolSerSlug',
                'c.CliName'
            ])
            ->where('r.ID_Rec', $id)
            ->where('r.RecCarte', 'Foto');

        // Si es un cliente, filtrar solo sus fotos
        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            $clienteID = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->value('clientes.ID_Cli');

            if ($clienteID) {
                $fotoQuery->where('c.ID_Cli', $clienteID);
            } else {
                abort(403, 'Cliente no encontrado');
            }
        }

        $foto = $fotoQuery->first();

        if (!$foto) {
            abort(404, 'Foto no encontrada');
        }

        // Construir la ruta del archivo
        $rutaArchivo = public_path('img/Recursos/' . $foto->RecSrc . '/' . $foto->RecRmSrc);

        if (!file_exists($rutaArchivo)) {
            abort(404, 'Archivo no encontrado');
        }

        // Generar nombre de descarga
        $nombreDescarga = 'ID' . $foto->ID_SolSer . '_' . $foto->SolSerSlug . '_' . $foto->RecTipo . '_' . date('Y-m-d', strtotime($foto->created_at)) . '.' . pathinfo($foto->RecRmSrc, PATHINFO_EXTENSION);

        return response()->download($rutaArchivo, $nombreDescarga);
    }

    /**
     * Descargar todas las fotos de un cliente en ZIP
     */
    public function downloadAll(Request $request)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol, Permisos::CLIENTE) &&
            !in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            abort(403, 'Acceso denegado');
        }

        // Inicializar query base
        $fotosQuery = DB::table('recursos as r')
            ->join('solicitud_residuos as sr', 'r.FK_RecSolRes', '=', 'sr.ID_SolRes')
            ->join('solicitud_servicios as ss', 'sr.FK_SolResSolSer', '=', 'ss.ID_SolSer')
            ->join('clientes as c', 'ss.FK_SolSerCliente', '=', 'c.ID_Cli')
            ->select([
                'r.ID_Rec',
                'r.RecSrc',
                'r.RecRmSrc',
                'r.RecTipo',
                'ss.ID_SolSer',
                'ss.SolSerSlug',
                'r.created_at',
                'c.CliName'
            ])
            ->where('r.RecCarte', 'Foto')
            ->where('r.RecTipo', 'Pesaje-Descargue')
            ->whereYear('ss.created_at', '>=', 2020);

        // Si es un cliente, filtrar solo sus fotos
        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            $clienteID = DB::table('personals')
                ->join('cargos', 'cargos.ID_Carg', 'personals.FK_PersCargo')
                ->join('areas', 'areas.ID_Area', 'cargos.CargArea')
                ->join('sedes', 'sedes.ID_Sede', 'areas.FK_AreaSede')
                ->join('clientes', 'clientes.ID_Cli', 'sedes.FK_SedeCli')
                ->where('personals.ID_Pers', Auth::user()->FK_UserPers)
                ->value('clientes.ID_Cli');

            if ($clienteID) {
                $fotosQuery->where('c.ID_Cli', $clienteID);
            } else {
                abort(403, 'Cliente no encontrado');
            }
        }

        $fotos = $fotosQuery->get();

        if ($fotos->isEmpty()) {
            return back()->with('error', 'No hay fotos disponibles para descargar');
        }

        // Crear archivo ZIP temporal
        $zip = new \ZipArchive();
        $clienteName = 'todos_los_clientes';
        if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            $clienteName = $fotos->first()->CliName ?? 'cliente';
        }
        $zipName = 'fotos_' . $clienteName . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($fotos as $foto) {
                $rutaArchivo = public_path('img/Recursos/' . $foto->RecSrc . '/' . $foto->RecRmSrc);

                if (file_exists($rutaArchivo)) {
                    $nombreArchivo = 'ID' . $foto->ID_SolSer . '_' . $foto->SolSerSlug . '_' . $foto->RecTipo . '_' . date('Y-m-d', strtotime($foto->created_at)) . '.' . pathinfo($foto->RecRmSrc, PATHINFO_EXTENSION);
                    $zip->addFile($rutaArchivo, $nombreArchivo);
                }
            }
            $zip->close();

            return response()->download($zipPath, $zipName)->deleteFileAfterSend();
        }

        return back()->with('error', 'Error al crear el archivo ZIP');
    }

    /**
     * Subir nuevas fotos a una solicitud
     */
    public function store(Request $request, $solserslug)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC)) {
            abort(403, 'Solo personal de PROSARC puede subir fotos');
        }

        // Obtener la solicitud
        $solicitud = DB::table('solicitud_servicios as ss')
            ->join('solicitud_residuos as sr', 'sr.FK_SolResSolSer', '=', 'ss.ID_SolSer')
            ->where('ss.SolSerSlug', $solserslug)
            ->select('ss.ID_SolSer', 'sr.ID_SolRes', 'ss.SolSerStatus')
            ->first();

        if (!$solicitud) {
            abort(404, 'Solicitud no encontrada');
        }

        // Verificar que la solicitud esté en un estado válido
        if (!in_array($solicitud->SolSerStatus, ['Conciliado', 'Certificacion', 'Facturado'])) {
            return redirect()->back()->with('error', 'Solo se pueden subir fotos a solicitudes conciliadas, certificadas o facturadas');
        }

        // Validar archivos
        $request->validate([
            'fotos.*' => 'required|image|mimes:jpeg,png,jpg|max:5120', // máximo 5MB
        ]);

        $fotos = [];

        // Procesar cada foto
        foreach ($request->file('fotos') as $foto) {
            // Generar nombre único para la foto
            $fotoName = uniqid() . '_' . time() . '.' . $foto->getClientOriginalExtension();

            // Crear directorio si no existe
            $directory = 'img/Recursos/Fotos-Descargue/' . $solicitud->ID_SolSer;
            if (!file_exists(public_path($directory))) {
                mkdir(public_path($directory), 0777, true);
            }

            // Mover la foto al directorio
            $foto->move(public_path($directory), $fotoName);

            // Registrar en la tabla recursos
            DB::table('recursos')->insert([
                'RecSrc' => 'Fotos-Descargue/' . $solicitud->ID_SolSer,
                'RecRmSrc' => $fotoName,
                'RecTipo' => 'Pesaje-Descargue',
                'RecCarte' => 'Foto',
                'FK_RecSolRes' => $solicitud->ID_SolRes,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $fotos[] = $fotoName;
        }

        return redirect()->back()->with('success', 'Fotos subidas correctamente: ' . count($fotos) . ' foto(s)');
    }

    /**
     * Mostrar las fotos de una solicitud
     */

    public function show($solserslug)
    {
        // Verificar permisos
        if (!in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC) &&
            !in_array(Auth::user()->UsRol, Permisos::CLIENTE) &&
            !in_array(Auth::user()->UsRol2, Permisos::CLIENTE)) {
            abort(403, 'Acceso denegado');
        }

        // Obtener la solicitud
        $solicitud = DB::table('solicitud_servicios as ss')
            ->join('solicitud_residuos as sr', 'sr.FK_SolResSolSer', '=', 'ss.ID_SolSer')
            ->where('ss.SolSerSlug', $solserslug)
            ->select('ss.ID_SolSer', 'sr.ID_SolRes', 'ss.SolSerStatus')
            ->first();

        if (!$solicitud) {
            abort(404, 'Solicitud no encontrada');
        }

        // Obtener fotos de la solicitud
        $fotos = DB::table('recursos as r')
            ->where('r.FK_RecSolRes', $solicitud->ID_SolRes)
            ->where('r.RecCarte', 'Foto')
            ->where('r.RecTipo', 'Pesaje-Descargue')
            ->orderBy('r.created_at', 'desc')
            ->get();

        return view('fotos-cliente.show', compact('fotos', 'solicitud'));
    }
}
