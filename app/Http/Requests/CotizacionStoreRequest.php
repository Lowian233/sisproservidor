<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CotizacionStoreRequest extends FormRequest
{/**
     * Determinar si el usuario est¨¢ autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Ajusta esto seg¨²n tus necesidades de autorizaci¨®n
    }

    /**
     * Obtener las reglas de validaci¨®n que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'Auditlog' => 'nullable|string|max:255',
            'Nit' => 'required|string|max:255',
            'Razon_Social' => 'required|string|max:255',
            'Telefono' => 'required|string|max:20',
            'Correo' => 'nullable|email|max:255',
            'Direccion' => 'required|string|max:255',
            'sede' => 'required|string|max:255',
            'frecuencia_recoleccion' => 'required|string|max:255',
            'transporte' => 'required|numeric|min:0',
            'tipo_cotizacion' => 'required|string|max:255',
            'Observaciones' => 'nullable|string|max:255',
            'residuos' => 'required|array|min:1',
            'residuos.*' => 'required|integer|exists:respels,ID_Respel',

            'clasf4741' => 'required|array|min:1',
            'clasf4741.*' => 'required|string|max:255',

            'tratamientos' => 'required|array|min:1',
            'tratamientos.*.*' => 'required|string|exists:tratamientos,ID_Trat',

            'cantidad_kilos' => 'required|array',
            'cantidad_kilos.*' => 'required|array',
            'cantidad_kilos.*.*' => 'required|numeric|min:0',

            'precio_kg' => 'required|array',
            'precio_kg.*' => 'required|array',
            'precio_kg.*.*' => 'required|numeric|min:0',
            
            'subtotal' => 'required|array',
            'subtotal.*' => 'required|array',
            'subtotal.*.*' => 'required|numeric|min:0',
            
            'peligrosidad' => 'required|array|min:1',
            'peligrosidad.*' => 'required|string|max:255',

            'estado_fisico' => 'required|array|min:1',
            'estado_fisico.*' => 'required|string|max:50',

            'CoStatus' => 'required|string|in:Pendiente,Aceptado,Rechazado',

            'Total' => 'required|numeric|min:1',
            'status' => 'required|string|in:Pendiente,Aprobado,Rechazado',
        ];
    }
}