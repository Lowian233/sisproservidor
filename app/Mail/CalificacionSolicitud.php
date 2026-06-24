<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Calificacion;
use App\SolicitudServicio;

class CalificacionSolicitud extends Mailable
{
    use Queueable, SerializesModels;

    public $calificacion;
    public $servicio;
    public $urlCalificacion;
    public $urlPDFRecibo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($calificacion, $servicio, $urlCalificacion, $urlPDFRecibo = null)
    {
        $this->calificacion = $calificacion;
        $this->servicio = $servicio;
        $this->urlCalificacion = $urlCalificacion;
        $this->urlPDFRecibo = $urlPDFRecibo;
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
                    ->subject('Califica nuestro servicio de recolección - Solicitud #' . $this->servicio->ID_SolSer)
                    ->markdown('emails.calificacion.solicitud')
                    ->with([
                        'calificacion' => $this->calificacion,
                        'servicio' => $this->servicio,
                        'clienteNombre' => $clienteNombre,
                        'urlCalificacion' => $this->urlCalificacion,
                        'urlPDFRecibo' => $this->urlPDFRecibo,
                    ]);
    }
}

