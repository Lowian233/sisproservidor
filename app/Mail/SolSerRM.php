<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use App\SolicitudServicio;
use App\Personal;
use App\FirmasServicios;

class SolSerRM extends Mailable
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

        $fileName = 'Recibo de Materia Solicitud No. ' . $this->GenerResiduos->FK_SolSer . '.pdf';

        // Adjuntar desde ruta; si no existe, adjuntar desde memoria para evitar fallo de envío.
        if (is_string($this->pdfPath) && file_exists($this->pdfPath)) {
            $mensaje->attach($this->pdfPath, [
                'as' => $fileName,
                'mime' => 'application/pdf',
            ]);
        } elseif (is_object($this->email) && method_exists($this->email, 'output')) {
            $mensaje->attachData($this->email->output(), $fileName, [
                'mime' => 'application/pdf',
            ]);
        }

        $mensaje->markdown('emails.SolSer.ReciboMaterial')
                ->subject('Recibo de material generado para la solicitud No.' .$this->GenerResiduos->FK_SolSer);

        return $mensaje;
    }
}
