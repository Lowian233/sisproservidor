<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatiMediaService
{
    /**
     * Descarga un archivo de Wati probando URL directa y getMedia con varias rutas.
     * Valida que el contenido sea binario real (PDF/imagen), no JSON/HTML vacío.
     *
     * @return array{content: string, extension: string}|null
     */
    public static function download(string $dataPath, ?string $textoNombre = null, ?string $type = null): ?array
    {
        $endpoint = rtrim((string) config('services.wati.endpoint'), '/');
        $token    = (string) config('services.wati.token');

        if ($dataPath === '') {
            return null;
        }

        $candidatos = array_values(array_unique(array_filter([
            $dataPath,
            basename($dataPath),
            $textoNombre ?: null,
            $textoNombre ? basename($textoNombre) : null,
        ])));

        // 1) URL absoluta en dataPath
        if (preg_match('#^https?://#i', $dataPath)) {
            $content = self::httpGet($dataPath, null);
            if (self::validarContenido($content, $type)) {
                return [
                    'content'   => $content,
                    'extension' => self::extensionDesdeContenido($content, $type, $textoNombre, $dataPath),
                ];
            }
        }

        // 2) getMedia con cada candidato
        foreach ($candidatos as $path) {
            if (preg_match('#^https?://#i', $path)) {
                continue;
            }

            $response = Http::withoutVerifying()
                ->withToken($token)
                ->withOptions(['allow_redirects' => ['max' => 10]])
                ->timeout(60)
                ->get("{$endpoint}/api/v1/getMedia", ['fileName' => $path]);

            if (!$response->successful()) {
                continue;
            }

            $content = $response->body();
            if (!self::validarContenido($content, $type)) {
                Log::warning('Wati getMedia: contenido inválido', [
                    'fileName'      => $path,
                    'contentType'   => $response->header('Content-Type'),
                    'size'          => strlen($content),
                    'preview'       => substr($content, 0, 120),
                ]);
                continue;
            }

            return [
                'content'   => $content,
                'extension' => self::extensionDesdeContenido($content, $type, $textoNombre, $dataPath),
            ];
        }

        return null;
    }

    private static function httpGet(string $url, ?string $token): ?string
    {
        $request = Http::withoutVerifying()
            ->withOptions(['allow_redirects' => ['max' => 10]])
            ->timeout(60);

        if ($token) {
            $request = $request->withToken($token);
        }

        $response = $request->get($url);

        return $response->successful() ? $response->body() : null;
    }

    private static function validarContenido(?string $content, ?string $type): bool
    {
        if ($content === null || $content === '' || strlen($content) < 50) {
            return false;
        }

        $inicio = ltrim($content);
        if ($inicio !== '' && ($inicio[0] === '{' || $inicio[0] === '<')) {
            return false;
        }

        if (self::esPdf($content) || self::esImagen($content)) {
            return true;
        }

        return in_array($type, ['document', '2', 2], true) && strlen($content) > 500;
    }

    private static function esPdf(string $content): bool
    {
        return str_starts_with($content, '%PDF');
    }

    private static function esImagen(string $content): bool
    {
        return str_starts_with($content, "\xFF\xD8\xFF")
            || str_starts_with($content, "\x89PNG")
            || str_starts_with($content, 'GIF8');
    }

    private static function extensionDesdeContenido(
        string $content,
        ?string $type,
        ?string $textoNombre,
        string $dataPath
    ): string {
        if (self::esPdf($content)) {
            return 'pdf';
        }
        if (str_starts_with($content, "\x89PNG")) {
            return 'png';
        }
        if (str_starts_with($content, 'GIF8')) {
            return 'gif';
        }
        if (str_starts_with($content, "\xFF\xD8\xFF")) {
            return 'jpg';
        }

        $ref = strtolower($textoNombre ?: $dataPath);
        $ext = pathinfo($ref, PATHINFO_EXTENSION);
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp'], true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }

        return in_array($type, ['image', '1', 1], true) ? 'jpg' : 'pdf';
    }
}
