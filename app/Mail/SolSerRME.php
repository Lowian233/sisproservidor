<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use App\ServiceExpress;
use App\Personal;
use App\FirmasServicios;

class SolSerRME extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $pdfPath;
    public $firmas;
    public $cliente;
    public $GenerResiduos;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $pdfPath, $firmas, $cliente, $GenerResiduos)
    {
        $this->email = $email;
        $this->firmas  = $firmas;
        $this->pdfPath = $pdfPath;
        $this->cliente = $cliente;
        $this->GenerResiduos = $GenerResiduos;

        //dd($GenerResiduos);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
    $mensaje = $this->from('notificaciones@prosarc.com.co', 'Prosarc S.A. ESP');

    // Usar directamente el objeto GenerResiduos
    $fkSolSer = ($this->GenerResiduos && isset($this->GenerResiduos->FK_SolSer)) ? $this->GenerResiduos->FK_SolSer : 'N/A';

    // Adjuntar el PDF existente usando la ruta del archivo
    $mensaje->attach($this->pdfPath, [
        'as' => 'Recibo de Materia Solicitud No. ' . $fkSolSer . '.pdf', // Nombre del archivo adjunto
        'mime' => 'application/pdf',
    ]);

    $mensaje->markdown('emails.Express.ReciboMaterial')
        ->subject('Recibo de material generado para la solicitud No.' . $fkSolSer);

    return $mensaje;
    }
}
