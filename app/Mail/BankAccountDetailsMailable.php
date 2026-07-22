<?php

namespace App\Mail;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BankAccountDetailsMailable extends Mailable
{
    use Queueable, SerializesModels;

    public WorkOrder $workOrder;

    /** @var Collection<int, \App\Models\BankAccount> */
    public Collection $bankAccounts;

    public function __construct(WorkOrder $workOrder, Collection $bankAccounts)
    {
        $this->workOrder = $workOrder;
        $this->bankAccounts = $bankAccounts;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Datos para la transferencia - AltoParque',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.datos-bancarios',
        );
    }
}
