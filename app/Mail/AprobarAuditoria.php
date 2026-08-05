<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use App\SolicitudServicio;
use App\Personal;

class AprobarAuditoria extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $AsuntoAuditoria;
    public $SolicitudServicio;
    public $Observacion;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($SolicitudServicio, $Observacion)
    {
        $this->SolicitudServicio = $SolicitudServicio;
        $this->Observacion = $Observacion;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $id = (int) ($this->SolicitudServicio->ID_SolSer ?? 0);
        $tipo = $this->SolicitudServicio->SolResAuditoriaTipo ?? '';
        if ($tipo === 99 || $tipo === '99') {
            $tipo = 'Virtual';
        } elseif ($tipo === 98 || $tipo === '98') {
            $tipo = 'Presencial';
        } elseif ($tipo === 97 || $tipo === '97') {
            $tipo = 'No Auditable';
        }

        $cliNombre = 'Cliente';
        if (isset($this->SolicitudServicio['cliente']) && is_object($this->SolicitudServicio['cliente']) && ! empty($this->SolicitudServicio['cliente']->CliName)) {
            $cliNombre = $this->SolicitudServicio['cliente']->CliName;
        }

        switch ($tipo) {
            case 'Virtual':
                $AsuntoAuditoria = 'Auditoría virtual Aprobada — Servicio #' . $id . ' — ' . $cliNombre;
                break;
            case 'Presencial':
                $AsuntoAuditoria = 'Auditoría presencial Aprobada — Servicio #' . $id . ' — ' . $cliNombre;
                break;
            default:
                $AsuntoAuditoria = 'El cliente solicitó auditoría en el servicio #' . $id . ' — ' . $cliNombre;
                break;
        }

        return $this->from('notificaciones@prosarc.com.co', 'Prosarc S.A. ESP')
                    ->subject($AsuntoAuditoria)
                    ->markdown('emails.SolSer.AprobarAuditoria');
    }

}
