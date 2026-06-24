<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\ProgramacionVehiculo;
use App\Permisos;

class RequirePreoperacional
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Desactivar obligación hasta que se active la beta (REQUIRE_PREOPERACIONAL_FORCED=true en .env)
        if (!config('app.require_preoperacional_forced', false)) {
            return $next($request);
        }

        // Solo aplicar a conductores autenticados
        if (Auth::check()) {
            $userRol = Auth::user()->UsRol;
            $userRol2 = Auth::user()->UsRol2;
            $esConductor = in_array($userRol, Permisos::CONDUCTOR) || in_array($userRol2, Permisos::CONDUCTOR);
            
            if ($esConductor) {
                Log::info('RequirePreoperacional - Conductor detectado', [
                    'user_id' => Auth::user()->id,
                    'FK_UserPers' => Auth::user()->FK_UserPers,
                    'UsRol' => $userRol,
                    'UsRol2' => $userRol2,
                    'path' => $request->path(),
                    'route_name' => $request->route() ? $request->route()->getName() : null
                ]);
                
                // Rutas que están permitidas sin completar el preoperacional
                $allowedRoutes = [
                    'vehicle-programacion.preoperacional',
                    'vehicle-programacion.store-preoperacional',
                    'logout'
                ];
                
                $currentRouteName = $request->route() ? $request->route()->getName() : null;
                $currentPath = $request->path();
                
                // Si la ruta actual está permitida, continuar
                if (($currentRouteName && in_array($currentRouteName, $allowedRoutes)) || 
                    strpos($currentPath, 'logout') !== false ||
                    strpos($currentPath, 'preoperacional') !== false) {
                    Log::info('RequirePreoperacional - Ruta permitida, continuando');
                    return $next($request);
                }
                // Quitar condición de ProgVehEntrada para que siempre verifique el preoperacional
                $programacionesDelDia = ProgramacionVehiculo::where('FK_ProgConductor', Auth::user()->FK_UserPers)
                    ->where('ProgVehDelete', 0)
                    ->whereDate('ProgVehFecha', Carbon::today())
                    ->orderBy('ProgVehFecha', 'asc')
                    ->orderBy('ProgVehSalida', 'asc')
                    ->get();
                
                Log::info('RequirePreoperacional - Programaciones del día', [
                    'count' => $programacionesDelDia->count(),
                    'FK_UserPers' => Auth::user()->FK_UserPers,
                    'programaciones' => $programacionesDelDia->map(function($p) {
                        return [
                            'ID_ProgVeh' => $p->ID_ProgVeh,
                            'FK_ProgConductor' => $p->FK_ProgConductor,
                            'ProgVehFecha' => $p->ProgVehFecha,
                            'ProgVehPreoperacionalCompletado' => $p->ProgVehPreoperacionalCompletado,
                            'ProgVehEntrada' => $p->ProgVehEntrada
                        ];
                    })
                ]);
                
                // Si no hay programaciones del día, no bloquear (permitir acceso normal)
                if ($programacionesDelDia->isEmpty()) {
                    Log::info('RequirePreoperacional - No hay programaciones del día, permitiendo acceso');
                    return $next($request);
                }
                
                // Verificar si hay alguna con preoperacional completado
                $tienePreoperacionalCompletado = $programacionesDelDia->where('ProgVehPreoperacionalCompletado', true)->isNotEmpty();
                
                Log::info('RequirePreoperacional - Verificación preoperacional', [
                    'tienePreoperacionalCompletado' => $tienePreoperacionalCompletado
                ]);
                
                // Si NO hay ningún preoperacional completado del día, redirigir obligatoriamente al primero
                if (!$tienePreoperacionalCompletado) {
                    $programacionPendiente = $programacionesDelDia->first();
                    Log::info('RequirePreoperacional - Redirigiendo a preoperacional (ninguno completado)', [
                        'ID_ProgVeh' => $programacionPendiente->ID_ProgVeh
                    ]);
                    return redirect()->route('vehicle-programacion.preoperacional', $programacionPendiente->ID_ProgVeh)
                        ->with('warning', 'Debe completar el formulario preoperacional del día antes de continuar.');
                }
                
                // Si hay al menos uno completado, verificar si hay pendientes
                $programacionPendiente = $programacionesDelDia->filter(function($p) {
                    return $p->ProgVehPreoperacionalCompletado == false || is_null($p->ProgVehPreoperacionalCompletado);
                })->first();
                
                // Si hay pendientes, redirigir obligatoriamente al formulario
                if ($programacionPendiente) {
                    Log::info('RequirePreoperacional - Redirigiendo a preoperacional (hay pendientes)', [
                        'ID_ProgVeh' => $programacionPendiente->ID_ProgVeh
                    ]);
                    return redirect()->route('vehicle-programacion.preoperacional', $programacionPendiente->ID_ProgVeh)
                        ->with('warning', 'Debe completar el formulario preoperacional del día antes de continuar.');
                }
                
                Log::info('RequirePreoperacional - Todos completados, permitiendo acceso');
            }
        }
        
        return $next($request);
    }
}
