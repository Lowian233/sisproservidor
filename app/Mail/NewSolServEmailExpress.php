<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSolServEmailExpress extends Mailable implements ShouldQueue
{
    use Queueable;

    public $solExpress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($solExpress)
    {
        $this->solExpress = $solExpress;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('notificaciones@prosarc.com.co')
                    ->subject('Nueva Solicitud de Servicio '.'#'.$this->solExpress->ID_SolSer.' realizada por chatbot ')
                    ->markdown('emails.SolSer.newsolservExpress');
    }
}
