<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehiculoCombustible extends Model
{
    protected $table = 'vehiculo_combustible';

    protected $fillable = [
        'FK_Vehiculo', 'fecha', 'tipo_combustible', 'cantidad', 'valor',
        'kilometraje', 'ruta_ticket', 'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
        'valor' => 'decimal:2',
    ];

    const TIPOS = [
        'gasolina_corriente' => 'Gasolina corriente',
        'diesel'             => 'Diesel',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'FK_Vehiculo', 'ID_Vehic');
    }

    public static function getNombreTipo($tipo)
    {
        return self::TIPOS[$tipo] ?? $tipo;
    }
}
