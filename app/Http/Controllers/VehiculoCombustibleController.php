<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Vehiculo;
use App\VehiculoCombustible;
use App\Permisos;

class VehiculoCombustibleController extends Controller
{
    /**
     * Formulario para registrar combustible seleccionando el vehículo (sin pasar por index).
     * Usado por logística/conductor desde el menú "Registrar gasolina".
     */
    public function createStandalone()
    {
        if (!in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) && !in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo)) {
            abort(403);
        }

        $Vehiculos = Vehiculo::where('VehicDelete', 0)->orderBy('VehicPlaca')->get();
        $Vehicle = null;

        return view('vehicle.combustible.create', compact('Vehicle', 'Vehiculos'));
    }

    /**
     * Formulario para registrar combustible con vehículo pre-seleccionado (desde index).
     */
    public function create($placa)
    {
        if (!in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) && !in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo)) {
            abort(403);
        }

        $Vehicle = Vehiculo::where('VehicPlaca', $placa)->where('VehicDelete', 0)->first();
        if (!$Vehicle) {
            abort(404);
        }

        $Vehiculos = Vehiculo::where('VehicDelete', 0)->orderBy('VehicPlaca')->get();

        return view('vehicle.combustible.create', compact('Vehicle', 'Vehiculos'));
    }

    /**
     * Guardar registro de combustible cuando la placa viene del request (formulario standalone).
     */
    public function storeStandalone(Request $request)
    {
        if (!in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) && !in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo)) {
            abort(403);
        }

        $request->validate([
            'placa'             => 'required|string|exists:vehiculos,VehicPlaca',
            'fecha'             => 'required|date',
            'tipo_combustible'   => 'required|in:gasolina_corriente,diesel',
            'cantidad'          => 'required|numeric|min:0.01',
            'valor'             => 'nullable|numeric|min:0',
            'kilometraje'       => 'nullable|integer|min:0',
            'ticket'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'observaciones'     => 'nullable|string|max:500',
        ], [
            'placa.required'           => 'Seleccione el vehículo.',
            'placa.exists'             => 'El vehículo seleccionado no es válido.',
            'fecha.required'           => 'La fecha de carga es obligatoria.',
            'tipo_combustible.required' => 'Seleccione el tipo de combustible.',
            'cantidad.required'        => 'La cantidad (galones) es obligatoria.',
        ]);

        $Vehicle = Vehiculo::where('VehicPlaca', $request->placa)->where('VehicDelete', 0)->first();
        if (!$Vehicle) {
            return redirect()->back()->withInput()->withErrors(['placa' => 'El vehículo seleccionado no está disponible.']);
        }

        $data = [
            'FK_Vehiculo'     => $Vehicle->ID_Vehic,
            'fecha'           => $request->fecha,
            'tipo_combustible'=> $request->tipo_combustible,
            'cantidad'        => $request->cantidad,
            'valor'           => $request->valor ?: null,
            'kilometraje'     => $request->kilometraje ?: null,
            'observaciones'   => $request->observaciones ?: null,
        ];

        if ($request->hasFile('ticket')) {
            $file = $request->file('ticket');
            $data['ruta_ticket'] = $file->storeAs(
                'vehiculos/combustible',
                Str::slug($Vehicle->VehicPlaca) . '_' . date('Y-m-d_His', strtotime($request->fecha)) . '_' . Str::random(4) . '.' . $file->getClientOriginalExtension(),
                'public'
            );
        }

        VehiculoCombustible::create($data);

        return redirect()->route('vehicle.index')
            ->with('success', 'Registro de combustible guardado correctamente.');
    }

    public function store(Request $request, $placa)
    {
        if (!in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) && !in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo)) {
            abort(403);
        }

        $Vehicle = Vehiculo::where('VehicPlaca', $placa)->where('VehicDelete', 0)->first();
        if (!$Vehicle) {
            abort(404);
        }

        $request->validate([
            'fecha'             => 'required|date',
            'tipo_combustible'   => 'required|in:gasolina_corriente,diesel',
            'cantidad'          => 'required|numeric|min:0.01',
            'valor'             => 'nullable|numeric|min:0',
            'kilometraje'       => 'nullable|integer|min:0',
            'ticket'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'observaciones'     => 'nullable|string|max:500',
        ], [
            'fecha.required'           => 'La fecha de carga es obligatoria.',
            'tipo_combustible.required' => 'Seleccione el tipo de combustible.',
            'cantidad.required'        => 'La cantidad (galones) es obligatoria.',
        ]);

        $data = [
            'FK_Vehiculo'     => $Vehicle->ID_Vehic,
            'fecha'           => $request->fecha,
            'tipo_combustible'=> $request->tipo_combustible,
            'cantidad'        => $request->cantidad,
            'valor'           => $request->valor ?: null,
            'kilometraje'     => $request->kilometraje ?: null,
            'observaciones'   => $request->observaciones ?: null,
        ];

        if ($request->hasFile('ticket')) {
            $file = $request->file('ticket');
            $data['ruta_ticket'] = $file->storeAs(
                'vehiculos/combustible',
                Str::slug($Vehicle->VehicPlaca) . '_' . date('Y-m-d_His', strtotime($request->fecha)) . '_' . Str::random(4) . '.' . $file->getClientOriginalExtension(),
                'public'
            );
        }

        VehiculoCombustible::create($data);

        return redirect()->route('vehicle.index')
            ->with('success', 'Registro de combustible guardado correctamente.');
    }

    public function index($placa)
    {
        if (!in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) && !in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo)) {
            abort(403);
        }

        $Vehicle = Vehiculo::where('VehicPlaca', $placa)->where('VehicDelete', 0)->first();
        if (!$Vehicle) {
            abort(404);
        }

        $registros = VehiculoCombustible::where('FK_Vehiculo', $Vehicle->ID_Vehic)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('vehicle.combustible.index', compact('Vehicle', 'registros'));
    }
}
