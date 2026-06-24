<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Mail\SolSerEmail;
use App\Mail\SolSerEmailClient;
use App\Mail\RespelMail;
use App\SolicitudServicio;
use App\Personal;
use App\Respel;

class EmailController extends Controller
{
    private const MAIL_RESIDUOS_INTERNO = 'residuos@prosarc.com.co';

    private const MAIL_REGULARES_INTERNO = 'regulares@prosarc.com.co';

    private const MAIL_CERTIFICACIONES_INTERNO = 'certificaciones@prosarc.com.co';

    /**
     * Servicios comerciales "regulares" (no Express ni flujo Interno de planta).
     * En ellos se reduce el correo al contacto del cliente según política acordada.
     */
    private function esServicioRegularComercial(SolicitudServicio $SolSer): bool
    {
        return in_array($SolSer->SolSerTipo, ['Cliente', 'Externo', 'Generador'], true);
    }

    // Email de Solcitud de Servicio
    protected function sendemail($slug)
    {
        $SolSer = SolicitudServicio::where('SolSerSlug', $slug)->first();
        $regularComercial = $this->esServicioRegularComercial($SolSer);
        // Regulares: menos correos de estado; se exceptúa la programación del vehículo (Programado / Notificado).
        $clienteOkRegular = ! $regularComercial || in_array($SolSer->SolSerStatus, ['Programado', 'Notificado'], true);

            switch ($SolSer->SolSerStatus) {

                case 'No Conciliado':
                    if (Auth::user()->UsRol === __('adminlte::message.Cliente') || Auth::user()->UsRol === __('adminlte::message.Programador')) {
                        $email = DB::table('solicitud_servicios')
                            ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                            ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                            ->select('personals.PersEmail', 'personals.PersFirstName', 'personals.PersLastName', 'clientes.CliName', 'clientes.CliComercial', 'solicitud_servicios.*')
                            ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                            ->first();

                        $comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
                        $destinatarios = ['logistica@prosarc.com.co',
                                            'conciliaciones@prosarc.com.co',
                                            'ingtratamiento1@prosarc.com.co',
                                            'ingtratamiento2@prosarc.com.co',
                                            'ingtratamiento3@prosarc.com.co',
                                            'jefedetratamiento@prosarc.com.co',
                                            $comercial->PersEmail
                                        ];
                        $destinatarioscc = ['asistentepda@prosarc.com.co'
                                            ];
                        Mail::to($destinatarios)
                        ->cc($destinatarioscc)
                        ->send(new SolSerEmailClient($email));
                    }
                    break;

                case 'Conciliado':
                    if (Auth::user()->UsRol === __('adminlte::message.Cliente') || Auth::user()->UsRol === __('adminlte::message.Programador')) {
                        $email = DB::table('solicitud_servicios')
                            ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                            ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                            ->select('personals.PersEmail', 'personals.PersFirstName', 'personals.PersLastName', 'clientes.CliName', 'clientes.CliComercial', 'solicitud_servicios.*')
                            ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                            ->first();

                        $comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
                        $destinatarios = ['auxiliarlogistico@prosarc.com.co',
                                            $comercial->PersEmail
                                        ];
                        $destinatarioscc = ['ingtratamiento1@prosarc.com.co',
                                            'ingtratamiento2@prosarc.com.co',
                                            'ingtratamiento3@prosarc.com.co',
                                            'dirtecnica@prosarc.com.co',
                                            'conciliaciones@prosarc.com.co',
                                            'asistentedplanta@prosarc.com.co',
                                            'jefedetratamiento@prosarc.com.co',
                                            'asistenteambiental@prosarc.com.co'
                                        ];
                        Mail::to($destinatarios)
                        ->cc($destinatarioscc)
                        ->send(new SolSerEmailClient($email));
                    }else{
                        if (Auth::user()->UsRol === 'AdministradorPlanta') {
                            $email = DB::table('solicitud_servicios')
                                ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                                ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                                ->select('personals.PersEmail', 'personals.PersFirstName', 'personals.PersLastName', 'clientes.CliName', 'clientes.CliComercial', 'solicitud_servicios.*')
                                ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                                ->first();

                            $comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
                            $destinatarios = ['auxiliarlogistico@prosarc.com.co',
                                                $comercial->PersEmail
                                            ];
                            $destinatarioscc = ['auxiliarpda@prosarc.com.co',
                                                'ingtratamiento1@prosarc.com.co',
                                                'ingtratamiento2@prosarc.com.co',
                                                'ingtratamiento3@prosarc.com.co',
                                                'conciliaciones@prosarc.com.co',
                                                'asistentedplanta@prosarc.com.co',
                                                'jefedetratamiento@prosarc.com.co'
                                            ];

                            if (! $regularComercial && $SolSer->SolServMailCopia !== "null") {
                                foreach (json_decode($SolSer->SolServMailCopia) as $key => $value) {
                                    array_push($destinatarioscc, $value);
                                }
                            }

                            Mail::to($destinatarios)
                            ->cc($destinatarioscc)
                            ->send(new SolSerEmail($email));
                        }
                    }

                    break;

                case 'Programado':
                    $email = DB::table('solicitud_servicios')
                        ->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->where('progvehiculos.FK_ProgServi', '=', $SolSer->ID_SolSer)
                        ->where('progvehiculos.ProgVehDelete', 0)
                        ->first();

                    $paraCliente = $clienteOkRegular ? $email->PersEmail : self::MAIL_REGULARES_INTERNO;

                    if ($SolSer->SolServMailCopia == 'null') {
                        Log::info('paraCliente: ' . $paraCliente);
                        Log::info('email: ' . json_encode($email));
                        Mail::to($paraCliente)
                            ->send(new SolSerEmail($email));
                    } else {
                        $copias = array_values(array_unique(array_filter((array) json_decode($SolSer->SolServMailCopia))));
                        Mail::to($paraCliente)
                        ->cc($copias)
                        ->send(new SolSerEmail($email));
                    }
                    break;

                case 'Notificado':
                    $email = DB::table('solicitud_servicios')
                        ->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->where('progvehiculos.FK_ProgServi', '=', $SolSer->ID_SolSer)
                        ->where('progvehiculos.ProgVehDelete', 0)
                        ->first();
                    $comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
                    $destinatarios = ['dirtecnica@prosarc.com.co',
                                        'auxiliarlogistico@prosarc.com.co',
                                        'auxiliarpda@prosarc.com.co',
                                        'conciliaciones@prosarc.com.co',
                                        'asistenteambiental@prosarc.com.co',
                                        $comercial->PersEmail
                                    ];
                    $paraCliente = $clienteOkRegular ? $email->PersEmail : self::MAIL_REGULARES_INTERNO;

                    if ($SolSer->SolServMailCopia == "null") {
                        Mail::to($paraCliente)
                        ->cc($destinatarios)
                        ->send(new SolSerEmail($email));
                    }else{
                        foreach (json_decode($SolSer->SolServMailCopia) as $key => $value) {
                            array_push($destinatarios, $value);
                        }
                        $destinatarios = array_values(array_unique(array_filter($destinatarios)));
                        Mail::to($paraCliente)
                        ->cc($destinatarios)
                        ->send(new SolSerEmail($email));
                    }
                    return redirect()->route('vehicle-programacion.index')->with('mensaje', __('servicio notificado correctamente'));
                    break;

                case 'Completado':
                    $email = DB::table('solicitud_servicios')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'clientes.CliName')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->first();

                    $destinatarios = ['conciliaciones@prosarc.com.co',
                                    'asistentepda@prosarc.com.co'];
                    if ($clienteOkRegular) {
                        $destinatarios[] = $email->PersEmail;
                    } else {
                        $destinatarios[] = self::MAIL_REGULARES_INTERNO;
                    }

                    if ($regularComercial || $SolSer->SolServMailCopia == 'null') {
                        Mail::to($destinatarios)
                        ->send(new SolSerEmail($email));
                    } else {
                        Mail::to($destinatarios)
                        ->cc(json_decode($SolSer->SolServMailCopia))
                        ->send(new SolSerEmail($email));
                    }
                    break;

                case 'Corregido':
                    $email = DB::table('solicitud_servicios')
                        ->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'clientes.CliName', 'clientes.CliComercial')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->first();
                    $comercial = Personal::where('ID_Pers', $email->CliComercial)->first();
                    $destinatarios = ['asistentedplanta@prosarc.com.co',
                                        'dirtecnica@prosarc.com.co',
                                        'conciliaciones@prosarc.com.co',
                                        'asistenteambiental@prosarc.com.co',
                                        $comercial->PersEmail
                                    ];

                    if (! $regularComercial && $SolSer->SolServMailCopia !== 'null') {
                        foreach (json_decode($SolSer->SolServMailCopia) as $key => $value) {
                            array_push($destinatarios, $value);
                        }
                    }
                    $paraCliente = $clienteOkRegular ? $email->PersEmail : self::MAIL_REGULARES_INTERNO;
                    Mail::to($paraCliente)->cc($destinatarios)->send(new SolSerEmail($email));
                    break;
                case 'Residuo Faltante':
                    $email = DB::table('solicitud_servicios')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'clientes.CliName', 'clientes.CliComercial')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->first();

                    $cc = [self::MAIL_RESIDUOS_INTERNO];
                    if (! $regularComercial && $SolSer->SolServMailCopia !== 'null' && $SolSer->SolServMailCopia !== null) {
                        $extra = json_decode($SolSer->SolServMailCopia);
                        if (is_array($extra)) {
                            $cc = array_merge($cc, $extra);
                        }
                    }
                    $cc = array_values(array_unique(array_filter($cc)));

                    $paraCliente = $clienteOkRegular ? $email->PersEmail : self::MAIL_RESIDUOS_INTERNO;
                    Mail::to($paraCliente)->cc($cc)->send(new SolSerEmail($email));
                    break;
                case 'Recepcionado':
                    // El cliente ya recibe el PDF del recibo de material (SolSerRM / flujo RM); no duplicar con SolSerEmail.
                    break;
                case 'Certificacion':
                    $email = DB::table('solicitud_servicios')
                        ->join('progvehiculos', 'progvehiculos.FK_ProgServi', '=', 'solicitud_servicios.ID_SolSer')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'progvehiculos.ProgVehFecha', 'progvehiculos.ProgVehSalida', 'clientes.CliName', 'clientes.CliComercial')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->where('progvehiculos.FK_ProgServi', '=', $SolSer->ID_SolSer)
                        ->where('progvehiculos.ProgVehDelete', 0)
                        ->first();

                    Mail::to(self::MAIL_CERTIFICACIONES_INTERNO)->send(new SolSerEmail($email));
                    return redirect()->route('solicitud-servicio.index');
                    break;
                default:
                    $email = DB::table('solicitud_servicios')
                        ->join('personals', 'personals.ID_Pers', '=', 'solicitud_servicios.FK_SolSerPersona')
                        ->join('clientes', 'clientes.ID_Cli', '=', 'solicitud_servicios.FK_SolSerCliente')
                        ->select('personals.PersEmail', 'solicitud_servicios.*', 'clientes.CliName', 'clientes.CliComercial')
                        ->where('solicitud_servicios.SolSerSlug', '=', $SolSer->SolSerSlug)
                        ->first();

                    $paraCliente = $clienteOkRegular ? $email->PersEmail : self::MAIL_REGULARES_INTERNO;
                    if ($regularComercial || $SolSer->SolServMailCopia == 'null') {
                        Mail::to($paraCliente)
                        ->send(new SolSerEmail($email));
                    } else {
                        Mail::to($paraCliente)
                        ->cc(json_decode($SolSer->SolServMailCopia))
                        ->send(new SolSerEmail($email));
                    }
                    break;
            }
        return back();
        return redirect()->route('solicitud-servicio.index');

    }

    protected function sendEmailRespel($slug){
        $respel = DB::table('respels')
            ->join('cotizacions', 'cotizacions.ID_Coti', 'respels.FK_RespelCoti')
            ->join('sedes', 'cotizacions.FK_CotiSede', '=', 'sedes.ID_Sede')
            ->join('clientes', 'clientes.ID_Cli', '=', 'sedes.FK_SedeCli')
            ->select('respels.*', 'clientes.ID_Cli')
            ->where('respels.RespelSlug', $slug)
            ->first();

        $email = DB::table('users')
            ->join('personals', 'personals.ID_Pers', 'users.FK_UserPers')
            ->join('cargos', 'personals.FK_PersCargo', '=', 'cargos.ID_Carg')
            ->join('areas', 'cargos.CargArea', '=', 'areas.ID_Area')
            ->join('sedes', 'areas.FK_AreaSede', '=', 'sedes.ID_Sede')
            ->join('clientes', 'sedes.FK_SedeCli', '=', 'clientes.ID_Cli')
            ->where('clientes.ID_Cli', $respel->ID_Cli)
            ->select('users.email')
            ->first();

        Mail::to($email->email)->send(new RespelMail($respel));
        // return back();
        return redirect()->route('respels.index');
    }
}
