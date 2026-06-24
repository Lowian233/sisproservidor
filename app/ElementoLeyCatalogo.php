<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de elementos exigidos por Ley 769 de 2002 (Código Nacional de Tránsito)
 * y complementos (kit derrames). Cada ítem se marca como cumple/no cumple por vehículo.
 */
class ElementoLeyCatalogo extends Model
{
    protected $table = 'elementos_ley_catalogo';

    protected $fillable = ['tipo_kit', 'codigo', 'nombre', 'requiere_vencimiento', 'tipo_entrada', 'orden'];

    const TIPOS_ENTRADA = [
        'checklist'         => 'Cumple (checkbox)',
        'cantidad'          => 'Cantidad',
        'estado'            => 'Estado (Bueno/Regular/Malo)',
        'observacion_estado' => 'Observación de estado',
    ];

    protected $casts = ['requiere_vencimiento' => 'boolean'];

    const TIPOS_KIT = [
        'kit_carretera'   => 'Equipo de carretera (Ley 769)',
        'kit_herramientas' => 'Caja de herramientas básica',
        'botiquin'        => 'Botiquín de primeros auxilios',
        'kit_derrames'    => 'Kit de derrames',
    ];

    public function vehiculosElementos()
    {
        return $this->hasMany(VehiculoElementoLey::class, 'FK_elemento_catalogo', 'id');
    }

    public static function getNombreTipoKit($tipo)
    {
        return self::TIPOS_KIT[$tipo] ?? $tipo;
    }
}
