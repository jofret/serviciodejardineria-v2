<?php

namespace App\Mail;

use App\Services\Altoparque\RemoteConversation;
use App\Services\Altoparque\RemoteCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Igual que WhatsappConversationDerivedMailable, pero para conversaciones
 * que viven en la API central de Altoparque en vez de en la base local
 * (usada por WhatsAppInboundMessageService).
 */
class AltoparqueConversationDerivedMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * stdClass en vez de WhatsappConversation: la conversación vive en la
     * API central, no hay un Eloquent model local que pasarle acá. Se arma
     * con la misma forma que espera la vista compartida.
     */
    public object $conversation;

    public string $panelUrl;

    public string $motivo;

    public function __construct(RemoteConversation $conversation, RemoteCustomer $customer, string $motivo, string $panelUrl)
    {
        $this->conversation = (object) [
            'zona' => $conversation->zona(),
            'servicio_solicitado' => $conversation->servicioSolicitado(),
            'customer' => (object) [
                'name' => $customer->name(),
                'phone' => $customer->phone(),
            ],
        ];
        $this->motivo = $motivo;
        $this->panelUrl = $panelUrl;
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
