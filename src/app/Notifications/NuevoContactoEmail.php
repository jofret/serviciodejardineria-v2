<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NuevoContactoEmail extends Notification
{
    use Queueable;

    protected $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $zona = $this->customer->zona_principal === 'Otra' 
            ? $this->customer->otra_zona 
            : $this->customer->zona_principal . ' - ' . $this->customer->partido;

        return (new MailMessage)
            ->subject('📬 Nuevo contacto - Limpieza de Terrenos')
            ->greeting('¡Hola!')
            ->line('Se ha recibido un nuevo contacto desde la web:')
            ->line('')
            ->line("👤 **Nombre:** {$this->customer->name}")
            ->line("📞 **Teléfono:** {$this->customer->phone}")
            ->line("✉️ **Email:** {$this->customer->email}")
            ->line("📍 **Zona:** {$zona}")
            ->line("🔧 **Servicio:** {$this->customer->servicio_interes}")
            ->line("💬 **Mensaje:**")
            ->line($this->customer->mensaje_inicial)
            ->line('')
            ->line('👉 Para ver más detalles:')
            ->action('Ver en el panel', url('/admin/customers/' . $this->customer->id))
            ->salutation('Saludos, sistema automático');
    }
}