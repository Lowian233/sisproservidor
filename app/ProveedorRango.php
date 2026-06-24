<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProveedorRango extends Model
{
    protected $table = 'proveedor_rangos';

    protected $fillable = ['PTarifaPrecio', 'PTarifaDesde', 'FK_RangoPTarifa'];

    protected $primaryKey = 'ID_PRango';

    public function tarifa()
    {
        return $this->belongsTo('App\ProveedorTarifa', 'FK_RangoPTarifa', 'ID_PTarifa');
    }
}

