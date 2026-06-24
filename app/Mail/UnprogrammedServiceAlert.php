<?php
namespace App\Mail;
use App\SolicitudServicio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class UnprogrammedServiceAlert extends Mailable
{
    use Queueable, SerializesModels;
    public $service;
    public function __construct(SolicitudServicio $service)
    {
        $this->service = $service;
    }
    public function build()
    {
        return $this->subject("Alerta: Servicio sin programar")
        ->markdown('emails.SolSer.unprogrammed-service-alert');
    }
}