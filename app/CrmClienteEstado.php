<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Estado CRM del cliente (Prospecto, Activo, Reactivación).
 * Se guarda aquí para no tocar la tabla clientes ni CliProcedencia.
 */
class CrmClienteEstado extends Model
{
    protected $table = 'crm_cliente_estado';

    const ESTADO_PROSPECTO = 'Prospecto';
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_REACTIVACION = 'Reactivación';

    protected $fillable = ['FK_Cliente', 'estado'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'FK_Cliente', 'ID_Cli');
    }
}
