<?php

namespace App\Http\Controllers;

use App\Jobs\DescargarDocumentosWati;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function debugMensajes(Request $request, $phone)
    {
        $endpoint = config('services.wati.endpoint');
        $token    = config('services.wati.token');

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->timeout(30)
            ->get("{$endpoint}/api/v1/getMessages/{$phone}", [
                'pageSize'   => 5,
                'pageNumber' => 1,
            ]);

        return response()->json([
            'status'  => $response->status(),
            'headers' => $response->headers(),
            'body'    => $response->json() ?? $response->body(),
        ]);
    }

    public function watiDocumentos(Request $request)
    {
        Log::info('Webhook Wati Documentos recibido', ['body' => $request->all()]);

        try {
            $numberPhone   = $request->input('numberPhone');
            $numberDocument = (int) $request->input('numberDocument', 0);
            $sheet         = (int) $request->input('sheet', 0);
            $idCliente     = $request->input('idCliente');

            if (!$numberPhone || !$idCliente || $numberDocument < 1) {
                return response()->json(['error' => 'numberPhone, idCliente y numberDocument son requeridos'], 400);
            }

            $ahora   = now();
            $año     = $ahora->year;
            $mes     = str_pad($ahora->month, 2, '0', STR_PAD_LEFT);
            $carpeta = public_path('documentos/' . $idCliente . '/' . $año . '/' . $mes);

            if (!file_exists($carpeta)) {
                mkdir($carpeta, 0755, true);
            }

            $minutosAtras    = (int) $request->input('minutosAtras', 2);
            $timestampLimite = time() - ($minutosAtras * 60);

            $job = new DescargarDocumentosWati(
                $numberPhone,
                $numberDocument,
                $sheet,
                $carpeta,
                $timestampLimite
            );
            $job->handle();

            return response()->json(['ok' => true], 200);

        } catch (\Exception $e) {
            Log::error('Webhook Wati Documentos error', [
                'mensaje' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function wati(Request $request)
    {
        Log::info('Webhook Wati recibido', [
            'body' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        try {
            $idCliente = $request->input('idCliente');
            $pdfUrl = $request->input('pdfUrl');

            if (!$idCliente || !$pdfUrl) {
                Log::warning('Webhook Wati campos faltantes', ['body' => $request->all()]);
                return response()->json(['error' => 'idCliente y pdfUrl son requeridos'], 400);
            }

            $fileContent = null;

            if (preg_match('#^https?://#i', $pdfUrl)) {
                Log::info('Webhook Wati descargando URL absoluta', ['pdfUrl' => $pdfUrl]);

                $response = Http::withoutVerifying()
                    ->withOptions(['allow_redirects' => ['max' => 10]])
                    ->timeout(30)
                    ->get($pdfUrl);

                if ($response->successful()) {
                    $fileContent = $response->body();
                } else {
                    Log::warning('Webhook Wati URL absoluta fallida', [
                        'pdfUrl' => $pdfUrl,
                        'status' => $response->status(),
                    ]);
                }
            }

            if (!$fileContent) {
                $fileName = basename($pdfUrl);
                $apiUrl = config('services.wati.endpoint') . '/api/v1/getMedia';
                $token = config('services.wati.token');

                $pathsToTry = [$fileName, $pdfUrl];

                foreach ($pathsToTry as $path) {
                    Log::info('Webhook Wati intentando API', [
                        'apiUrl' => $apiUrl,
                        'paramValue' => $path,
                    ]);

                    $response = Http::withoutVerifying()
                        ->withToken($token)
                        ->timeout(30)
                        ->get($apiUrl, ['fileName' => $path]);

                    Log::info('Webhook Wati respuesta API', [
                        'status' => $response->status(),
                        'contentType' => $response->header('Content-Type'),
                        'tamaño' => strlen($response->body()),
                        'body' => substr($response->body(), 0, 300),
                    ]);

                    if ($response->successful() && $response->body() !== '') {
                        $fileContent = $response->body();
                        break;
                    }
                }

                if (!$fileContent) {
                    Log::error('Webhook Wati API fallida todos los intentos', [
                        'paths' => $pathsToTry,
                    ]);

                    return response()->json([
                        'error' => 'No se pudo obtener el archivo desde Wati',
                        'pathsTried' => $pathsToTry,
                    ], 500);
                }
            }

            if (!$fileContent || $fileContent === '') {
                return response()->json(['error' => 'El archivo descargado está vacío'], 500);
            }

            $ahora = now();
            $año = $ahora->year;
            $mes = str_pad($ahora->month, 2, '0', STR_PAD_LEFT);

            $carpeta = public_path('documentos/' . $idCliente . '/' . $año . '/' . $mes);
            if (!file_exists($carpeta)) {
                mkdir($carpeta, 0755, true);
            }

            $nombreArchivo = time() . '.pdf';
            $ruta = $carpeta . '/' . $nombreArchivo;

            file_put_contents($ruta, $fileContent);

            Log::info('Webhook Wati guardado exitosamente', [
                'ruta' => $ruta,
                'tamaño' => strlen($fileContent),
            ]);

            return response()->json(['ok' => true], 200);

        } catch (\Exception $e) {
            Log::error('Webhook Wati error', [
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
