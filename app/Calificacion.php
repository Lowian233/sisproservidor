<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';
    
    protected $primaryKey = 'ID_Calificacion';
    
    protected $fillable = [
        'ID_SolSer','ID_Firma','ID_Cli','score','comment',
        'status','signed_hash','completed_at','meta'
    ];
    
    protected $casts = [
        'meta'=>'array',
        'completed_at'=>'datetime'
    ];
    
    public function cliente()
    { 
        return $this->belongsTo(User::class,'ID_Cli'); 
    }
    
    public function servicio()
    { 
        return $this->belongsTo(SolicitudServicio::class,'ID_SolSer','ID_SolSer'); 
    }
    
    public function rm()
    { 
        return $this->belongsTo(FirmasServicios::class,'ID_Firma','ID_Firmas'); 
    }
}
