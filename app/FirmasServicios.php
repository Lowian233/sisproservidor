<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FirmasServicios extends Model
{
    protected $table = 'firmas_servicio';

    protected $fillable = ['FK_SolSer', 'FK_Gener', 'FirmaCliente', 'FirmaConductor', 'FirmaPDA', 'SlugFirmas', 'NombreFuncionario', 'Cedula', 'Observaciones'];

    protected $primaryKey = 'ID_Firma';

    public function Generador()
	{
	 return $this->belongsTo('App\generador', 'FK_SolResRg', 'ID_Gener');
	}


}
