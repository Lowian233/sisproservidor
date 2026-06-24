<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmOportunidadV2 extends Model
{
    use SoftDeletes;

    protected $table = 'crm_oportunidades_v2';
    protected $primaryKey = 'ID_Oportunidad';

    protected $fillable = [
        'OportTitulo',
        'OportDescripcion',
        'OportValorEstimado',
        'OportProbabilidad',
        'OportEtapa',
        'OportFechaCierreEsperada',
        'OportEstado',
        'OportNotas',
        'FK_OportCliente',
        'FK_OportComercial',
        'FK_OportCotizacion'
    ];

    protected $dates = [
        'OportFechaCierreEsperada',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Etapas disponibles
    const ETAPAS = ['Prospección', 'Cotización', 'Negociación', 'Cierre', 'Aprobado', 'Rechazado'];
    
    // Estados disponibles
    const ESTADOS = ['Activa', 'Ganada', 'Perdida', 'En Pausa'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'FK_OportCliente', 'ID_Cli');
    }

    public function comercial()
    {
        return $this->belongsTo(Personal::class, 'FK_OportComercial', 'ID_Pers');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'FK_OportCotizacion', 'id_cotizacion');
    }

    public function actividades()
    {
        return $this->hasMany(CrmActividad::class, 'FK_ActCotizacion', 'FK_OportCotizacion');
    }

    // Calcular valor esperado (valor * probabilidad)
    public function getValorEsperadoAttribute()
    {
        return ($this->OportValorEstimado * $this->OportProbabilidad) / 100;
    }

    // Scope para oportunidades activas
    public function scopeActivas($query)
    {
        return $query->where('OportEstado', 'Activa');
    }

    // Scope por etapa
    public function scopePorEtapa($query, $etapa)
    {
        return $query->where('OportEtapa', $etapa);
    }
}

