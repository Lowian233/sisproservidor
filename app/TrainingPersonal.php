<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingPersonal extends Model{

    protected $table = 'training_personals';

    protected $fillable = ['CapaPersDate', 'CapaPersExpire', 'CapaPersPdf', 'CapaPersDelete', 'FK_Pers', 'FK_Capa', 'FK_Sede'];

    protected $primaryKey = 'ID_CapPers';


    public function sede(){
        return $this->belongsTo('App\Sede', 'FK_Sede', 'ID_Sede');
    }
    public function training(){
        return $this->belongsTo('App\Training', 'FK_Capa', 'ID_Capa');
    }
    public function personal(){
        return $this->belongsTo('App\Personal', 'FK_Pers', 'ID_Pers');
    }
}