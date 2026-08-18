<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoContactoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public object $customer;

    /**
     * $customer es un stdClass con los datos del formulario de contacto
     * (ver ContactController::send()) — el Customer real ahora vive en
     * altoparque.com, no en la base local de este sitio.
     */
    public function __construct(object $customer)
    {
        $this->customer = $customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📬 Nuevo contacto - AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nuevo-contacto',
        );
    }
}