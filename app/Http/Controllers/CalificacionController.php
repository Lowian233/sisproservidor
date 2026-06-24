<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Calificacion;
use App\FirmasServicios;
use App\SolicitudServicio;
use App\Permisos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\CalificacionNotificacion;
use Illuminate\Support\Str;

class CalificacionController extends Controller
{
    /** Buzón interno único para notificaciones de calificaciones recibidas (regulares). */
    private const MAIL_REGULARES_INTERNO = 'programaciones@prosarc.com.co';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || 
            in_array(Auth::user()->UsRol, Permisos::COMERCIALES) ||
            in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) ||
            in_array(Auth::user()->UsRol, Permisos::ADMINISTRADORBOGOTA) ||
            (Auth::user()->UsRol2 && in_array(Auth::user()->UsRol2, Permisos::ADMINISTRADORBOGOTA))) {
            
            $calificaciones = Calificacion::with(['servicio.cliente', 'cliente', 'rm'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('Calificacion.index', compact('calificaciones'));
        }
        
        abort(403, 'No tiene permisos para ver calificaciones');
    }

    /**
     * Muestra las calificaciones pendientes del cliente logueado
     *
     * @return \Illuminate\Http\Response
     */
    public function pendientesCliente()
    {
        if (!Auth::check() || Auth::user()->UsRol !== 'Cliente') {
            abort(403, 'Solo los clientes pueden ver sus calificaciones pendientes');
        }

        $calificaciones = Calificacion::with(['servicio.cliente', 'rm'])
            ->where('ID_Cli', Auth::user()->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Calificacion.pendientes-cliente', compact('calificaciones'));
    }

    /**
     * Muestra el formulario de calificación para el cliente
     *
     * @param  string  $hash
     * @return \Illuminate\Http\Response
     */
    public function create($hash)
    {
        $calificacion = Calificacion::where('signed_hash', $hash)
            ->where('status', 'pending')
            ->with(['servicio', 'cliente'])
            ->first();

        if (!$calificacion) {
            abort(404, 'Enlace de calificación no válido o ya utilizado');
        }

        return view('Calificacion.create', compact('calificacion'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'signed_hash' => 'required|exists:calificaciones,signed_hash',
            'score' => 'required|integer|min:1|max:3',
            'comment' => 'nullable|string|max:1000',
            'acepta_politicas' => 'required|accepted'
        ]);

        $calificacion = Calificacion::where('signed_hash', $request->signed_hash)
            ->where('status', 'pending')
            ->first();

        if (!$calificacion) {
            return redirect()->back()->with('error', 'Esta calificación ya fue completada o el enlace no es válido');
        }

        $calificacion->score = $request->score;
        $calificacion->comment = $request->comment;
        // Guardar la aceptación de políticas en el campo meta
        $meta = $calificacion->meta ?? [];
        $meta['acepta_politicas'] = true;
        $meta['acepta_politicas_at'] = now()->toDateTimeString();
        $calificacion->meta = $meta;
        $calificacion->status = 'completed';
        $calificacion->completed_at = now();
        $calificacion->save();

        // Notificar a Comercial y Logística
        $this->notificarInternos($calificacion);

        // Redirigir directamente al PDF del recibo de material guardado en storage (solo servicios regulares)
        // Usar la relación rm() de la calificación para obtener la firma directamente
        $firma = $calificacion->rm;
        
        if ($firma && $firma->SlugFirmas) {
            // Servicio regular - servir el PDF directamente
            $pdfPath = 'RecibosdeMaterial/' . $firma->SlugFirmas . '.pdf';
            $fullPath = Storage::disk('public')->path($pdfPath);
            Log::info('store: Buscando PDF con SlugFirmas: ' . $firma->SlugFirmas . ', Ruta: ' . $pdfPath);
            
            if (Storage::disk('public')->exists($pdfPath)) {
                Log::info('store: PDF encontrado! Sirviendo desde: ' . $fullPath);
                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $firma->SlugFirmas . '.pdf"'
                ]);
            } else {
                Log::warning('store: PDF no encontrado en storage: ' . $pdfPath);
                // Listar archivos en el directorio para debug
                $files = Storage::disk('public')->files('RecibosdeMaterial');
                Log::info('store: Archivos disponibles en RecibosdeMaterial: ' . json_encode(array_slice($files, 0, 10)));
            }
        } else {
            Log::warning('store: No se encontró firma o SlugFirmas. ID_Firma: ' . ($calificacion->ID_Firma ?? 'NULL') . ', Firma: ' . ($firma ? 'Sí' : 'No') . ', SlugFirmas: ' . ($firma->SlugFirmas ?? 'NULL'));
        }

        Log::info('store: No se pudo encontrar el PDF, mostrando mensaje de éxito');
        // No usar redirect()->back() porque la calificación ya está completada y create() rechazará el acceso
        // Mostrar un mensaje HTML simple de éxito
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificación Enviada - Prosarc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .success-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h2 class="mb-3">¡Gracias por calificar nuestro servicio!</h2>
        <p class="text-muted">Tu opinión es muy importante para nosotros.</p>
        <p class="mt-4">Tu calificación ha sido registrada exitosamente.</p>
        <div class="text-center mt-4 mb-4 p-4">    
         <a href="https://sispro.prosarc.com/recibomaterial" class="btn btn-primary">Ver Recibo de Material</a>
        </div>
    </div>
</body>
</html>';
        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $calificacion = Calificacion::with(['servicio', 'cliente', 'rm'])->findOrFail($id);
        
        return view('Calificacion.show', compact('calificacion'));
    }

    /**
     * Vista consolidada de todas las calificaciones por número de solicitud
     *
     * @return \Illuminate\Http\Response
     */
    public function verCalificaciones()
    {
        if (in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || 
            in_array(Auth::user()->UsRol, Permisos::COMERCIALES) ||
            in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) ||
            in_array(Auth::user()->UsRol, Permisos::ADMINISTRADORBOGOTA) ||
            (Auth::user()->UsRol2 && in_array(Auth::user()->UsRol2, Permisos::ADMINISTRADORBOGOTA))) {
            
            $calificaciones = Calificacion::with(['servicio.cliente', 'cliente', 'rm'])
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->get()
                ->groupBy('ID_SolSer');

            return view('Calificacion.consolidada', compact('calificaciones'));
        }
        
        abort(403, 'No tiene permisos para ver esta información');
    }

    /**
     * Responder a una calificación
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function responder(Request $request, $id)
    {
        $request->validate([
            'respuesta' => 'required|string|max:500'
        ]);

        $calificacion = Calificacion::findOrFail($id);
        
        $meta = $calificacion->meta ?? [];
        $meta['respuesta'] = $request->respuesta;
        $meta['respondido_por'] = Auth::user()->id;
        $meta['respondido_at'] = now()->toDateTimeString();
        
        $calificacion->meta = $meta;
        $calificacion->save();

        return redirect()->back()->with('success', 'Respuesta guardada correctamente');
    }

    /**
     * Notificar internamente una nueva calificación recibida.
     *
     * @param  Calificacion  $calificacion
     * @return void
     */
    private function notificarInternos($calificacion)
    {
        try {
            $servicio = SolicitudServicio::with('cliente')->find($calificacion->ID_SolSer);
            
            if ($servicio) {
                $destinatarios = [self::MAIL_REGULARES_INTERNO];

                Mail::to($destinatarios)->send(new CalificacionNotificacion($calificacion, $servicio));
                
                Log::info('Notificación interna enviada para calificación ID: ' . $calificacion->ID_Calificacion);
            }
        } catch (\Exception $e) {
            // Log error pero no interrumpir el flujo
            Log::error('Error al enviar notificación de calificación: ' . $e->getMessage());
        }
    }

    /**
     * Crear registro de calificación cuando el conductor genera el RM
     *
     * @param  int  $idFirma
     * @param  int  $idSolSer
     * @return Calificacion|null
     */
    public static function crearCalificacionDesdeRM($idFirma, $idSolSer)
    {
        try {
            Log::info('crearCalificacionDesdeRM: inicio para firma ' . $idFirma . ' y servicio ' . $idSolSer);
            
            $firma = FirmasServicios::where('ID_Firmas', $idFirma)->first();
            $servicio = SolicitudServicio::find($idSolSer);

            if (!$firma || !$servicio) {
                Log::warning('crearCalificacionDesdeRM: no se encontró firma o servicio');
                return null;
            }

            // Obtener el cliente asociado al servicio
            $cliente = $servicio->cliente;
            if (!$cliente) {
                Log::warning('crearCalificacionDesdeRM: no se encontró cliente para servicio ' . $idSolSer);
                return null;
            }

            Log::info('crearCalificacionDesdeRM: encontrado cliente ID ' . $cliente->ID_Cli . ' (' . $cliente->CliName . ')');

            // Obtener el usuario cliente - La relación es: users -> personals -> cargos -> areas -> sedes -> clientes
            $usuarioCliente = DB::table('users')
                ->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
                ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
                ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->where('clientes.ID_Cli', $cliente->ID_Cli)
                ->where('users.UsRol', 'Cliente')
                ->select('users.id')
                ->first();

            // Si no se encuentra con rol Cliente, intentar buscar cualquier usuario asociado al cliente
            if (!$usuarioCliente) {
                Log::info('crearCalificacionDesdeRM: no se encontró usuario con rol Cliente, buscando otros usuarios...');
                $usuarioCliente = DB::table('users')
                    ->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
                    ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                    ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
                    ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
                    ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                    ->where('clientes.ID_Cli', $cliente->ID_Cli)
                    ->select('users.id')
                    ->first();
            }

            if (!$usuarioCliente) {
                Log::warning('crearCalificacionDesdeRM: no se encontró usuario cliente para cliente ID ' . $cliente->ID_Cli);
                return null;
            }

            // Verificar si ya existe una calificación pendiente
            $calificacionExistente = Calificacion::where('ID_Firma', $idFirma)
                ->where('ID_SolSer', $idSolSer)
                ->where('status', 'pending')
                ->first();

            if ($calificacionExistente) {
                Log::info('crearCalificacionDesdeRM: ya existe calificación pendiente ID ' . $calificacionExistente->ID_Calificacion);
                return $calificacionExistente;
            }

            // Crear nueva calificación
            $calificacion = new Calificacion();
            $calificacion->ID_SolSer = $idSolSer;
            $calificacion->ID_Firma = $idFirma;
            $calificacion->ID_Cli = $usuarioCliente->id;
            $calificacion->status = 'pending';
            $calificacion->signed_hash = Str::random(64);
            $calificacion->save();

            Log::info('crearCalificacionDesdeRM: calificación creada ID ' . $calificacion->ID_Calificacion . ' con hash ' . $calificacion->signed_hash);

            // NO enviar notificación aquí porque se incluirá en el correo del recibo de material
            // self::notificarCliente($calificacion, $servicio);

            return $calificacion;
        } catch (\Exception $e) {
            Log::error('Error en crearCalificacionDesdeRM: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Notificar al cliente sobre la disponibilidad de calificación
     *
     * @param  Calificacion  $calificacion
     * @param  SolicitudServicio  $servicio
     * @param  FirmasServicios|null  $firma
     * @return void
     */
    public static function notificarCliente($calificacion, $servicio, $firma = null)
    {
        try {
            // Calificación por correo solo aplica a servicios regulares. Express envía el RM aparte (SolSerRME / ServiceExpressController).
            if ($servicio->SolSerTipo === 'Express') {
                Log::info('notificarCliente: omitido para Express (recibo de material vía módulo Express)');
                return;
            }

            Log::info('notificarCliente: inicio para servicio #' . $servicio->ID_SolSer . ', calificación #' . $calificacion->ID_Calificacion);
            
            $cliente = $servicio->cliente;
            
            if (!$cliente) {
                Log::warning('notificarCliente: no se encontró cliente para el servicio #' . $servicio->ID_SolSer);
                return;
            }

            Log::info('notificarCliente: buscando email para cliente ' . $cliente->CliName . ' (ID: ' . $cliente->ID_Cli . ')');

            // Primero intentar obtener email del usuario cliente - La relación es: users -> personals -> cargos -> areas -> sedes -> clientes
            $usuarioCliente = DB::table('users')
                ->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
                ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
                ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
                ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                ->where('clientes.ID_Cli', $cliente->ID_Cli)
                ->where('users.UsRol', 'Cliente')
                ->select('users.email', 'personals.PersEmail')
                ->first();

            Log::info('notificarCliente: resultado usuarioCliente: ' . json_encode($usuarioCliente));

            $email = null;

            if ($usuarioCliente) {
                $email = $usuarioCliente->email ?? $usuarioCliente->PersEmail;
                if ($email) {
                    Log::info('notificarCliente: email encontrado desde usuario: ' . $email);
                }
            }

            // Si no se encontró con rol Cliente, buscar cualquier usuario asociado al cliente
            if (!$email) {
                Log::info('notificarCliente: buscando email en otros usuarios del cliente...');
                $otroUsuario = DB::table('users')
                    ->join('personals', 'users.FK_UserPers', '=', 'personals.ID_Pers')
                    ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
                    ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
                    ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
                    ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                    ->where('clientes.ID_Cli', $cliente->ID_Cli)
                    ->whereNotNull('users.email')
                    ->select('users.email', 'personals.PersEmail')
                    ->first();
                
                if ($otroUsuario) {
                    $email = $otroUsuario->email ?? $otroUsuario->PersEmail;
                    if ($email) {
                        Log::info('notificarCliente: email encontrado desde otro usuario: ' . $email);
                    }
                }
            }

            // Si no se encontró email del usuario, intentar obtenerlo del personal asociado al servicio
            if (!$email && $servicio->FK_SolSerPersona) {
                $personal = DB::table('personals')
                    ->where('ID_Pers', $servicio->FK_SolSerPersona)
                    ->select('PersEmail')
                    ->first();
                
                if ($personal && !empty($personal->PersEmail)) {
                    $email = $personal->PersEmail;
                    Log::info('notificarCliente: email encontrado desde personal FK_SolSerPersona: ' . $email);
                }
            }

            // Si aún no hay email, intentar obtenerlo de la sede del cliente
            if (!$email) {
                $sede = DB::table('sedes')
                    ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
                    ->where('clientes.ID_Cli', $cliente->ID_Cli)
                    ->whereNotNull('sedes.SedeEmail')
                    ->select('sedes.SedeEmail')
                    ->first();
                
                if ($sede && !empty($sede->SedeEmail)) {
                    $email = $sede->SedeEmail;
                    Log::info('notificarCliente: email encontrado desde sede: ' . $email);
                }
            }

            if ($email) {
                // Si no se pasó la firma, intentar obtenerla
                if (!$firma) {
                    $firma = \App\FirmasServicios::where('FK_SolSer', $servicio->ID_SolSer)->first();
                }

                // No adjuntamos enlace al RM aquí: en regulares el cliente solo recibe la invitación a calificar; Express no usa este correo.
                $urlPDFRecibo = null;

                // URL de calificación (para el botón de calificar)
                $urlCalificacion = route('calificaciones.create', ['hash' => $calificacion->signed_hash]);

                Log::info('notificarCliente: preparando envío a ' . $email);
                Log::info('notificarCliente: URL PDF Recibo: ' . ($urlPDFRecibo ?? 'NO DISPONIBLE'));
                Log::info('notificarCliente: URL Calificación: ' . $urlCalificacion);
                Log::info('notificarCliente: Calificación ID: ' . $calificacion->ID_Calificacion . ', Hash: ' . $calificacion->signed_hash);

                try {
                    $mailable = new \App\Mail\CalificacionSolicitud($calificacion, $servicio, $urlCalificacion, $urlPDFRecibo);
                    Log::info('notificarCliente: Mailable creado exitosamente');
                    
                    Mail::to($email)->send($mailable);
                    Log::info('notificarCliente: Mail::send completado exitosamente para ' . $email);
                } catch (\Exception $mailException) {
                    Log::error('notificarCliente: Error al enviar correo a ' . $email . ': ' . $mailException->getMessage());
                    Log::error('notificarCliente: Trace: ' . $mailException->getTraceAsString());
                    throw $mailException; // Re-lanzar para que se capture en el catch externo
                }
            } else {
                Log::warning('notificarCliente: NO SE ENCONTRÓ EMAIL para enviar notificación. Servicio #' . $servicio->ID_SolSer . ', Cliente: ' . $cliente->CliName . ' (ID: ' . $cliente->ID_Cli . ')');
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar cliente sobre calificación: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
        }
    }
}

