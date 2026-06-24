<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\SolicitudServicio;
use App\ProgramacionVehiculo;

class GenerateTomorrowSignatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signatures:generate-tomorrow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera automáticamente las firmas de servicio Express para los servicios programados para mañana';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando generación de firmas para servicios Express programados para mañana...');
        
        // Obtener la fecha de mañana
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        
        // Buscar servicios Express programados para mañana
        $serviciosExpressMañana = DB::table('solicitud_servicios')
            ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
            ->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
            ->where('progvehiculos.ProgVehFecha', $tomorrow)
            ->where('progvehiculos.ProgVehDelete', 0)
            ->where('clientes.CliCategoria', 'ClientePrepago') // Solo servicios Express
            ->where('solicitud_servicios.SolSerDelete', 0)
            ->select('solicitud_servicios.*')
            ->get();
        
        $this->info("Encontrados {$serviciosExpressMañana->count()} servicios Express programados para mañana ({$tomorrow})");
        
        $firmasCreadas = 0;
        $firmasExistentes = 0;
        
        foreach ($serviciosExpressMañana as $servicioData) {
            $servicio = SolicitudServicio::find($servicioData->ID_SolSer);
            
            if (!$servicio) {
                $this->warn("Servicio ID {$servicioData->ID_SolSer} no encontrado");
                continue;
            }
            
            $this->info("Procesando servicio Express: {$servicio->SolSerSlug} (ID: {$servicio->ID_SolSer})");
            
            // Verificar si ya existen firmas para este servicio
            $firmasExistentesCount = DB::table('firmas_servicio')
                ->where('FK_SolSer', $servicio->ID_SolSer)
                ->count();
            
            if ($firmasExistentesCount > 0) {
                $this->warn("Ya existen firmas para el servicio Express {$servicio->SolSerSlug}");
                $firmasExistentes++;
                continue;
            }
            
            // Crear firmas según el tipo de servicio Express
            $this->createSignaturesForExpressService($servicio);
            $firmasCreadas++;
        }
        
        $this->info("Proceso completado:");
        $this->info("- Firmas creadas: {$firmasCreadas}");
        $this->info("- Firmas ya existentes: {$firmasExistentes}");
        
        return 0;
    }
    
    /**
     * Crear firmas para un servicio Express específico
     *
     * @param SolicitudServicio $servicio
     * @return void
     */
    private function createSignaturesForExpressService(SolicitudServicio $servicio)
    {
        $tipo = $servicio->SolSerTypeCollect; // NULL, 97, 98, 99
        
        if (is_null($tipo)) {
            // Servicios Express regulares - crear firmas por generador
            $this->createSignaturesForRegularService($servicio);
        } else {
            // Servicios Express de recolección (97/98/99) - crear firmas por generador y sede
            $this->createSignaturesForCollectionService($servicio);
        }
    }
    
    /**
     * Crear firmas para servicios regulares
     *
     * @param SolicitudServicio $servicio
     * @return void
     */
    private function createSignaturesForRegularService(SolicitudServicio $servicio)
    {
        $pares = DB::table('solicitud_residuos')
            ->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
            ->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
            ->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
            ->where('solicitud_residuos.FK_SolResSolSer', $servicio->ID_SolSer)
            ->distinct()
            ->get([
                'generadors.ID_Gener as id_gener',
            ]);
        
        foreach ($pares as $p) {
            $keys = [
                'FK_SolSer' => $servicio->ID_SolSer,
                'FK_Gener' => $p->id_gener,
                'FK_SGener' => 0,
            ];
            
            $exists = DB::table('firmas_servicio')->where($keys)->exists();
            if (!$exists) {
                DB::table('firmas_servicio')->insert($keys + [
                    'SlugFirmas' => Str::uuid()->toString(),
                    'FirmaCliente' => '0',
                    'FirmaConductor' => '0',
                    'FirmaPDA' => '0',
                    'NombreFuncionario' => '',
                    'Cedula' => '0',
                    'Observaciones' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info("  ✓ Firma creada para generador ID: {$p->id_gener}");
            }
        }
    }
    
    /**
     * Crear firmas para servicios de recolección
     *
     * @param SolicitudServicio $servicio
     * @return void
     */
    private function createSignaturesForCollectionService(SolicitudServicio $servicio)
    {
        $pares = DB::table('solicitud_residuos')
            ->join('residuos_geners', 'residuos_geners.ID_SGenerRes', '=', 'solicitud_residuos.FK_SolResRg')
            ->join('gener_sedes', 'gener_sedes.ID_GSede', '=', 'residuos_geners.FK_SGener')
            ->join('generadors', 'generadors.ID_Gener', '=', 'gener_sedes.FK_GSede')
            ->where('solicitud_residuos.FK_SolResSolSer', $servicio->ID_SolSer)
            ->distinct()
            ->get([
                'generadors.ID_Gener as id_gener',
                'gener_sedes.ID_GSede as id_sgener',
            ]);
        
        foreach ($pares as $p) {
            $keys = [
                'FK_SolSer' => $servicio->ID_SolSer,
                'FK_Gener' => $p->id_gener,
                'FK_SGener' => $p->id_sgener,
            ];
            
            $exists = DB::table('firmas_servicio')->where($keys)->exists();
            if (!$exists) {
                DB::table('firmas_servicio')->insert($keys + [
                    'SlugFirmas' => Str::uuid()->toString(),
                    'FirmaCliente' => '0',
                    'FirmaConductor' => '0',
                    'FirmaPDA' => '0',
                    'NombreFuncionario' => '',
                    'Cedula' => '0',
                    'Observaciones' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info("  ✓ Firma creada para generador ID: {$p->id_gener}, sede ID: {$p->id_sgener}");
            }
        }
    }
}
