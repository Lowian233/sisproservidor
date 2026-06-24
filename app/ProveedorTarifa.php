<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProveedorTarifa extends Model
{
    protected $table = 'proveedor_tarifas';

    protected $fillable = ['PTarifaDelete', 'PTarifaVencimiento', 'PTarifaFrecuencia', 'PTarifatipo', 'PTarifaConcepto', 'PTarifaCategoria', 'FK_Tratamiento', 'FK_Proveedor'];

    protected $primaryKey = 'ID_PTarifa';

    public function proveedor(){
        return $this->belongsTo('App\Cliente', 'FK_Proveedor', 'ID_Cli');
    }

    public function tratamiento(){
        return $this->belongsTo('App\Tratamiento', 'FK_Tratamiento', 'ID_Trat');
    }

    public function rangos(){
        return $this->hasMany('App\ProveedorRango', 'FK_RangoPTarifa', 'ID_PTarifa')->orderBy('PTarifaDesde', 'asc');
    }
}

