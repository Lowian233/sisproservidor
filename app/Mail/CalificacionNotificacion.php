<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Calificacion;
use App\SolicitudServicio;

class CalificacionNotificacion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $calificacion;
    public $servicio;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($calificacion, $servicio)
    {
        $this->calificacion = $calificacion;
        $this->servicio = $servicio;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $cliente = $this->servicio->cliente;
        $clienteNombre = $cliente ? $cliente->CliName : 'Cliente';

        return $this->from('notificaciones@prosarc.com.co', 'Prosarc S.A. ESP')
                    ->subject('Nueva Calificación de Servicio - Solicitud #' . $this->servicio->ID_SolSer)
                    ->view('emails.calificacion.notificacion')
                    ->with([
                        'calificacion' => $this->calificacion,
                        'servicio' => $this->servicio,
                        'clienteNombre' => $clienteNombre,
                    ]);
    }
}

