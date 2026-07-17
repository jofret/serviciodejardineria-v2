<?php

namespace App\Http\Controllers\Relevador;

use App\Http\Controllers\Controller;
use App\Models\Relevamiento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelevamientoController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->query('estado', 'pendiente');

        $query = $request->user()->relevamientos()->with('property.customer', 'serviceOrder');

        if (in_array($estado, ['pendiente', 'enviado'], true)) {
            $query->where('status', $estado);
        }

        $relevamientos = $query->orderByDesc('scheduled_date')->get();

        return view('relevador.relevamientos.index', [
            'relevamientos' => $relevamientos,
            'estado' => $estado,
        ]);
    }

    public function show(Request $request, Relevamiento $relevamiento): View
    {
        abort_unless($relevamiento->assigned_to === $request->user()->id, 403);

        $relevamiento->load('property.customer', 'serviceOrder');

        return view('relevador.relevamientos.show', [
            'relevamiento' => $relevamiento,
        ]);
    }
}
