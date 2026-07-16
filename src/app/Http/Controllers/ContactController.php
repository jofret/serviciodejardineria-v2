<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevoContactoMailable;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Reglas base
        $rules = [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email',
            'phone' => 'required|min:8|max:20',
            'service' => 'required|exists:categories,name',
            'message' => 'required|min:10|max:1000',
            'zona_principal' => 'required',
        ];

        // Validación condicional según la zona
        if ($request->zona_principal === 'Otra') {
            $rules['otra_zona'] = 'required|min:3|max:100';
        } else {
            $rules['partido'] = 'required';
        }

        $validated = $request->validate($rules);

        // Determinar la zona final
        if ($validated['zona_principal'] === 'Otra') {
            $zona_final = $validated['otra_zona'];
            $partido = null;
        } else {
            $zona_final = $validated['zona_principal'] . ' - ' . $validated['partido'];
            $partido = $validated['partido'];
        }

        // Buscar si el cliente ya existe por teléfono
        $customer = Customer::firstOrNew(['phone' => $validated['phone']]);

        // Actualizar o crear
        $customer->name = $validated['name'];
        $customer->email = $validated['email'];
        $customer->status = 'potencial';
        $customer->fuente = $customer->exists ? $customer->fuente : 'web';
        $customer->zona_principal = $validated['zona_principal'];
        $customer->partido = $partido;
        $customer->otra_zona = $validated['otra_zona'] ?? null;
        $customer->servicio_interes = $validated['service'];
        
        // Acumular mensajes (no sobrescribir)
        $mensajeAnterior = $customer->mensaje_inicial;
        $customer->mensaje_inicial = $mensajeAnterior 
            ? $mensajeAnterior . "\n---\n" . $validated['message']
            : $validated['message'];
        
        // Actualizar metadata
        $metadata = $customer->metadata ? json_decode($customer->metadata, true) : [];
        $metadata[] = [
            'fecha' => now()->toDateTimeString(),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip(),
            'zona_completa' => $zona_final,
            'servicio' => $validated['service'],
        ];
        $customer->metadata = json_encode($metadata);

        $customer->save();

        // Enviar emails a los destinatarios usando Mailable
        $emails = [
            'info@serviciodejardineria.com.ar',
            'jofretjofret@gmail.com',
        ];

        foreach ($emails as $email) {
            Mail::to($email)->send(new NuevoContactoMailable($customer));
        }

        $mensaje = $customer->wasRecentlyCreated 
            ? '¡Gracias por contactarnos! Te responderemos a la brevedad.'
            : '¡Gracias por contactarte nuevamente! Hemos registrado tu nuevo mensaje.';

        return redirect()->back()->with('success', $mensaje);
    }
}