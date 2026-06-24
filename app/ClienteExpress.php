<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClienteExpress extends Model
{
    protected $table = 'clientes_express';
    public $timestamps = false;
    protected $fillable = [
        'nit',
        'nombreEmpresa',
        'ciudadEmpresa',
        'numeroEmpresa',
        'direccion',
        'numero_contacto',
        'correoEmpresa',
        'nombreRepLegal',
        'encargado',
        'identificacionRepLegal',
        'lugarExpedicion',
        'localidad'
    ];

    public function sedes()
    {
        return $this->hasMany(SedeExpress::class, 'idClienteExpress');
    }

    public static function normalizarNit(?string $nit): string
    {
        $nit = trim((string) $nit);
        $nit = preg_replace('/\s+/', '', $nit) ?? $nit;
        $nit = str_replace(['.', '-', '_'], '', $nit);

        return substr($nit, 0, 13);
    }
}