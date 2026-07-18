<?php

namespace App\Http\Controllers\Relevador\Concerns;

use App\Models\Relevamiento;
use Illuminate\Http\Request;

trait AuthorizesRelevamientoEditing
{
    private function authorizeEditable(Request $request, Relevamiento $relevamiento): void
    {
        abort_unless($relevamiento->assigned_to === $request->user()->id, 403);
        abort_if($relevamiento->status !== 'enviado_a_relevador', 403);
        abort_if($relevamiento->submitted_at !== null, 403);
    }
}
