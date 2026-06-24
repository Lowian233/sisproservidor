<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentoExpres extends Model
{
    protected $table = 'documentos_cotizacionesExpress';
    public $timestamps = false;
    protected $fillable = [
        'idClienteExpress',
        'nombreDocumento',
        'rutaDocumento',
        'tipoDocumento',
        'usuarioOrigen'
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteExpress::class, 'idClienteExpress');
    }
}
