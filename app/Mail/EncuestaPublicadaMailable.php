<?php

namespace App\Mail;

use App\Models\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EncuestaPublicadaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Survey $survey;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu comentario ya está publicado! - AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.encuesta-publicada',
        );
    }
}
