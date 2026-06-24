<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RespelPublicAprobadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $respel;

    /**
     * Create a new message instance.
     *
     * @param \App\Respel $respel
     * @return void
     */
    public function __construct($respel)
    {
        $this->respel = $respel;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('residuos@prosarc.com.co', 'Prosarc S.A. ESP')
                    ->subject('Residuo Aprobado: ' . $this->respel->RespelName)
                    ->markdown('emails.Respelpublic.emailrespelcomun')
                    ->with([
                        'respelName' => $this->respel->RespelName,
                        'respelSlug' => $this->respel->RespelSlug,
                    ]);
    }
}