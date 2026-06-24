<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Certificado;
use App\SolicitudServicio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MoverManifiestosACertificadoExt extends Command
{
    protected $signature = 'certificados:mover-manifiestos-a-externos
                            {--year=2025 : Año a procesar (por ProgVehEntrada)}
                            {--dry-run : Solo listar qué se haría, sin ejecutar}';

    protected $description = 'Copia archivos de certificados de terceros (CertType=2) desde manifiestosRegular/ a certificadoExt/ para un año dado';

    public function handle()
    {
        $year = (int) $this->option('year');
        $dryRun = $this->option('dry-run');

        $this->info("=== Mover certificados de terceros del {$year} desde manifiestosRegular a certificadoExt ===");
        if ($dryRun) {
            $this->warn('Modo DRY-RUN: solo se listará lo que se haría, sin copiar archivos.');
        }

        $certificados = Certificado::where('CertType', 2)
            ->whereHas('SolicitudServicio.programacionesrecibidas', function ($q) use ($year) {
                $q->whereYear('ProgVehEntrada', $year);
            })
            ->get();

        $this->info("Certificados de terceros (CertType=2) del {$year} encontrados: " . $certificados->count());

        $copiados = 0;
        $omitidos = 0;
        $errores = 0;

        Storage::disk('public')->makeDirectory('certificadoExt');
        Storage::disk('public')->makeDirectory('manifiestosRegular');

        foreach ($certificados as $cert) {
            $archivo = $this->obtenerNombreArchivo($cert);
            if (!$archivo || $archivo === 'CertificadoDefault.pdf') {
                $omitidos++;
                continue;
            }

            $origen = $this->buscarArchivoOrigen($archivo);
            $destino = storage_path('app/public/certificadoExt/' . $archivo);

            if (!$origen) {
                $this->line("  ⚠ ID {$cert->ID_Cert}: archivo no encontrado: {$archivo}");
                $errores++;
                continue;
            }

            if (file_exists($destino)) {
                $this->line("  ○ ID {$cert->ID_Cert}: ya existe en certificadoExt: {$archivo}");
                $omitidos++;
                continue;
            }

            if (!$dryRun) {
                try {
                    File::copy($origen, $destino);
                    $this->line("  ✓ ID {$cert->ID_Cert}: copiado {$archivo}");
                    $copiados++;
                } catch (\Throwable $e) {
                    $this->error("  ✗ ID {$cert->ID_Cert}: error al copiar: " . $e->getMessage());
                    $errores++;
                }
            } else {
                $this->line("  [DRY-RUN] Se copiaría: {$origen} → certificadoExt/{$archivo}");
                $copiados++;
            }
        }

        $this->newLine();
        $this->info("Resumen: {$copiados} copiados, {$omitidos} omitidos, {$errores} errores.");

        return $errores > 0 ? 1 : 0;
    }

    /**
     * Obtiene el nombre del archivo según CertSrc o CertSrcExt
     */
    private function obtenerNombreArchivo(Certificado $cert): ?string
    {
        $src = $cert->CertSrc ?? $cert->CertSrcExt ?? null;
        if (empty($src)) return null;
        return (substr($src, -4) === '.pdf') ? $src : $src . '.pdf';
    }

    /**
     * Busca el archivo en manifiestosRegular (varias ubicaciones posibles)
     */
    private function buscarArchivoOrigen(string $archivo): ?string
    {
        $posibles = [
            storage_path('app/public/manifiestosRegular/' . $archivo),
            storage_path('app/public/manifiestosRegular/' . pathinfo($archivo, PATHINFO_FILENAME)),
            base_path('public/manifiestosRegular/' . $archivo),
            base_path('public/storage/manifiestosRegular/' . $archivo),
        ];

        foreach ($posibles as $ruta) {
            if (file_exists($ruta) && is_file($ruta)) {
                return $ruta;
            }
        }

        $sinExt = pathinfo($archivo, PATHINFO_FILENAME);
        $conExt = $archivo;
        $posibles2 = [
            storage_path('app/public/manifiestosRegular/' . $sinExt),
            base_path('public/manifiestosRegular/' . $sinExt),
        ];
        foreach ($posibles2 as $ruta) {
            if (file_exists($ruta) && is_file($ruta)) {
                return $ruta;
            }
        }

        return null;
    }
}
