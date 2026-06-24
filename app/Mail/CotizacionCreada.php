<?php

namespace App\Mail;

use App\Cotizacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CotizacionCreada extends Mailable
{
    use Queueable, SerializesModels;

    

    /**
     * Create a new message instance.
     *
     * @param Cotizacion $cotizacion
     * @return void
     *
     *
     **/
    
     public $cotizacion;

    public function __construct(Cotizacion $cotizacion)
    {
        $this->cotizacion = $cotizacion;
    }

    public function build()
    {
        return $this ->from('notificaciones@prosarc.com.co', 'Prosarc S.A. ESP')
                     ->subject('Nueva Cotización Creada - #' . $this->cotizacion->id)
                     ->markdown('emails.Cotizacion.cotizacion_creada')
                     ->with([
                        'cotizacion' => $this->cotizacion,
                        'urlCotizacion' => url("/cotizacion/{$this->cotizacion->id_cotizacion}")
                    ]);
                    
    }
}