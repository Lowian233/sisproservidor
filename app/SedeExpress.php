<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SedeExpress extends Model
{
    protected $table = 'sedes_express';
    public $timestamps = false;

    protected $fillable = [
        'idClienteExpress',
        'nombreSede',
        'direccion',
        'localidad',
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteExpress::class, 'idClienteExpress');
    }
}