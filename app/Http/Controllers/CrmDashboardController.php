<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\CrmActividad;
use App\CrmOportunidadV2;
use App\Cliente;
use App\Cotizacion;
use App\Personal;
use App\Permisos;

class CrmDashboardController extends Controller
{
    public function index()
    {
        // Obtener el comercial actual
        $comercialId = $this->getComercialId();
        
        // Actividades del día
        $actividadesHoy = CrmActividad::with(['cliente', 'cotizacion'])
            ->where('FK_ActComercial', $comercialId)
            ->hoy()
            ->orderBy('ActFechaProgramada', 'asc')
            ->get();

        // Actividades pendientes (próximas)
        $actividadesPendientes = CrmActividad::with(['cliente'])
            ->where('FK_ActComercial', $comercialId)
            ->pendientes()
            ->orderBy('ActFechaProgramada', 'asc')
            ->take(10)
            ->get();

        // Oportunidades activas
        $oportunidades = CrmOportunidadV2::with(['cliente', 'cotizacion'])
            ->where('FK_OportComercial', $comercialId)
            ->activas()
            ->orderBy('OportFechaCierreEsperada', 'asc')
            ->get();

        // Agrupar oportunidades por etapa
        $oportunidadesPorEtapa = $oportunidades->groupBy('OportEtapa');

        // Cotizaciones pendientes
        $cotizacionesPendientes = Cotizacion::where('CoStatus', 'Pendiente')
            ->where('Auditlog', Auth::user()->email)
            ->orderBy('FechaCotizacion', 'desc')
            ->take(5)
            ->get();

        // Clientes nuevos del mes (excluye prospectos y desactivados)
        $baseCli = fn () => Cliente::where('CliComercial', $comercialId)
            ->where('CliDelete', 0)
            ->soloActivos()
            ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
            ->whereNotIn('ID_Cli', function ($sub) {
                $sub->select('FK_Cliente')->from('crm_cliente_estado')->where('estado', 'Prospecto');
            });
        $clientesNuevosMes = $baseCli()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->select('CliProcedencia', DB::raw('count(*) as total'))
            ->groupBy('CliProcedencia')
            ->get();

        $stats = [
            'totalClientes' => $baseCli()->count(),
            'clientesNuevosMes' => $baseCli()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'oportunidadesActivas' => $oportunidades->count(),
            'valorTotalPipeline' => $oportunidades->sum('OportValorEstimado'),
            'valorEsperado' => $oportunidades->sum(function($op) {
                return ($op->OportValorEstimado * $op->OportProbabilidad) / 100;
            }),
            'actividadesHoy' => $actividadesHoy->count(),
            'cotizacionesPendientes' => $cotizacionesPendientes->count()
        ];

        return view('crm.dashboard', compact(
            'actividadesHoy',
            'actividadesPendientes',
            'oportunidades',
            'oportunidadesPorEtapa',
            'cotizacionesPendientes',
            'stats',
            'clientesNuevosMes'
        ));
    }

    public function gerencia()
    {
        // Verificar permisos de gerente comercial
        if (!in_array(Auth::user()->UsRol, Permisos::JefeComercial) && 
            !in_array(Auth::user()->UsRol2, Permisos::JefeComercial)) {
            abort(403, 'No tiene permisos para acceder a esta vista');
        }

        // Obtener todos los comerciales (personas con rol Comercial, Comercialap o Ejecutivo Comercial)
        $comercialesIds = DB::table('users')
            ->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
            ->whereIn('users.UsRol', ['Comercial', 'Comercialap', 'Ejecutivo Comercial'])
            ->orWhereIn('users.UsRol2', ['Comercial', 'Comercialap', 'Ejecutivo Comercial'])
            ->where('personals.PersDelete', 0)
            ->pluck('personals.ID_Pers')
            ->unique()
            ->toArray();

        // También incluir comerciales que tienen clientes asignados
        $comercialesConClientes = Cliente::where('CliDelete', 0)
            ->whereNotNull('CliComercial')
            ->distinct()
            ->pluck('CliComercial')
            ->toArray();

        $todosComercialesIds = array_unique(array_merge($comercialesIds, $comercialesConClientes));

        // Obtener información de cada comercial
        $comerciales = Personal::whereIn('ID_Pers', $todosComercialesIds)
            ->where('PersDelete', 0)
            ->get();

        // Estadísticas generales (excluye prospectos: NIT PEND- y estado Prospecto)
        $baseClientes = function () use ($todosComercialesIds) {
            return Cliente::whereIn('CliComercial', $todosComercialesIds)
                ->where('CliDelete', 0)
                ->soloActivos()
                ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
                ->whereNotIn('ID_Cli', function ($sub) {
                    $sub->select('FK_Cliente')
                        ->from('crm_cliente_estado')
                        ->where('estado', 'Prospecto');
                });
        };
        $statsGenerales = [
            'totalComerciales' => $comerciales->count(),
            'totalClientes' => $baseClientes()->count(),
            'clientesNuevosMes' => $baseClientes()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'clientesNuevosAnio' => $baseClientes()
                ->whereYear('created_at', now()->year)
                ->count(),
            'totalOportunidades' => CrmOportunidadV2::whereIn('FK_OportComercial', $todosComercialesIds)
                ->activas()
                ->count(),
            'valorTotalPipeline' => CrmOportunidadV2::whereIn('FK_OportComercial', $todosComercialesIds)
                ->activas()
                ->sum('OportValorEstimado'),
            'valorEsperado' => CrmOportunidadV2::whereIn('FK_OportComercial', $todosComercialesIds)
                ->activas()
                ->get()
                ->sum(function($op) {
                    return ($op->OportValorEstimado * $op->OportProbabilidad) / 100;
                }),
            'actividadesHoy' => CrmActividad::whereIn('FK_ActComercial', $todosComercialesIds)
                ->hoy()
                ->count(),
            'cotizacionesPendientes' => Cotizacion::whereIn('Auditlog', 
                    Personal::whereIn('ID_Pers', $todosComercialesIds)
                        ->pluck('PersEmail')
                        ->toArray()
                )
                ->where('CoStatus', 'Pendiente')
                ->count(),
        ];

        // Actividades del día de todos los comerciales
        $actividadesHoy = CrmActividad::with(['cliente', 'comercial', 'cotizacion'])
            ->whereIn('FK_ActComercial', $todosComercialesIds)
            ->hoy()
            ->orderBy('ActFechaProgramada', 'asc')
            ->get();

        // Actividades pendientes
        $actividadesPendientes = CrmActividad::with(['cliente', 'comercial'])
            ->whereIn('FK_ActComercial', $todosComercialesIds)
            ->pendientes()
            ->orderBy('ActFechaProgramada', 'asc')
            ->take(20)
            ->get();

        // Oportunidades activas de todos los comerciales
        $oportunidades = CrmOportunidadV2::with(['cliente', 'comercial', 'cotizacion'])
            ->whereIn('FK_OportComercial', $todosComercialesIds)
            ->activas()
            ->orderBy('OportFechaCierreEsperada', 'asc')
            ->get();

        // Agrupar oportunidades por etapa
        $oportunidadesPorEtapa = $oportunidades->groupBy('OportEtapa');

        // Cotizaciones pendientes del equipo
        $emailsComerciales = Personal::whereIn('ID_Pers', $todosComercialesIds)
            ->pluck('PersEmail')
            ->filter()
            ->toArray();
            
        $cotizacionesPendientes = Cotizacion::whereIn('Auditlog', $emailsComerciales)
            ->where('CoStatus', 'Pendiente')
            ->orderBy('FechaCotizacion', 'desc')
            ->take(10)
            ->get();

        // Estadísticas por comercial (excluye prospectos: NIT PEND- y estado Prospecto)
        $statsPorComercial = [];
        foreach ($comerciales as $comercial) {
            $qComercial = function () use ($comercial) {
                return Cliente::where('CliComercial', $comercial->ID_Pers)
                    ->where('CliDelete', 0)
                    ->soloActivos()
                    ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
                    ->whereNotIn('ID_Cli', function ($sub) {
                        $sub->select('FK_Cliente')
                            ->from('crm_cliente_estado')
                            ->where('estado', 'Prospecto');
                    });
            };
            $clientesComercial = $qComercial()->count();
            $clientesNuevosMesComercial = $qComercial()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $clientesNuevosAnioComercial = $qComercial()
                ->whereYear('created_at', now()->year)
                ->count();

            $oportunidadesComercial = CrmOportunidadV2::where('FK_OportComercial', $comercial->ID_Pers)
                ->activas()
                ->get();

            $actividadesHoyComercial = CrmActividad::where('FK_ActComercial', $comercial->ID_Pers)
                ->hoy()
                ->count();

            $statsPorComercial[$comercial->ID_Pers] = [
                'comercial' => $comercial,
                'totalClientes' => $clientesComercial,
                'clientesNuevosMes' => $clientesNuevosMesComercial,
                'clientesNuevosAnio' => $clientesNuevosAnioComercial,
                'oportunidadesActivas' => $oportunidadesComercial->count(),
                'valorPipeline' => $oportunidadesComercial->sum('OportValorEstimado'),
                'valorEsperado' => $oportunidadesComercial->sum(function($op) {
                    return ($op->OportValorEstimado * $op->OportProbabilidad) / 100;
                }),
                'actividadesHoy' => $actividadesHoyComercial,
            ];
        }

        // Ordenar comerciales por valor esperado (descendente)
        uasort($statsPorComercial, function($a, $b) {
            return $b['valorEsperado'] <=> $a['valorEsperado'];
        });

        // Clientes nuevos por procedencia (excluye prospectos: NIT PEND- y estado Prospecto)
        $clientesNuevosPorProcedencia = $baseClientes()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->select('CliProcedencia', DB::raw('count(*) as total'))
            ->groupBy('CliProcedencia')
            ->get();

        return view('crm.gerencia', compact(
            'statsGenerales',
            'actividadesHoy',
            'actividadesPendientes',
            'oportunidades',
            'oportunidadesPorEtapa',
            'cotizacionesPendientes',
            'statsPorComercial',
            'comerciales',
            'clientesNuevosPorProcedencia'
        ));
    }

    public function clientesNuevosMes(Request $request)
    {
        // Verificar permisos de gerente comercial
        if (!in_array(Auth::user()->UsRol, Permisos::JefeComercial) && 
            !in_array(Auth::user()->UsRol2, Permisos::JefeComercial)) {
            abort(403, 'No tiene permisos para acceder a esta vista');
        }

        // Obtener todos los comerciales
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

        $todosComercialesIds = array_unique(array_merge($comercialesIds, $comercialesConClientes));

        // Obtener parámetros de mes y año (opcionales)
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);
        $verTodos = $request->input('todos', false); // Si es true, mostrar todos del año

        // Validar mes y año
        $mes = max(1, min(12, (int)$mes));
        $anio = max(2020, min(2100, (int)$anio));

        // Crear fecha del mes seleccionado
        $fechaSeleccionada = Carbon::create($anio, $mes, 1);
        $mesAnterior = $fechaSeleccionada->copy()->subMonth();
        $mesSiguiente = $fechaSeleccionada->copy()->addMonth();

        // Obtener clientes nuevos (excluye prospectos: NIT PEND- y estado Prospecto)
        $query = Cliente::with(['comercialAsignado', 'sedes', 'crmClienteEstado'])
            ->whereIn('CliComercial', $todosComercialesIds)
            ->where('CliDelete', 0)
            ->soloActivos()
            ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
            ->whereNotIn('ID_Cli', function ($sub) {
                $sub->select('FK_Cliente')
                    ->from('crm_cliente_estado')
                    ->where('estado', 'Prospecto');
            });

        if ($verTodos) {
            $query->whereYear('created_at', $anio);
            $tituloVista = "Todos los Clientes Nuevos de {$anio}";
        } else {
            $query->whereMonth('created_at', $mes)
                  ->whereYear('created_at', $anio);
            $tituloVista = "Clientes Nuevos de " . $fechaSeleccionada->format('F Y');
        }

        $clientesNuevos = $query->orderBy('created_at', 'desc')->get()
            ->filter(fn ($c) => !$c->esProspecto())
            ->values();

        $stats = [
            'totalClientesNuevos' => $clientesNuevos->count(),
            'clientesNuevosAnio' => Cliente::whereIn('CliComercial', $todosComercialesIds)
                ->where('CliDelete', 0)
                ->soloActivos()
                ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
                ->whereNotIn('ID_Cli', function ($sub) {
                    $sub->select('FK_Cliente')
                        ->from('crm_cliente_estado')
                        ->where('estado', 'Prospecto');
                })
                ->whereYear('created_at', $anio)
                ->count(),
            'mesActual' => $fechaSeleccionada->format('F Y'),
            'mesActualNumero' => $mes,
            'anioActual' => $anio,
            'mesAnterior' => [
                'mes' => $mesAnterior->month,
                'anio' => $mesAnterior->year,
                'nombre' => $mesAnterior->format('F Y')
            ],
            'mesSiguiente' => [
                'mes' => $mesSiguiente->month,
                'anio' => $mesSiguiente->year,
                'nombre' => $mesSiguiente->format('F Y')
            ],
            'verTodos' => $verTodos,
            'tituloVista' => $tituloVista
        ];

        return view('crm.gerencia.clientes-nuevos-mes', compact('clientesNuevos', 'stats'));
    }

    public function misClientesNuevos(Request $request)
    {
        // Verificar que sea un comercial
        $comercialId = $this->getComercialId();
        
        if (!$comercialId) {
            abort(403, 'No tiene permisos para acceder a esta vista');
        }

        // Obtener parámetros de mes y año (opcionales)
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);
        $verTodos = $request->input('todos', false); // Si es true, mostrar todos del año

        // Validar mes y año
        $mes = max(1, min(12, (int)$mes));
        $anio = max(2020, min(2100, (int)$anio));

        // Crear fecha del mes seleccionado
        $fechaSeleccionada = Carbon::create($anio, $mes, 1);
        $mesAnterior = $fechaSeleccionada->copy()->subMonth();
        $mesSiguiente = $fechaSeleccionada->copy()->addMonth();

        // Obtener clientes nuevos (excluye prospectos: NIT PEND- y estado Prospecto)
        $query = Cliente::with(['comercialAsignado', 'sedes', 'crmClienteEstado'])
            ->where('CliComercial', $comercialId)
            ->where('CliDelete', 0)
            ->soloActivos()
            ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
            ->whereNotIn('ID_Cli', function ($sub) {
                $sub->select('FK_Cliente')
                    ->from('crm_cliente_estado')
                    ->where('estado', 'Prospecto');
            });

        if ($verTodos) {
            $query->whereYear('created_at', $anio);
            $tituloVista = "Todos mis Clientes Nuevos de {$anio}";
        } else {
            $query->whereMonth('created_at', $mes)
                  ->whereYear('created_at', $anio);
            $tituloVista = "Mis Clientes Nuevos de " . $fechaSeleccionada->format('F Y');
        }

        $clientesNuevos = $query->orderBy('created_at', 'desc')->get()
            ->filter(fn ($c) => !$c->esProspecto())
            ->values();

        $stats = [
            'totalClientesNuevos' => $clientesNuevos->count(),
            'clientesNuevosAnio' => Cliente::where('CliComercial', $comercialId)
                ->where('CliDelete', 0)
                ->soloActivos()
                ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
                ->whereNotIn('ID_Cli', function ($sub) {
                    $sub->select('FK_Cliente')
                        ->from('crm_cliente_estado')
                        ->where('estado', 'Prospecto');
                })
                ->whereYear('created_at', $anio)
                ->count(),
            'mesActual' => $fechaSeleccionada->format('F Y'),
            'mesActualNumero' => $mes,
            'anioActual' => $anio,
            'mesAnterior' => [
                'mes' => $mesAnterior->month,
                'anio' => $mesAnterior->year,
                'nombre' => $mesAnterior->format('F Y')
            ],
            'mesSiguiente' => [
                'mes' => $mesSiguiente->month,
                'anio' => $mesSiguiente->year,
                'nombre' => $mesSiguiente->format('F Y')
            ],
            'verTodos' => $verTodos,
            'tituloVista' => $tituloVista
        ];

        return view('crm.comercial.clientes-nuevos', compact('clientesNuevos', 'stats'));
    }

    /**
     * Obtener recordatorios para el comercial actual
     */
    public function obtenerRecordatorios()
    {
        try {
            $comercialId = $this->getComercialId();
            
            if (!$comercialId) {
                return response()->json(['recordatorios' => []]);
            }

        $recordatorios = [];

        // Actividades pendientes que están próximas (hoy o próximas 24 horas)
        $actividadesProximas = CrmActividad::with(['cliente'])
            ->where('FK_ActComercial', $comercialId)
            ->where('ActEstado', 'Pendiente')
            ->where('ActFechaProgramada', '>=', now())
            ->where('ActFechaProgramada', '<=', now()->addHours(24))
            ->orderBy('ActFechaProgramada', 'asc')
            ->get();

        foreach ($actividadesProximas as $actividad) {
            $fechaHora = Carbon::parse($actividad->ActFechaProgramada);
            $minutosRestantes = now()->diffInMinutes($fechaHora, false);
            $horasRestantes = now()->diffInHours($fechaHora, false);
            
            // Solo mostrar si está en las próximas 24 horas
            if ($minutosRestantes >= 0 && $minutosRestantes <= 1440) { // 0 minutos a 24 horas (1440 minutos)
                // Determinar urgencia basada en tiempo real
                if ($minutosRestantes <= 60) {
                    // Menos de 1 hora = URGENTE
                    $urgencia = 'alta';
                } elseif ($minutosRestantes <= 240) {
                    // Entre 1 y 4 horas = MEDIA
                    $urgencia = 'media';
                } else {
                    // Más de 4 horas = BAJA
                    $urgencia = 'baja';
                }
                
                $recordatorios[] = [
                    'tipo' => 'actividad',
                    'titulo' => $actividad->ActTipo . ' programada',
                    'mensaje' => $actividad->ActTitulo . ' - ' . ($actividad->cliente ? $actividad->cliente->CliName : 'Cliente'),
                    'fecha' => $fechaHora->format('d/m/Y H:i'),
                    'urgencia' => $urgencia,
                    'url' => route('crm.actividades.index')
                ];
            }
        }

        // Actividades vencidas (pasadas pero no completadas) - solo las últimas 2 horas
        $actividadesVencidas = CrmActividad::with(['cliente'])
            ->where('FK_ActComercial', $comercialId)
            ->where('ActEstado', 'Pendiente')
            ->where('ActFechaProgramada', '<', now())
            ->where('ActFechaProgramada', '>=', now()->subHours(2)) // Solo las últimas 2 horas
            ->orderBy('ActFechaProgramada', 'desc')
            ->take(3)
            ->get();

        foreach ($actividadesVencidas as $actividad) {
            $fechaHora = Carbon::parse($actividad->ActFechaProgramada);
            $recordatorios[] = [
                'tipo' => 'actividad',
                'titulo' => $actividad->ActTipo . ' vencida',
                'mensaje' => $actividad->ActTitulo . ' - ' . ($actividad->cliente ? $actividad->cliente->CliName : 'Cliente'),
                'fecha' => $fechaHora->format('d/m/Y H:i'),
                'urgencia' => 'alta', // Siempre alta porque ya pasó
                'url' => route('crm.actividades.index')
            ];
        }

        // Oportunidades con fecha de cierre próxima (solo si la fecha es válida y está en el futuro)
        $oportunidadesVencimiento = CrmOportunidadV2::with(['cliente'])
            ->where('FK_OportComercial', $comercialId)
            ->where('OportEstado', 'Activa')
            ->whereNotNull('OportFechaCierreEsperada')
            ->where('OportFechaCierreEsperada', '>=', now()) // Solo futuras
            ->where('OportFechaCierreEsperada', '<=', now()->addDays(7)) // Próximos 7 días
            ->orderBy('OportFechaCierreEsperada', 'asc')
            ->take(5)
            ->get();

        foreach ($oportunidadesVencimiento as $oportunidad) {
            $fechaCierre = Carbon::parse($oportunidad->OportFechaCierreEsperada);
            $diasRestantes = now()->diffInDays($fechaCierre, false);
            
            // Solo agregar si realmente quedan días (no negativos)
            if ($diasRestantes >= 0) {
                // Determinar urgencia basada en días reales
                if ($diasRestantes <= 1) {
                    // 1 día o menos = URGENTE
                    $urgencia = 'alta';
                } elseif ($diasRestantes <= 3) {
                    // 2-3 días = MEDIA
                    $urgencia = 'media';
                } else {
                    // 4+ días = BAJA
                    $urgencia = 'baja';
                }
                
                $recordatorios[] = [
                    'tipo' => 'oportunidad_vencimiento',
                    'titulo' => 'Oportunidad por cerrar',
                    'mensaje' => $oportunidad->OportTitulo . ' - ' . ($oportunidad->cliente ? $oportunidad->cliente->CliName : 'Cliente'),
                    'fecha' => $fechaCierre->format('d/m/Y'),
                    'urgencia' => $urgencia,
                    'url' => route('crm.oportunidades.index')
                ];
            }
        }

        return response()->json(['recordatorios' => $recordatorios]);
        
        } catch (\Exception $e) {
            Log::error('Error al obtener recordatorios: ' . $e->getMessage());
            return response()->json(['recordatorios' => [], 'error' => 'Error al cargar recordatorios'], 500);
        }
    }

    private function getComercialId()
    {
        // Obtener el ID del personal/comercial del usuario autenticado
        $user = Auth::user();
        if ($user->FK_UserPers) {
            return $user->FK_UserPers;
        }
        
        // Si no tiene personal asociado, buscar por email en personals
        $personal = Personal::where('PersEmail', $user->email)->first();
        return $personal ? $personal->ID_Pers : null;
    }
}