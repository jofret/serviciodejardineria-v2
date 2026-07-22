<?php

namespace App\Mail;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderConformityMailable extends Mailable
{
    use Queueable, SerializesModels;

    public WorkOrder $workOrder;

    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Conformidad confirmada - AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conformidad-confirmada',
        );
    }
}
