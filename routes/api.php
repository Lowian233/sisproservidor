<?php

use Illuminate\Http\Request;
use App\Console\Commands\EnviarRecordatoriosWatiCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {
    // Rutas API para Clientes Express (sin autenticación, compatible con Wati)
    Route::get('/clientes/verificar-nit/{nit}', 'Api\ClienteExpressController@verificarNit');
    Route::get('/clientes/{idCliente}/sede', 'Api\ClienteExpressController@sede');
    Route::post('/clientes/{idCliente}/sede', 'Api\ClienteExpressController@crearSede');
    Route::post('/clientes', 'Api\ClienteExpressController@store');
    Route::post('/clientes/{id}', 'Api\ClienteExpressController@update');
    Route::get('/clientes/{id}', 'Api\ClienteExpressController@show');
    Route::get('/clientes', 'Api\ClienteExpressController@index');

    // Endpoint unificado de solicitudes (crea y actualiza todo)
    Route::post('/solicitud', 'Api\SolicitudExpressController@solicitud');

    // Webhook para Wati - recepción de documentos
    Route::post('/webhook/wati', 'WebhookController@wati');
    Route::post('/webhook/wati-documentos', 'WebhookController@watiDocumentos');
    Route::get('/debug/wati-mensajes/{phone}', 'WebhookController@debugMensajes');

    // Verificación de comprobantes de pago via OCR
    Route::post('/verificar-pago', 'Api\VerificarPagoController@verificar');
    Route::get('/resultado-pago/{phone}', 'Api\VerificarPagoController@resultado');
    Route::get('/test-wati', function () {
        try {
            // Ejecuta el comando nativo directamente desde la solicitud web
            Artisan::call('wati:enviar-recordatorios');

            // Obtiene la salida formateada que genera el comando en la consola
            $output = Artisan::output();

            return response()->json([
                'status' => 'OK',
                'mensaje' => 'Comando ejecutado correctamente.',
                'salida_artisan' => trim($output)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al ejecutar comando WATI por HTTP: ' . $e->getMessage());

            return response()->json([
                'status' => 'Error',
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine()
            ], 500);
        }
    });
});

Route::group(['prefix' => 'v1','middleware' => 'auth:api'], function () {
    //    Route::resource('task', 'TasksController');

    //Please do not remove this if you want adminlte:route and adminlte:link commands to works correctly.
    #adminlte_api_routes
});
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');

// Endpoint para validación automática de comprobantes de pago
Route::post('/validar-pago', 'Api\PaymentValidationController@validatePayment');