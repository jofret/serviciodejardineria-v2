<?php

namespace App\Mail;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetMailable extends Mailable
{
    use Queueable, SerializesModels;

    public ServiceOrder $order;

    public string $link;

    public function __construct(ServiceOrder $order, string $link)
    {
        $this->order = $order;
        $this->link = $link;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu presupuesto de AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.presupuesto',
        );
    }
}
