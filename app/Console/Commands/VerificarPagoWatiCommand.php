<?php

namespace App\Console\Commands;

use App\Jobs\VerificarPagoWati;
use Illuminate\Console\Command;

class VerificarPagoWatiCommand extends Command
{
    protected $signature = 'wati:verificar-pago {jobFile}';
    protected $description = 'Verifica comprobante de pago de Wati en background';

    public function handle(): void
    {
        $jobFile = $this->argument('jobFile');

        if (!file_exists($jobFile)) {
            return;
        }

        $params = json_decode(file_get_contents($jobFile), true);
        @unlink($jobFile);

        $job = new VerificarPagoWati($params['numberPhone'], $params['monto'] ?? null);
        $job->handle();
    }
}
