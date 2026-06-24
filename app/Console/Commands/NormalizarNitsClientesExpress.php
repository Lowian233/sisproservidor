<?php

namespace App\Console\Commands;

use App\ClienteExpress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizarNitsClientesExpress extends Command
{
    protected $signature = 'clientes-express:normalizar-nits
                            {--dry-run : Muestra los cambios sin guardar}';

    protected $description = 'Quita puntos, guiones y guiones bajos de los NIT en clientes_express y elimina duplicados';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $clientes = ClienteExpress::orderBy('id')->get();
        $grupos = [];

        foreach ($clientes as $cliente) {
            $nitNormalizado = ClienteExpress::normalizarNit($cliente->nit);
            $grupos[$nitNormalizado][] = $cliente;
        }

        $actualizados = 0;
        $eliminados = 0;

        foreach ($grupos as $nitNormalizado => $items) {
            usort($items, function (ClienteExpress $a, ClienteExpress $b) {
                $camposA = $this->contarCamposLlenos($a);
                $camposB = $this->contarCamposLlenos($b);

                if ($camposA !== $camposB) {
                    return $camposB <=> $camposA;
                }

                return $a->id <=> $b->id;
            });

            $keeper = array_shift($items);

            if ($keeper->nit !== $nitNormalizado) {
                $this->line("Actualizar id {$keeper->id}: {$keeper->nit} -> {$nitNormalizado}");
                if (!$dryRun) {
                    $keeper->nit = $nitNormalizado;
                    $keeper->save();
                }
                $actualizados++;
            }

            foreach ($items as $duplicado) {
                $this->warn("Eliminar duplicado id {$duplicado->id} ({$duplicado->nit}), se conserva id {$keeper->id}");
                if (!$dryRun) {
                    $this->reubicarReferencias((int) $duplicado->id, (int) $keeper->id);
                    $duplicado->delete();
                }
                $eliminados++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[SIMULACIÓN] ' : '') . "NITs actualizados: {$actualizados}");
        $this->info(($dryRun ? '[SIMULACIÓN] ' : '') . "Registros duplicados eliminados: {$eliminados}");
        $this->info('Total final esperado: ' . count($grupos));

        if ($dryRun) {
            $this->comment('Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return self::SUCCESS;
    }

    private function contarCamposLlenos(ClienteExpress $cliente): int
    {
        $total = 0;

        foreach ($cliente->getFillable() as $campo) {
            if ($campo === 'nit') {
                continue;
            }

            $valor = $cliente->{$campo};
            if ($valor !== null && trim((string) $valor) !== '') {
                $total++;
            }
        }

        return $total;
    }

    private function reubicarReferencias(int $idDuplicado, int $idKeeper): void
    {
        DB::table('solicitudes_express')
            ->where('idCliente', $idDuplicado)
            ->update(['idCliente' => $idKeeper]);

        DB::table('sedes_express')
            ->where('idClienteExpress', $idDuplicado)
            ->update(['idClienteExpress' => $idKeeper]);
    }
}
