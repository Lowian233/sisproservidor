<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Cotizacion;

class CotizacionAprobada extends Mailable
{
    use Queueable, SerializesModels;

    public $cotizacion;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Cotizacion $cotizacion)
    {
        $this->cotizacion = $cotizacion;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('notificaciones@prosarc.com.co', 'Prosarc S.A. ESP')
                    ->subject('Cotización Aprobada - #' . $this->cotizacion->id_cotizacion)
                    ->markdown('emails.Cotizacion.cotizacion_aprobada')
                    ->with([
                        'cotizacion' => $this->cotizacion,
                        'urlCotizacion' => route('cotizacion.show', ['cotizacion' => $this->cotizacion->id_cotizacion])
                    ]);
    }
}