<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'CambioDeFechaProgVehic/*',
        'login',
        // Rutas de sincronización offline para conductores (evita error 419 cuando el token CSRF expira)
        'solicitud-servicio/*/firmacliente',
        'solicitud-servicio/*/firmaconductor',
        'solicitud-servicio/*/firmapda',
        'serviciosexpress/*/firmacliente',
        'serviciosexpress/*/firmaconductor',
        'serviciosexpress/*/firmapda',
        'serviciosexpress/*/conciliar',
        'solicitud-residuo/*/Update',
        'serviciosexpress-residuo/*/Update',
        'solicitud-residuo/*/UpdatePrice',
        'solicitud-servicio/*/updateRms',
        'solicitud-servicio/*/update-respel',
        'serviciosexpress/*/updateRms',
        'serviciosexpress/*/update-respel',
    ];

    protected function tokensMatch($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = $this->encrypter->decrypt($header);
        }

        $tokensMatch = hash_equals($request->session()->token(), $token);
        if($tokensMatch) $request->session()->regenerateToken();

        return $tokensMatch;
        // return true;

    }
}
