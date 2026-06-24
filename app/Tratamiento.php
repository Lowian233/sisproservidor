<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{

	protected $table = 'tratamientos';

	protected $primaryKey = 'ID_Trat';

	protected $fillable=['TratName', 'TratTipo', 'TratDelete', 'TratPretratamiento', 'FK_TratRespel',' FK_TratProv'];

    public function gestor()
	{
	 return $this->belongsTo('App\Sede', 'FK_TratProv', 'ID_Sede');
	 //como tratamiento pertenece a un gestor/sede de cliente
	}

    public function clasificaciones()
    {
        // Nota: La tabla 'clasificacion' no existe en producción
        // Esta relación puede causar errores si la tabla no existe
        // La vista debe verificar si la tabla existe antes de usar esta relación
        return $this->belongsToMany('App\Clasificacion','clasificacion_tratamiento', 'FK_Trat', 'FK_Clasf');
        //lista las clasificaciones relacionados usando muchos a muchos
    }
    public function pretratamientos()
    {
        return $this->belongsToMany('App\Pretratamiento','pretratamiento_tratamiento', 'FK_Trat', 'FK_PreTrat');
        //lista las pretratamientos relacionados usando muchos a muchos
    }


    public function requerimientos()
    {
        return $this->belongsTo('App\Requerimiento', 'FK_ReqTrata');
        // cada tratamiento esta relacionado con muchos residuos mediante de la tabla requerimientos
    }

    public function manifiestos(){
        return $this->hasMany('App\Manifiesto','FK_ManifTrat', 'ID_Trat');
    }

    public function tarifas_cliente(){
        return $this->hasMany('App\CTarifa','FK_Tratamiento', 'ID_Trat')->with('rangos');
    }

    public function tarifas_proveedor(){
        return $this->hasMany('App\ProveedorTarifa','FK_Tratamiento', 'ID_Trat')->where('PTarifaDelete', 0)->with('rangos');
    }

}
