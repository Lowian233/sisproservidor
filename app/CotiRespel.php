<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotiRespel extends Model
{
    use HasFactory;

    protected $table = 'coti_respel'; // Asegúrate de que el nombre de la tabla es correcto
    protected $primaryKey = 'id'; // Cambia esto si tu tabla tiene una PK diferente

    protected $fillable = [
        'cotizacion_id',
        'FK_ID_Respel',
        'FK_Tratamiento',
        'cantidad_kilos',
        'precio_kg',
        'subtotal',
        'peligrosidad',
        'clasf4741',
        'estado_fisico',
    ];

    // Relación con Cotizacion (muchos a uno)
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id', 'id_cotizacion');
    }

    // Relación con Respel (muchos a uno)
    public function respel()
{
    return $this->belongsTo(Respel::class, 'FK_ID_Respel');
}

    // Relación con Tratamiento (muchos a uno)
public function tratamiento()
{
    return $this->belongsTo(Tratamiento::class, 'FK_Tratamiento', 'ID_Trat');
}
    public $timestamps = true;
}