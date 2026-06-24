<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Cliente;
use App\Sede;
use App\Cotizacion;
use App\CrmActividad;
use App\CrmOportunidadV2;
use App\Personal;
use App\Permisos;

class CrmClienteController extends Controller
{
    public function index(Request $request)
    {
        $comercialId = $this->getComercialId();

        $esGerencia = in_array(Auth::user()->UsRol, Permisos::JefeComercial) || in_array(Auth::user()->UsRol2, Permisos::JefeComercial);
        $filtrarComercial = $request->input('comercial');

        $verTodosEquipo = false;
        if ($esGerencia && $filtrarComercial) {
            $comercialId = (int) $filtrarComercial;
            $comercialIds = [$comercialId];
        } elseif ($esGerencia && !$filtrarComercial) {
            $todosComercialesIds = $this->getTodosComercialesIds();
            $verTodosEquipo = true;
            $comercialIds = $todosComercialesIds;
        } else {
            $comercialIds = [$comercialId];
        }

        $verNuevos = $request->get('ver') === 'nuevos';
        $verDesactivados = $request->get('desactivados') === '1';
        $orden = $request->get('orden', 'nombre'); // nombre | ultima_reciente | ultima_antigua | sin_solicitudes

        $baseQuery = function ($incluirDesactivados = false) use ($comercialIds) {
            $q = Cliente::with(['sedes', 'comercialAsignado', 'ultimaSolicitud', 'crmClienteEstado'])
                ->whereIn('CliComercial', $comercialIds)
                ->where('CliDelete', 0)
                ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
                ->whereNotIn('ID_Cli', function ($sub) {
                    $sub->select('FK_Cliente')->from('crm_cliente_estado')->where('estado', 'Prospecto');
                });
            if (!$incluirDesactivados) {
                $q->soloActivos();
            }
            return $q;
        };

        // Lista de clientes (activos por defecto; con ?desactivados=1 incluye desactivados)
        $clientes = $baseQuery($verDesactivados)->get()
            ->filter(fn ($c) => !$c->esProspecto())
            ->values();

        // Ordenar por tiempo de solicitudes
        $clientes = $this->ordenarClientesPorSolicitudes($clientes, $orden);

        $clientesNuevos = $baseQuery($verDesactivados)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn ($c) => !$c->esProspecto())
            ->values();

        if ($verNuevos) {
            $clientesNuevos = $this->ordenarClientesPorSolicitudes($clientesNuevos, $orden);
        }

        // Contadores: totales solo cuentan ACTIVOS (no desactivados)
        $totalActivos = $baseQuery(false)->get()->filter(fn ($c) => !$c->esProspecto())->count();
        $totalDesactivados = $baseQuery(true)->get()->filter(fn ($c) => !$c->esProspecto())->count() - $totalActivos;
        $clientesNuevosActivos = $baseQuery(false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->filter(fn ($c) => !$c->esProspecto())
            ->count();

        $comercialFiltrado = null;
        if ($esGerencia && $filtrarComercial) {
            $pers = Personal::find($comercialId);
            if ($pers) {
                $comercialFiltrado = $pers;
            }
        }

        $queryParams = array_filter([
            'comercial' => $filtrarComercial,
            'ver' => $verNuevos ? 'nuevos' : null,
            'desactivados' => $verDesactivados ? '1' : null,
            'orden' => $orden !== 'nombre' ? $orden : null,
        ]);

        return view('crm.clientes.index', compact(
            'clientes',
            'clientesNuevos',
            'verNuevos',
            'comercialFiltrado',
            'verTodosEquipo',
            'verDesactivados',
            'orden',
            'totalActivos',
            'totalDesactivados',
            'clientesNuevosActivos',
            'queryParams'
        ));
    }

    /**
     * Ordena la colección de clientes según el criterio de solicitudes.
     */
    private function ordenarClientesPorSolicitudes($clientes, $orden)
    {
        return $clientes->sort(function ($a, $b) use ($orden) {
            $ultA = $a->ultimaSolicitud;
            $ultB = $b->ultimaSolicitud;
            $fechaA = $ultA ? strtotime($ultA->created_at) : 0;
            $fechaB = $ultB ? strtotime($ultB->created_at) : 0;

            switch ($orden) {
                case 'ultima_reciente':
                    return $fechaB <=> $fechaA; // Más reciente primero
                case 'ultima_antigua':
                    return $fechaA <=> $fechaB; // Más antiguo primero
                case 'sin_solicitudes':
                    if ($fechaA === 0 && $fechaB === 0) {
                        return strcasecmp($a->CliName ?? '', $b->CliName ?? '');
                    }
                    if ($fechaA === 0) return -1;
                    if ($fechaB === 0) return 1;
                    return $fechaA <=> $fechaB; // Sin solicitudes primero, luego por fecha
                default:
                    return strcasecmp($a->CliName ?? '', $b->CliName ?? '');
            }
        })->values();
    }

    /**
     * Activar o desactivar un cliente (solo afecta conteo en CRM).
     */
    public function toggleActivo(Request $request, $slug)
    {
        $cliente = Cliente::where('CliSlug', $slug)->firstOrFail();

        // Verificar que el usuario tenga permiso (comercial asignado o gerencia)
        $comercialId = $this->getComercialId();
        $esGerencia = in_array(Auth::user()->UsRol, Permisos::JefeComercial) || in_array(Auth::user()->UsRol2, Permisos::JefeComercial);
        if (!$esGerencia && $cliente->CliComercial != $comercialId) {
            abort(403, 'No tiene permiso para modificar este cliente');
        }

        $activo = $request->input('activo', null);
        if ($activo === '1' || $activo === 1) {
            $cliente->CliActivo = 1;
            $mensaje = 'Cliente activado correctamente. Volverá a contar en el total.';
        } else {
            $cliente->CliActivo = 0;
            $mensaje = 'Cliente desactivado. Ya no contará en el total de clientes hasta que lo reactive.';
        }
        $cliente->save();

        if ($request->input('redirect') === 'show') {
            return redirect()
                ->route('crm.clientes.show', $cliente->CliSlug)
                ->with('success', $mensaje);
        }
        if ($request->input('redirect') === 'cliente') {
            return redirect()
                ->route('cliente-show', $cliente->CliSlug)
                ->with('success', $mensaje);
        }

        $queryParams = array_filter([
            'comercial' => $request->input('comercial'),
            'ver' => $request->input('ver'),
            'desactivados' => $request->input('desactivados'),
            'orden' => $request->input('orden'),
        ]);

        return redirect()
            ->route('crm.clientes.index', $queryParams)
            ->with('success', $mensaje);
    }

    public function show($slug)
    {
        $cliente = Cliente::where('CliSlug', $slug)
            ->with(['sedes', 'comercialAsignado'])
            ->firstOrFail();

        // Cotizaciones del cliente
        $cotizaciones = Cotizacion::where('Nit', $cliente->CliNit)
            ->orderBy('FechaCotizacion', 'desc')
            ->get();

        // Actividades relacionadas
        $actividades = CrmActividad::with(['comercial', 'cotizacion'])
            ->where('FK_ActCliente', $cliente->ID_Cli)
            ->orderBy('ActFechaProgramada', 'desc')
            ->take(20)
            ->get();

        // Oportunidades relacionadas
        $oportunidades = CrmOportunidadV2::with(['cotizacion', 'comercial'])
            ->where('FK_OportCliente', $cliente->ID_Cli)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('crm.clientes.show', compact(
            'cliente',
            'cotizaciones',
            'actividades',
            'oportunidades'
        ));
    }

    /**
     * IDs de todos los comerciales del equipo (para vista gerencia).
     */
    private function getTodosComercialesIds()
    {
        $comercialesIds = DB::table('users')
            ->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
            ->whereIn('users.UsRol', ['Comercial', 'Comercialap', 'Ejecutivo Comercial'])
            ->orWhereIn('users.UsRol2', ['Comercial', 'Comercialap', 'Ejecutivo Comercial'])
            ->where('personals.PersDelete', 0)
            ->pluck('personals.ID_Pers')
            ->unique()
            ->toArray();

        $comercialesConClientes = Cliente::where('CliDelete', 0)
            ->whereNotNull('CliComercial')
            ->distinct()
            ->pluck('CliComercial')
            ->toArray();

        return array_unique(array_merge($comercialesIds, $comercialesConClientes));
    }

    private function getComercialId()
    {
        $user = Auth::user();
        if ($user->FK_UserPers) {
            return $user->FK_UserPers;
        }
        $personal = Personal::where('PersEmail', $user->email)->first();
        return $personal ? $personal->ID_Pers : null;
    }
}