<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmActividad extends Model
{
    use SoftDeletes;

    protected $table = 'crm_actividades';
    protected $primaryKey = 'ID_Actividad';

    protected $fillable = [
        'ActTipo',
        'ActTitulo',
        'ActDescripcion',
        'ActFechaProgramada',
        'ActFechaCompletada',
        'ActEstado',
        'ActResultado',
        'FK_ActCliente',
        'FK_ActComercial',
        'FK_ActCotizacion'
    ];

    protected $dates = [
        'ActFechaProgramada',
        'ActFechaCompletada',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Tipos de actividad disponibles
    const TIPOS = ['Llamada', 'Visita', 'Email', 'Tarea', 'Reunión'];
    
    // Estados disponibles
    const ESTADOS = ['Pendiente', 'Completada', 'Cancelada', 'En Progreso'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'FK_ActCliente', 'ID_Cli');
    }

    public function comercial()
    {
        return $this->belongsTo(Personal::class, 'FK_ActComercial', 'ID_Pers');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'FK_ActCotizacion', 'id_cotizacion');
    }

    // Scope para actividades pendientes
    public function scopePendientes($query)
    {
        return $query->where('ActEstado', 'Pendiente')
                     ->where('ActFechaProgramada', '>=', now());
    }

    // Scope para actividades del día
    public function scopeHoy($query)
    {
        return $query->whereDate('ActFechaProgramada', today());
    }
}


















