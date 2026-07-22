<?php

namespace App\Mail;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderConformityRequestMailable extends Mailable
{
    use Queueable, SerializesModels;

    public WorkOrder $workOrder;

    public string $link;

    public function __construct(WorkOrder $workOrder, string $link)
    {
        $this->workOrder = $workOrder;
        $this->link = $link;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Quedó todo bien? - AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conformidad',
        );
    }
}
