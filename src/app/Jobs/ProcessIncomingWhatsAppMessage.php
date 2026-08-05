<?php

namespace App\Jobs;

use App\Services\WhatsApp\WhatsAppInboundMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessIncomingWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $message
     * @param  array<int, array<string, mixed>>  $contacts
     */
    public function __construct(
        public array $message,
        public array $contacts,
    ) {
    }

    public function handle(WhatsAppInboundMessageService $service): void
    {
        $service->handle($this->message, $this->contacts);
    }
}
