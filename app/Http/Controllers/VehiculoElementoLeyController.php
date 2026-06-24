<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Vehiculo;
use App\VehiculoElementoLey;
use App\ElementoLeyCatalogo;
use App\Permisos;

/**
 * Gestiona el checklist de elementos de ley por vehículo (Ley 769, HSEQ, kit derrames y extintores)
 * para uso en preoperacional.
 */
class VehiculoElementoLeyController extends Controller
{
    public function edit($placa)
    {
        if (!in_array(Auth::user()->UsRol, Permisos::ProgVehic1) && !in_array(Auth::user()->UsRol2, Permisos::ProgVehic1)) {
            abort(403);
        }

        $Vehicle = Vehiculo::where('VehicPlaca', $placa)->first();
        if (!$Vehicle) {
            abort(404);
        }

        $catalogo = ElementoLeyCatalogo::orderBy('tipo_kit')->orderBy('orden')->get()->groupBy('tipo_kit');
        $porVehiculo = VehiculoElementoLey::where('FK_Vehiculo', $Vehicle->ID_Vehic)->get()->keyBy('FK_elemento_catalogo');

        return view('vehicle.elementos-ley.edit', compact('Vehicle', 'catalogo', 'porVehiculo'));
    }

    public function update(Request $request, $placa)
    {
        if (!in_array(Auth::user()->UsRol, Permisos::ProgVehic1) && !in_array(Auth::user()->UsRol2, Permisos::ProgVehic1)) {
            abort(403);
        }

        $Vehicle = Vehiculo::where('VehicPlaca', $placa)->first();
        if (!$Vehicle) {
            abort(404);
        }

        // Actualizar campos detallados de extintores (HSEQ)
        $extFields = [];
        foreach ([1, 2] as $num) {
            $extFields['VehicExt'.$num.'Capacidad'] = $request->input('VehicExt'.$num.'Capacidad');
            $extFields['VehicExt'.$num.'FechaRecarga'] = $request->input('VehicExt'.$num.'FechaRecarga') ?: null;
            $extFields['VehicExt'.$num.'EstadoManometro'] = $request->input('VehicExt'.$num.'EstadoManometro');
            $extFields['VehicExt'.$num.'EstadoPasador'] = $request->input('VehicExt'.$num.'EstadoPasador');
            $extFields['VehicExt'.$num.'EstadoManija'] = $request->input('VehicExt'.$num.'EstadoManija');
            $extFields['VehicExt'.$num.'EstadoValvula'] = $request->input('VehicExt'.$num.'EstadoValvula');
            $extFields['VehicExt'.$num.'EstadoManguera'] = $request->input('VehicExt'.$num.'EstadoManguera');
            $extFields['VehicExt'.$num.'EstadoBoquilla'] = $request->input('VehicExt'.$num.'EstadoBoquilla');
            $extFields['VehicExt'.$num.'EstadoCilindro'] = $request->input('VehicExt'.$num.'EstadoCilindro');
            $extFields['VehicExt'.$num.'EstadoCalcomania'] = $request->input('VehicExt'.$num.'EstadoCalcomania');
            $extFields['VehicExt'.$num.'EstadoSoporte'] = $request->input('VehicExt'.$num.'EstadoSoporte');
            $extFields['VehicExt'.$num.'Observacion'] = $request->input('VehicExt'.$num.'Observacion');
        }
        $Vehicle->update($extFields);

        // Actualizar elementos del catálogo
        $catalogo = ElementoLeyCatalogo::all();
        $cumple = $request->input('cumple', []);
        $vencimiento = $request->input('vencimiento', []);
        $observaciones = $request->input('observaciones', []);
        $valor = $request->input('valor', []);

        foreach ($catalogo as $item) {
            $el = VehiculoElementoLey::firstOrCreate(
                ['FK_Vehiculo' => $Vehicle->ID_Vehic, 'FK_elemento_catalogo' => $item->id],
                ['cumple' => false]
            );
            $el->cumple = isset($cumple[$item->id]) && $cumple[$item->id];
            $el->fecha_vencimiento = !empty($vencimiento[$item->id]) ? $vencimiento[$item->id] : null;
            $el->observaciones = $observaciones[$item->id] ?? null;
            $el->valor = $valor[$item->id] ?? null;
            $el->save();
        }

        return redirect()->route('vehicle.index')->with('success', 'Elementos de ley actualizados. Se usarán en el preoperacional.');
    }
}
