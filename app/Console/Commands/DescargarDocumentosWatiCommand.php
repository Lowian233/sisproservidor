<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DescargarDocumentosWatiCommand extends Command
{
    protected $signature = 'wati:descargar {jobFile}';
    protected $description = 'Descarga documentos de Wati en background';

    public function handle(): void
    {
        $jobFile = $this->argument('jobFile');

        if (!file_exists($jobFile)) {
            Log::error('wati:descargar job file no encontrado', ['file' => $jobFile]);
            return;
        }

        $params = json_decode(file_get_contents($jobFile), true);
        @unlink($jobFile);

        $numberPhone     = $params['numberPhone'];
        $numberDocument  = (int) $params['numberDocument'];
        $sheet           = (int) $params['sheet'];
        $carpeta         = $params['carpeta'];
        $timestampLimite = (int) $params['timestampLimite'];

        $endpoint = config('services.wati.endpoint');
        $token    = config('services.wati.token');

        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $guardados  = 0;
        $pageNumber = $sheet + 1;
        $pageSize   = 20;
        $maxPaginas = 10;

        while ($guardados < $numberDocument && $maxPaginas-- > 0) {
            $msgResponse = Http::withoutVerifying()
                ->withToken($token)
                ->timeout(30)
                ->get("{$endpoint}/api/v1/getMessages/{$numberPhone}", [
                    'pageSize'   => $pageSize,
                    'pageNumber' => $pageNumber,
                ]);

            if (!$msgResponse->successful()) {
                Log::warning('wati:descargar getMessages falló', ['status' => $msgResponse->status()]);
                break;
            }

            $items = $msgResponse->json()['messages']['items'] ?? [];
            if (empty($items)) break;

            foreach ($items as $idx => $message) {
                if ($guardados >= $numberDocument) break;

                $msgTimestamp = (int) ($message['timestamp'] ?? 0);
                if ($msgTimestamp > 0 && $msgTimestamp < $timestampLimite) {
                    $maxPaginas = 0;
                    break;
                }

                // Solo mensajes recibidos del contacto, no enviados por el bot
                $owner     = strtolower($message['owner'] ?? '');
                $eventType = strtolower($message['eventType'] ?? '');
                if ($owner === 'us' || $eventType === 'sent') {
                    Log::info('wati:descargar saltando mensaje del bot', [
                        'owner'     => $owner,
                        'eventType' => $eventType,
                        'data'      => $message['data'] ?? '',
                    ]);
                    continue;
                }

                $type        = $message['type'] ?? '';
                $dataPath    = $message['data'] ?? '';
                $textoNombre = $message['text'] ?? '';

                Log::info('wati:descargar mensaje analizado', [
                    'type'       => $type,
                    'owner'      => $owner,
                    'eventType'  => $eventType,
                    'textoNombre'=> $textoNombre,
                    'dataPath'   => $dataPath,
                ]);

                if (!in_array($type, ['document', 'image']) || !$dataPath) continue;

                $nombreReferencia = strtolower($textoNombre ?: $dataPath);
                if (str_contains($nombreReferencia, '.crdownload')) continue;

                if ($type === 'document') {
                    $ext = pathinfo($nombreReferencia, PATHINFO_EXTENSION);
                    if ($ext !== 'pdf') continue;
                }

                $fileResponse = Http::withoutVerifying()
                    ->withToken($token)
                    ->withOptions(['allow_redirects' => ['max' => 10]])
                    ->timeout(60)
                    ->get("{$endpoint}/api/v1/getMedia", ['fileName' => $dataPath]);

                if (!$fileResponse->successful() || $fileResponse->body() === '') {
                    Log::warning('wati:descargar archivo fallido', ['dataPath' => $dataPath]);
                    continue;
                }

                if ($type === 'image') {
                    $ext = pathinfo($nombreReferencia, PATHINFO_EXTENSION);
                    $ext = in_array($ext, ['jpg', 'jpeg', 'png']) ? $ext : 'jpg';
                    $nombreArchivo = time() . '_' . $idx . '.' . $ext;
                } elseif ($textoNombre && preg_match('/\.pdf$/i', $textoNombre)) {
                    $nombreArchivo = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $textoNombre);
                } else {
                    $nombreArchivo = time() . '_' . $idx . '.pdf';
                }

                file_put_contents($carpeta . '/' . $nombreArchivo, $fileResponse->body());
                Log::info('wati:descargar archivo guardado', ['archivo' => $nombreArchivo, 'tipo' => $type]);
                $guardados++;
            }

            $pageNumber++;
        }

        Log::info('wati:descargar completado', ['phone' => $numberPhone, 'guardados' => $guardados]);
    }
}
