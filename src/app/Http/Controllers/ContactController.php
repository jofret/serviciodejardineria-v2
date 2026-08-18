<?php

namespace App\Http\Controllers;

use App\Mail\NuevoContactoMailable;
use App\Services\Altoparque\AltoparqueApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request, AltoparqueApiClient $altoparque)
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

        if ($validated['zona_principal'] === 'Otra') {
            $partido = null;
        } else {
            $partido = $validated['partido'];
        }

        // El lead se guarda en el Customer central (altoparque.com), no en
        // la base local de este sitio — ver AltoparqueApiClient::upsertContactLead().
        $remoteCustomer = $altoparque->upsertContactLead([
            'phone' => $validated['phone'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'zona' => $validated['zona_principal'],
            'partido' => $partido,
            'otra_zona' => $validated['otra_zona'] ?? null,
            'servicio_interes' => $validated['service'],
            'mensaje' => $validated['message'],
        ]);

        $contacto = (object) [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'zona_principal' => $validated['zona_principal'],
            'partido' => $partido,
            'otra_zona' => $validated['otra_zona'] ?? null,
            'servicio_interes' => $validated['service'],
            'mensaje_inicial' => $validated['message'],
        ];

        $emails = [
            'info@serviciodejardineria.com.ar',
            'jofretjofret@gmail.com',
        ];

        foreach ($emails as $email) {
            Mail::to($email)->send(new NuevoContactoMailable($contacto));
        }

        $mensaje = $remoteCustomer->wasRecentlyCreated()
            ? '¡Gracias por contactarnos! Te responderemos a la brevedad.'
            : '¡Gracias por contactarte nuevamente! Hemos registrado tu nuevo mensaje.';

        return redirect()->back()->with('success', $mensaje);
    }
}