<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Sede;
use App\Cliente;
use App\audit;
use App\Tratamiento;
use App\Permisos;
use App\ProveedorTarifa;
use App\ProveedorRango;

class ProveedorTarifasController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function create($slug, Request $request)
    {
        // Decodificar el slug si viene codificado en la URL
        $slug = urldecode($slug);
        
        $proveedor = Cliente::where('CliSlug', $slug)
            ->where('CliCategoria', 'Proveedor')
            ->with(['proveedorTarifas.rangos', 'proveedorTarifas.tratamiento', 'sedes'])
            ->first();
        
        if (!$proveedor) {
            // Intentar buscar sin el filtro de categoría por si acaso
            $proveedor = Cliente::where('CliSlug', $slug)
                ->with(['proveedorTarifas.rangos', 'proveedorTarifas.tratamiento', 'sedes'])
                ->first();
            
            if (!$proveedor) {
                \Log::warning('ProveedorTarifas: Proveedor no encontrado', [
                    'slug' => $slug,
                    'slug_length' => strlen($slug),
                    'url' => $request->fullUrl()
                ]);
                abort(404, 'Proveedor no encontrado. Slug: ' . substr($slug, 0, 20) . '...');
            }
            
            // Si se encontró pero no es proveedor, permitir acceso de todas formas
            // (puede ser que Prosarc esté marcado como "Cliente" pero actúe como proveedor)
            if ($proveedor->CliCategoria != 'Proveedor') {
                \Log::info('ProveedorTarifas: Cliente encontrado pero categoría es ' . $proveedor->CliCategoria, [
                    'slug' => $slug,
                    'categoria' => $proveedor->CliCategoria,
                    'cliente_id' => $proveedor->ID_Cli,
                    'cliente_name' => $proveedor->CliName
                ]);
                // Continuar de todas formas para permitir agregar tarifas
            }
        }
        
        // Obtener tratamiento específico si viene en la URL
        $tratamientoSeleccionado = $request->query('tratamiento', null);
        
        // Obtener todos los tratamientos asociados a sedes del proveedor
        $sedeIds = $proveedor->sedes->pluck('ID_Sede')->toArray();
        
        // Buscar tratamientos de dos formas:
        // 1. Tratamientos asociados directamente a las sedes del proveedor
        // 2. Tratamientos donde la sede pertenece al proveedor (por si las sedes no están cargadas)
        $tratamientos = Tratamiento::with('gestor')
            ->where(function($query) use ($sedeIds, $proveedor) {
                if (!empty($sedeIds)) {
                    $query->whereIn('FK_TratProv', $sedeIds);
                }
                // También buscar por cliente de la sede
                $query->orWhereHas('gestor', function($q) use ($proveedor) {
                    $q->where('FK_SedeCli', $proveedor->ID_Cli);
                });
            })
            ->where('TratDelete', 0)
            ->get();
        
        // Si viene un tratamiento específico en la URL, asegurarse de que esté en la lista
        if ($tratamientoSeleccionado) {
            $tratamientoEspecifico = Tratamiento::with('gestor')
                ->where('ID_Trat', $tratamientoSeleccionado)
                ->where('TratDelete', 0)
                ->first();
            
            if ($tratamientoEspecifico) {
                // Verificar si ya está en la lista usando find
                $existeEnLista = $tratamientos->first(function($trat) use ($tratamientoSeleccionado) {
                    return $trat->ID_Trat == $tratamientoSeleccionado;
                });
                
                if (!$existeEnLista) {
                    // Verificar que la sede del tratamiento pertenezca al proveedor
                    if ($tratamientoEspecifico->gestor && $tratamientoEspecifico->gestor->FK_SedeCli == $proveedor->ID_Cli) {
                        $tratamientos->push($tratamientoEspecifico);
                    }
                }
            }
        }

        return view('proveedor_tarifas.create', compact(['proveedor', 'tratamientos', 'tratamientoSeleccionado']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $slug)
    {
        // Validación personalizada: debe tener tratamiento O concepto
        $request->validate([
            'FK_Tratamiento' => 'nullable|exists:tratamientos,ID_Trat',
            'PTarifaConcepto' => 'nullable|string|max:255',
            'PTarifaCategoria' => 'nullable|in:Tratamiento,Transporte,Alquiler,Otro',
            'PTarifaDesde' => 'required|numeric|min:0',
            'PTarifatipo' => 'required|in:Kg,Unid,Lt,Viaje',
            'PTarifaPrecio' => 'required|numeric|min:0',
            'PTarifaFrecuencia' => 'required|in:Servicio,Mensual',
            'PTarifaVencimiento' => 'required|date',
        ], [
            '*.required' => 'Debe especificar un valor en el campo :attribute',
            'PTarifaDesde.min' => 'Ingrese un valor mayor a 0 en el campo :attribute',
            'PTarifaPrecio.min' => 'Ingrese un valor mayor a 0 en el campo :attribute',
            'FK_Tratamiento.exists' => 'El :attribute seleccionado no se encuentra en la base de datos',
        ], [
            'FK_Tratamiento' => 'Tratamiento',
            'PTarifaConcepto' => 'Concepto',
            'PTarifaCategoria' => 'Categoría',
            'PTarifaDesde' => 'Rango',
            'PTarifatipo' => 'Unidad',
            'PTarifaPrecio' => 'Precio',
            'PTarifaFrecuencia' => 'Frecuencia',
            'PTarifaVencimiento' => 'Vencimiento',
        ]);

        // Validar que tenga tratamiento O concepto
        if (empty($request->input('FK_Tratamiento')) && empty($request->input('PTarifaConcepto'))) {
            return redirect()->back()
                ->withErrors(['FK_Tratamiento' => 'Debe seleccionar un tratamiento o ingresar un concepto.'])
                ->withInput();
        }

        $proveedor = Cliente::where('CliSlug', $slug)
            ->where('CliCategoria', 'Proveedor')
            ->with(['proveedorTarifas.rangos', 'proveedorTarifas.tratamiento'])
            ->first();

        if (!$proveedor) {
            abort(404, 'Proveedor no encontrado');
        }

        // Buscar tarifa previa: por tratamiento o por concepto
        $query = ProveedorTarifa::where('FK_Proveedor', $proveedor->ID_Cli)
            ->where('PTarifatipo', $request->input('PTarifatipo'))
            ->where('PTarifaDelete', 0);
        
        if ($request->input('FK_Tratamiento')) {
            $query->where('FK_Tratamiento', $request->input('FK_Tratamiento'));
        } else {
            $query->where('PTarifaConcepto', $request->input('PTarifaConcepto'));
        }
        
        $tarifaPrevia = $query->first();

        if ($tarifaPrevia === null) {
            $Tarifanueva = new ProveedorTarifa();
            $Tarifanueva->PTarifaDelete = 0;
            $Tarifanueva->PTarifaVencimiento = $request->input('PTarifaVencimiento');
            $Tarifanueva->PTarifaFrecuencia = $request->input('PTarifaFrecuencia');
            $Tarifanueva->PTarifatipo = $request->input('PTarifatipo');
            $Tarifanueva->PTarifaConcepto = $request->input('PTarifaConcepto');
            $Tarifanueva->PTarifaCategoria = $request->input('PTarifaCategoria');
            $Tarifanueva->FK_Proveedor = $proveedor->ID_Cli;
            $Tarifanueva->FK_Tratamiento = $request->input('FK_Tratamiento');
            $Tarifanueva->save();

            $Rangonuevo = new ProveedorRango();
            $Rangonuevo->PTarifaPrecio = $request->input('PTarifaPrecio');
            $Rangonuevo->PTarifaDesde = $request->input('PTarifaDesde');
            $Rangonuevo->FK_RangoPTarifa = $Tarifanueva->ID_PTarifa;
            $Rangonuevo->save();

            $log = new audit();
            $log->AuditTabla="ProveedorTarifa";
            $log->AuditType="Tarifa Nueva";
            $log->AuditRegistro=$Tarifanueva->ID_PTarifa;
            $log->AuditUser=Auth::user()->email;
            $log->Auditlog=json_encode($Tarifanueva);
            $log->save();

        } else {
            $Rangonuevo = new ProveedorRango();
            $Rangonuevo->PTarifaPrecio = $request->input('PTarifaPrecio');
            $Rangonuevo->PTarifaDesde = $request->input('PTarifaDesde');
            $Rangonuevo->FK_RangoPTarifa = $tarifaPrevia->ID_PTarifa;
            $Rangonuevo->save();

            $log = new audit();
            $log->AuditTabla="ProveedorRango";
            $log->AuditType="Rango adicional";
            $log->AuditRegistro=$Rangonuevo->ID_PRango;
            $log->AuditUser=Auth::user()->email;
            $log->Auditlog=json_encode($Rangonuevo);
            $log->save();

            $tarifaPrevia->PTarifaFrecuencia = $request->input('PTarifaFrecuencia');
            $tarifaPrevia->PTarifaVencimiento = $request->input('PTarifaVencimiento');
            if ($request->input('PTarifaConcepto')) {
                $tarifaPrevia->PTarifaConcepto = $request->input('PTarifaConcepto');
            }
            if ($request->input('PTarifaCategoria')) {
                $tarifaPrevia->PTarifaCategoria = $request->input('PTarifaCategoria');
            }
            $tarifaPrevia->save();
        }

        return redirect()->route('proveedor-tarifas.create', ['slug' => $proveedor->CliSlug]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $slug
     * @param  int  $ID_PRango
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $ID_PRango)
    {
        $Rangoparaborrar = ProveedorRango::find($ID_PRango);
        
        if (!$Rangoparaborrar) {
            abort(404, 'Rango no encontrado');
        }

        $Rangoparaborrar->delete();

        // Contar rangos de la tarifa
        $tarifaparaborrar = ProveedorTarifa::where('ID_PTarifa', $Rangoparaborrar->FK_RangoPTarifa)->with('rangos')->first();

        $log = new audit();
        $log->AuditTabla="ProveedorRango";
        $log->AuditType="Rango Eliminado";
        $log->AuditRegistro=$Rangoparaborrar->ID_PRango;
        $log->AuditUser=Auth::user()->email;
        $log->Auditlog=json_encode($Rangoparaborrar);
        $log->save();

        if ($tarifaparaborrar && $tarifaparaborrar->rangos->count() < 1) {
            $tarifaparaborrar->delete();

            $log = new audit();
            $log->AuditTabla="ProveedorTarifa";
            $log->AuditType="Tarifa Eliminada";
            $log->AuditRegistro=$tarifaparaborrar->ID_PTarifa;
            $log->AuditUser=Auth::user()->email;
            $log->Auditlog=json_encode($tarifaparaborrar);
            $log->save();
        }

        return redirect()->route('proveedor-tarifas.create', ['slug' => $slug]);
    }
}

