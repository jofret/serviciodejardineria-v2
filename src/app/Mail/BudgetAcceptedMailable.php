<?php

namespace App\Mail;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetAcceptedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public ServiceOrder $order;

    public function __construct(ServiceOrder $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Presupuesto aceptado - AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.presupuesto-aceptado',
        );
    }
}
