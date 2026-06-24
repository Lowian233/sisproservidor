<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\CrmOportunidadV2;
use App\Cliente;
use App\Personal;
use App\Cotizacion;

class CrmOportunidadController extends Controller
{
    public function index(Request $request)
    {
        $comercialId = $this->getComercialId();

        $oportunidades = CrmOportunidadV2::with(['cliente', 'cotizacion'])
            ->where('FK_OportComercial', $comercialId)
            ->orderBy('OportFechaCierreEsperada', 'asc')
            ->get();

        // Agrupar por etapa para vista de pipeline
        $oportunidadesPorEtapa = $oportunidades->groupBy('OportEtapa');

        // Total del pipeline por estado (suma de OportValorEstimado en cada etapa)
        $etapas = CrmOportunidadV2::ETAPAS;
        $totalPipelinePorEtapa = [];
        foreach ($etapas as $etapa) {
            $oports = $oportunidadesPorEtapa[$etapa] ?? collect();
            $totalPipelinePorEtapa[$etapa] = $oports->sum('OportValorEstimado');
        }

        return view('crm.oportunidades.index', compact('oportunidades', 'oportunidadesPorEtapa', 'totalPipelinePorEtapa', 'etapas'));
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

        return view('crm.oportunidades.create', compact('clientes', 'cotizaciones'));
    }

    public function store(Request $request)
    {
        $comercialId = $this->getComercialId();
        $clienteId = null;

        if ($request->filled('cliente_nuevo') && $request->filled('cliente_nuevo_nombre')) {
            $request->validate([
                'OportTitulo' => 'required|string|max:255',
                'OportDescripcion' => 'nullable|string',
                'OportValorEstimado' => 'required|numeric|min:0',
                'OportProbabilidad' => 'required|integer|min:0|max:100',
                'OportEtapa' => 'required|in:' . implode(',', CrmOportunidadV2::ETAPAS),
                'OportFechaCierreEsperada' => 'nullable|date',
                'cliente_nuevo_nombre' => 'required|string|max:255',
                'cliente_nuevo_estado' => 'required|in:Prospecto,Activo,Reactivación',
                'FK_OportCotizacion' => 'nullable|exists:cotizacion,id_cotizacion'
            ]);
            $cliente = Cliente::crearMinimoParaCRM(
                $request->cliente_nuevo_nombre,
                $request->cliente_nuevo_estado,
                $comercialId
            );
            $clienteId = $cliente->ID_Cli;
        } else {
            $request->validate([
                'OportTitulo' => 'required|string|max:255',
                'OportDescripcion' => 'nullable|string',
                'OportValorEstimado' => 'required|numeric|min:0',
                'OportProbabilidad' => 'required|integer|min:0|max:100',
                'OportEtapa' => 'required|in:' . implode(',', CrmOportunidadV2::ETAPAS),
                'OportFechaCierreEsperada' => 'nullable|date',
                'FK_OportCliente' => 'required|exists:clientes,ID_Cli',
                'FK_OportCotizacion' => 'nullable|exists:cotizacion,id_cotizacion'
            ]);
            $clienteId = $request->FK_OportCliente;
        }

        $oportunidad = new CrmOportunidadV2();
        $oportunidad->OportTitulo = $request->OportTitulo;
        $oportunidad->OportDescripcion = $request->OportDescripcion;
        $oportunidad->OportValorEstimado = $request->OportValorEstimado;
        $oportunidad->OportProbabilidad = $request->OportProbabilidad;
        $oportunidad->OportEtapa = $request->OportEtapa;
        $oportunidad->OportFechaCierreEsperada = $request->OportFechaCierreEsperada;
        $oportunidad->OportNotas = $request->OportNotas;
        $oportunidad->FK_OportCliente = $clienteId;
        $oportunidad->FK_OportCotizacion = $request->FK_OportCotizacion;
        $oportunidad->FK_OportComercial = $comercialId;
        $oportunidad->OportEstado = 'Activa';
        $oportunidad->save();

        return redirect()->route('crm.oportunidades.index')
            ->with('success', 'Oportunidad creada exitosamente' . ($request->filled('cliente_nuevo') ? '. Cliente creado con solo nombre; complete sus datos después desde Mis Clientes.' : ''));
    }

    /**
     * Devuelve el HTML de los detalles para el modal (vía AJAX).
     */
    public function detalle($id)
    {
        try {
            $comercialId = $this->getComercialId();
            $oportunidad = CrmOportunidadV2::with(['cliente', 'cotizacion', 'comercial'])
                ->where('ID_Oportunidad', $id)
                ->where('FK_OportComercial', $comercialId)
                ->firstOrFail();

            return response()->make(
                view('crm.oportunidades.partials.detalle-modal', compact('oportunidad'))->render(),
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        } catch (ModelNotFoundException $e) {
            return response('<p class="text-danger">Oportunidad no encontrada o sin permiso.</p>', 404)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        } catch (\Throwable $e) {
            Log::error('CRM detalle oportunidad: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response('<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> Error al cargar los detalles. Revisa el log del servidor.</p>', 500)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }
    }

    public function edit($id)
    {
        $comercialId = $this->getComercialId();
        $oportunidad = CrmOportunidadV2::with(['cliente', 'cotizacion'])
            ->where('ID_Oportunidad', $id)
            ->where('FK_OportComercial', $comercialId)
            ->firstOrFail();

        $clientes = Cliente::where('CliComercial', $comercialId)
            ->where('CliDelete', 0)
            ->orderBy('CliName', 'asc')
            ->get();

        $cotizaciones = Cotizacion::where('CoStatus', 'Pendiente')
            ->orderBy('FechaCotizacion', 'desc')
            ->get();

        return view('crm.oportunidades.edit', compact('oportunidad', 'clientes', 'cotizaciones'));
    }

    public function update(Request $request, $id)
    {
        $comercialId = $this->getComercialId();
        $oportunidad = CrmOportunidadV2::where('ID_Oportunidad', $id)
            ->where('FK_OportComercial', $comercialId)
            ->firstOrFail();

        $request->validate([
            'OportTitulo' => 'required|string|max:255',
            'OportDescripcion' => 'nullable|string',
            'OportValorEstimado' => 'required|numeric|min:0',
            'OportProbabilidad' => 'required|integer|min:0|max:100',
            'OportEtapa' => 'required|in:' . implode(',', CrmOportunidadV2::ETAPAS),
            'OportFechaCierreEsperada' => 'nullable|date',
            'FK_OportCliente' => 'required|exists:clientes,ID_Cli',
            'FK_OportCotizacion' => 'nullable|exists:cotizacion,id_cotizacion',
        ]);

        $oportunidad->fill($request->only([
            'OportTitulo', 'OportDescripcion', 'OportValorEstimado', 'OportProbabilidad',
            'OportEtapa', 'OportFechaCierreEsperada', 'OportNotas', 'FK_OportCliente', 'FK_OportCotizacion'
        ]));
        $oportunidad->save();

        return redirect()->route('crm.oportunidades.index')
            ->with('success', 'Oportunidad actualizada correctamente.');
    }

    public function updateEtapa(Request $request, $id)
    {
        $request->validate([
            'etapa' => 'required|in:' . implode(',', CrmOportunidadV2::ETAPAS),
        ]);

        $comercialId = $this->getComercialId();
        $oportunidad = CrmOportunidadV2::where('ID_Oportunidad', $id)
            ->where('FK_OportComercial', $comercialId)
            ->firstOrFail();
        $oportunidad->OportEtapa = $request->etapa;
        $oportunidad->save();

        return response()->json(['success' => true, 'message' => 'Etapa actualizada']);
    }

    public function cerrar(Request $request, $id)
    {
        $oportunidad = CrmOportunidadV2::findOrFail($id);
        $oportunidad->OportEstado = $request->estado; // Ganada o Perdida
        $oportunidad->OportNotas = $request->notas ?? null;
        $oportunidad->save();

        return redirect()->route('crm.oportunidades.index')
            ->with('success', 'Oportunidad cerrada exitosamente');
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