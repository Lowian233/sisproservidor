<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Checklist por vehículo de los elementos del catálogo (Ley 769 y kit derrames).
 * Se usa para listas de verificación y preoperacional.
 */
class VehiculoElementoLey extends Model
{
    protected $table = 'vehiculo_elementos_ley';

    protected $fillable = [
        'FK_Vehiculo', 'FK_elemento_catalogo', 'cumple', 'fecha_vencimiento', 'observaciones', 'valor'
    ];

    protected $casts = ['cumple' => 'boolean'];

    protected $dates = ['fecha_vencimiento'];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'FK_Vehiculo', 'ID_Vehic');
    }

    public function catalogo()
    {
        return $this->belongsTo(ElementoLeyCatalogo::class, 'FK_elemento_catalogo', 'id');
    }
}
