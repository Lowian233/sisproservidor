<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CotizacionCreada;
use App\Mail\CotizacionAprobada;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizacion'; // Aseg¨²rate de que el nombre de la tabla es correcto
    protected $primaryKey = 'id_cotizacion';

    protected $fillable = [
        'Auditlog',
        'FechaCotizacion',
        'Nit',
        'Razon_Social',
        'Telefono',
        'Correo',
        'Direccion',
        'CoStatus',
        'Total',
        'transporte',
        'sede',
        'frecuencia_recoleccion',
        'Observaciones',
        'tipo_cotizacion',
        'Auditlog',
        'status',
        
    ];
    protected $casts = [
        'FechaCotizacion' => 'datetime',
    ];

    // Relaci¨®n con CotiRespel (uno a muchos)
    public function coti_respel()
{
    return $this->hasMany(CotiRespel::class, 'cotizacion_id');
}

    public $timestamps = true;

    public function comercial()
    {
        return $this->belongsTo(User::class, 'Auditlog', 'email'); // Relacionando 'Auditlog' con 'email' del User
    }

    
}