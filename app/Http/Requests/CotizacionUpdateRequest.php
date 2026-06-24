<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class CotizacionUpdateRequest extends FormRequest
{
/**
     * Determinar si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Ajusta esto según tus necesidades de autorización
    }

    public function rules()
    {
        return [
            'Nit' => 'required|string|max:255',
            'Razon_Social' => 'required|string|max:255',
            'Telefono' => 'required|string|max:20',
            'Correo' => 'required|email|max:255',
            'Direccion' => 'required|string|max:255',
            'sede' => 'required|string|max:255',
            'frecuencia_recoleccion' => 'required|string|max:255',
            'transporte' => 'required|numeric|min:0',
        
            'residuos' => 'required|array|min:1',
            'residuos.*' => 'required|integer|exists:respels,ID_Respel',
        
            'clasf4741' => 'required|array|min:1',
            'clasf4741.*' => 'required|string|max:255',
        
            'tratamientos' => 'required|array|min:1',
            'tratamientos.*' => 'required|string|max:255',
        
            'cantidad_kilos' => 'required|array|min:1',
            'cantidad_kilos.*' => 'required|numeric|min:0',
        
            'precio_kg' => 'required|array|min:1',
            'precio_kg.*' => 'required|numeric|min:0',
        
            'subtotal' => 'required|array|min:1',
            'subtotal.*' => 'required|numeric|min:0',
        
            'peligrosidad' => 'required|array|min:1',
            'peligrosidad.*' => 'required|string|max:255',
        
            'estado_fisico' => 'required|array|min:1',
            'estado_fisico.*' => 'required|string|max:50',
        
            'CoStatus' => 'required|string|in:Pendiente,Aceptado,Rechazado',
        
            'Total' => 'required|numeric|min:1',
        ];
            }
}