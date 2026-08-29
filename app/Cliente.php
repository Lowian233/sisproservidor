<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Cliente extends Model
{
	protected $table = 'clientes';

	protected $fillable = ['CliNit', 'CliName', 'CliShortname', 'CliCode','CliType', 'CliCategoria','CliAuditable', 'CliSlug' ,'CliDelete', 'CliActivo', 'ID_Cli', 'TipoFacturacion', 'provedor', 'CliTipoProveedor', 'CliProcedencia'];

	protected $primaryKey = 'ID_Cli';

    /**
     * Scope: solo clientes activos en CRM (CliActivo=1). Los desactivados no cuentan en totales.
     * Si la columna CliActivo no existe (migración no ejecutada), no filtra.
     */
    public function scopeSoloActivos($query)
    {
        if (!Schema::hasColumn($this->getTable(), 'CliActivo')) {
            return $query;
        }
        return $query->where(function ($q) {
            $q->whereNull('CliActivo')->orWhere('CliActivo', 1);
        });
    }

    /**
     * Scope: excluye prospectos (NIT PEND-xxx o estado CRM Prospecto).
     * No cuentan como clientes nuevos; siguen apareciendo en oportunidades y actividades.
     */
    public function scopeExcluirProspectos($query)
    {
        return $query
            ->whereRaw("(CliNit IS NULL OR UPPER(TRIM(CliNit)) NOT LIKE 'PEND%')")
            ->whereDoesntHave('crmClienteEstado', function ($sub) {
                $sub->where('estado', CrmClienteEstado::ESTADO_PROSPECTO);
            });
    }

    /**
     * Indica si el cliente es prospecto (NIT PEND- o estado CRM Prospecto).
     * Útil para filtrar colecciones en vistas de clientes nuevos del CRM.
     */
    public function esProspecto()
    {
        if ($this->CliNit && strtoupper(substr(trim($this->CliNit), 0, 4)) === 'PEND') {
            return true;
        }
        $estado = $this->crmClienteEstado;
        return $estado && $estado->estado === CrmClienteEstado::ESTADO_PROSPECTO;
    }

    /**
     * Estado CRM (Prospecto/Activo/Reactivación) guardado en tabla crm_cliente_estado, sin tocar clientes.
     */
    public function crmClienteEstado()
    {
        return $this->hasOne(CrmClienteEstado::class, 'FK_Cliente', 'ID_Cli');
    }

    /** Atributo de conveniencia: estado CRM desde crm_cliente_estado (null si no tiene). */
    public function getEstadoCrmAttribute()
    {
        $rel = $this->crmClienteEstado;
        return $rel ? $rel->estado : null;
    }

    /**
     * Crea un cliente mínimo desde CRM (solo nombre). El estado se guarda en crm_cliente_estado.
     * No se escribe nada en CliProcedencia ni en otras columnas de clientes.
     *
     * @param string $nombre Razón social o nombre del contacto
     * @param string $estadoCRM Prospecto | Activo | Reactivación
     * @param int|null $comercialId ID del comercial asignado
     * @return self
     */
    public static function crearMinimoParaCRM($nombre, $estadoCRM, $comercialId = null)
    {
        $nombre = trim($nombre);
        $shortname = \Illuminate\Support\Str::limit($nombre, 100, '');
        $slug = \Illuminate\Support\Str::slug($shortname) . '-' . substr(uniqid(), -6);
        $nit = 'PEND-' . substr(uniqid(), -10);

        $cliente = new self();
        $cliente->CliNit = $nit;
        $cliente->CliName = $nombre;
        $cliente->CliShortname = $shortname ?: $nombre;
        $cliente->CliCategoria = 'Cliente';
        $cliente->CliSlug = $slug;
        $cliente->CliDelete = 0;
        $cliente->CliComercial = $comercialId;
        $cliente->CliStatus = null;
        $cliente->TipoFacturacion = 'Contado';
        $cliente->save();

        $estadoValido = in_array($estadoCRM, [CrmClienteEstado::ESTADO_PROSPECTO, CrmClienteEstado::ESTADO_ACTIVO, CrmClienteEstado::ESTADO_REACTIVACION], true)
            ? $estadoCRM
            : CrmClienteEstado::ESTADO_PROSPECTO;
        CrmClienteEstado::create([
            'FK_Cliente' => $cliente->ID_Cli,
            'estado' => $estadoValido,
        ]);

        return $cliente;
    }

	/**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName()
	{
		return 'CliSlug';
	}

	public function sedes()
	{
		return $this->hasMany('App\Sede', 'FK_SedeCli', 'ID_Cli');//como cliente tiene muchas sedes el busca automaticamente el campo negocios_id
	}
	public function contratos()
	{
		return $this->hasMany('App\Contrato', 'ID_Contra', 'ID_Cli');//como cliente tiene muchos contratos
	}
	public function requerimientos()
	{
		return $this->hasOne('App\RequerimientosCliente', 'ID_RequeCli', 'ID_Cli');//como cliente tiene solo unos requerimientos
	}

	public function manifiestos(){
		return $this->hasMany('App\Manifiesto','FK_ManifCliente', 'ID_Cli');
    }

    public function manifiestosdegestor(){
		return $this->hasMany('App\Manifiesto','FK_ManifTransp', 'ID_Cli');
    }

    public function manifiestosdetransportador(){
		return $this->hasMany('App\Manifiesto','FK_ManifGestor', 'ID_Cli');
    }

    public function certificados(){
        // Certificados donde este cliente es el cliente asociado
        return $this->hasMany('App\Certificado','FK_CertCliente', 'ID_Cli');
    }

    public function certificadosdegestor(){
        // Certificados donde este cliente actúa como Gestor
        return $this->hasMany('App\Certificado','FK_CertGestor', 'ID_Cli');
    }

    public function certificadosdetransportador(){
        // Certificados donde este cliente actúa como Transportador
        return $this->hasMany('App\Certificado','FK_CertTransp', 'ID_Cli');
    }

	public function comercialAsignado(){
		return $this->hasOne('App\Personal','ID_Pers', 'CliComercial');
    }

    /** Solicitudes de servicio del cliente (FK_SolSerCliente) */
    public function solicitudServicios()
    {
        return $this->hasMany('App\SolicitudServicio', 'FK_SolSerCliente', 'ID_Cli');
    }

    /** Última solicitud de servicio del cliente (para CRM) */
    public function ultimaSolicitud()
    {
        return $this->hasOne('App\SolicitudServicio', 'FK_SolSerCliente', 'ID_Cli')
            ->where('SolSerDelete', 0)
            ->orderByDesc('created_at');
    }

	public function clientetarifa(){
		return $this->hasMany('App\CTarifa','FK_Cliente', 'ID_Cli');
	}

	public function proveedorTarifas(){
		return $this->hasMany('App\ProveedorTarifa','FK_Proveedor', 'ID_Cli');
	}

	public function PersonalCliente(){
		return $this->sedes()->with('Areas.Cargos.Personal'); //Pendiente completar
	}

    public function Telefonos()
    {
        $this->loadMissing('sedes.Areas.Cargos.Personal');
        return $this->sedes
            ->pluck('Areas')->flatten()
            ->pluck('Cargos')->flatten()
            ->pluck('Personal')->flatten()
            ->unique('ID_Pers');
    }
}

