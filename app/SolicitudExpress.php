<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitudExpress extends Model
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'solicitudes_express';
    
    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables.
     *
     * @var array
     */
    protected $fillable = [
        'idSolicitud',
        'idCliente',
        'idSede',
        'peso',
        'precio',
        'tipoResiduo',
        'localidad',
        'estado',
        'RequiereContrato',
    ];
}
