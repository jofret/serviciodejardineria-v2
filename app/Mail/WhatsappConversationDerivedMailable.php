<?php

namespace App\Mail;

use App\Filament\Resources\WhatsappConversationResource;
use App\Models\WhatsappConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WhatsappConversationDerivedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public WhatsappConversation $conversation;

    public string $panelUrl;

    public string $motivo;

    public function __construct(WhatsappConversation $conversation, string $motivo)
    {
        $this->conversation = $conversation;
        $this->motivo = $motivo;
        $this->panelUrl = WhatsappConversationResource::getUrl('manage', ['record' => $conversation]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🙋 Claudia derivó un caso de WhatsApp - Servicio de Jardinería',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.whatsapp-conversacion-derivada',
        );
    }
}
