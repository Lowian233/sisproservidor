<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CrmActividad;
use App\Cliente;
use App\Personal;
use App\Cotizacion;

class CrmActividadController extends Controller
{
    public function index(Request $request)
    {
        $comercialId = $this->getComercialId();
        
        $query = CrmActividad::with(['cliente', 'cotizacion'])
            ->where('FK_ActComercial', $comercialId);

        // Filtros
        if ($request->has('estado') && $request->estado) {
            $query->where('ActEstado', $request->estado);
        }

        if ($request->has('tipo') && $request->tipo) {
            $query->where('ActTipo', $request->tipo);
        }

        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('ActFechaProgramada', $request->fecha);
        }

        $actividades = $query->orderBy('ActFechaProgramada', 'desc')->paginate(20);

        return view('crm.actividades.index', compact('actividades'));
    }

    public function create()
    {
        $comercialId = $this->getComercialId();
        
        $clientes = Cliente::where('CliComercial', $comercialId)
            ->where('CliDelete', 0)
            ->orderBy('CliName', 'asc')
            ->get();

        $cotizaciones = Cotizacion::where('CoStatus', 'Pendiente')
            ->orderBy('FechaCotizacion', 'desc')
            ->get();

        return view('crm.actividades.create', compact('clientes', 'cotizaciones'));
    }

    public function store(Request $request)
    {
        $comercialId = $this->getComercialId();
        $clienteId = null;

        if ($request->filled('cliente_nuevo') && $request->filled('cliente_nuevo_nombre')) {
            $request->validate([
                'ActTipo' => 'required|in:Llamada,Visita,Email,Tarea,Reuni車n',
                'ActTitulo' => 'required|string|max:255',
                'ActDescripcion' => 'nullable|string',
                'ActFechaProgramada' => 'required|date',
                'cliente_nuevo_nombre' => 'required|string|max:255',
                'cliente_nuevo_estado' => 'required|in:Prospecto,Activo,Reactivaci車n',
                'FK_ActCotizacion' => 'nullable|exists:cotizacion,id_cotizacion'
            ]);
            $cliente = Cliente::crearMinimoParaCRM(
                $request->cliente_nuevo_nombre,
                $request->cliente_nuevo_estado,
                $comercialId
            );
            $clienteId = $cliente->ID_Cli;
        } else {
            $request->validate([
                'ActTipo' => 'required|in:Llamada,Visita,Email,Tarea,Reuni車n',
                'ActTitulo' => 'required|string|max:255',
                'ActDescripcion' => 'nullable|string',
                'ActFechaProgramada' => 'required|date',
                'FK_ActCliente' => 'required|exists:clientes,ID_Cli',
                'FK_ActCotizacion' => 'nullable|exists:cotizacion,id_cotizacion'
            ]);
            $clienteId = $request->FK_ActCliente;
        }

        $actividad = new CrmActividad();
        $actividad->ActTipo = $request->ActTipo;
        $actividad->ActTitulo = $request->ActTitulo;
        $actividad->ActDescripcion = $request->ActDescripcion;
        $actividad->ActFechaProgramada = $request->ActFechaProgramada;
        $actividad->FK_ActCliente = $clienteId;
        $actividad->FK_ActCotizacion = $request->FK_ActCotizacion;
        $actividad->FK_ActComercial = $comercialId;
        $actividad->ActEstado = 'Pendiente';
        $actividad->save();

        return redirect()->route('crm.actividades.index')
            ->with('success', 'Actividad creada exitosamente' . ($request->filled('cliente_nuevo') ? '. Cliente creado con solo nombre; complete sus datos despu谷s desde Mis Clientes.' : ''));
    }

    public function edit($id)
    {
        $comercialId = $this->getComercialId();
        
        $actividad = CrmActividad::with(['cliente', 'cotizacion'])
            ->where('ID_Actividad', $id)
            ->where('FK_ActComercial', $comercialId)
            ->firstOrFail();

        $clientes = Cliente::where('CliComercial', $comercialId)
            ->where('CliDelete', 0)
            ->orderBy('CliName', 'asc')
            ->get();

        $cotizaciones = Cotizacion::where('CoStatus', 'Pendiente')
            ->orderBy('FechaCotizacion', 'desc')
            ->get();

        return view('crm.actividades.edit', compact('actividad', 'clientes', 'cotizaciones'));
    }

    public function update(Request $request, $id)
    {
        $comercialId = $this->getComercialId();
        
        $actividad = CrmActividad::where('ID_Actividad', $id)
            ->where('FK_ActComercial', $comercialId)
            ->firstOrFail();

        $request->validate([
            'ActTipo' => 'required|in:Llamada,Visita,Email,Tarea,Reuni車n',
            'ActTitulo' => 'required|string|max:255',
            'ActDescripcion' => 'nullable|string',
            'ActFechaProgramada' => 'required|date',
            'ActEstado' => 'required|in:Pendiente,Completada,Cancelada,En Progreso',
            'ActResultado' => 'nullable|string',
            'FK_ActCliente' => 'required|exists:clientes,ID_Cli',
            'FK_ActCotizacion' => 'nullable|exists:cotizacion,id_cotizacion'
        ]);

        $actividad->fill($request->all());
        
        // Si se marca como completada y no tiene fecha de completada, asignarla
        if ($request->ActEstado === 'Completada' && !$actividad->ActFechaCompletada) {
            $actividad->ActFechaCompletada = now();
        }
        
        // Si cambia de completada a otro estado, limpiar fecha de completada
        if ($request->ActEstado !== 'Completada' && $actividad->ActFechaCompletada) {
            $actividad->ActFechaCompletada = null;
        }

        $actividad->save();

        return redirect()->route('crm.actividades.index')
            ->with('success', 'Actividad actualizada exitosamente');
    }

    public function updateEstado(Request $request, $id)
    {
        $actividad = CrmActividad::findOrFail($id);
        
        if ($request->estado === 'Completada') {
            $actividad->ActEstado = 'Completada';
            $actividad->ActFechaCompletada = now();
            $actividad->ActResultado = $request->resultado ?? null;
        } else {
            $actividad->ActEstado = $request->estado;
        }
        
        $actividad->save();

        return response()->json(['success' => true, 'message' => 'Estado actualizado']);
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