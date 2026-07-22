<?php

namespace App\Http\Controllers;

use App\Mail\WorkOrderConformityMailable;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicWorkOrderController extends Controller
{
    private const ADMIN_EMAILS = [
        'info@serviciodejardineria.com.ar',
        'jofretjofret@gmail.com',
    ];

    public function show(string $token)
    {
        $workOrder = WorkOrder::with(['serviceOrder.customer', 'serviceOrder.property', 'serviceOrder.category', 'checklistItems'])
            ->where('conformity_token', $token)
            ->firstOrFail();

        return view('conformity.show', ['workOrder' => $workOrder]);
    }

    public function confirm(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $workOrder = WorkOrder::with('serviceOrder.customer')
            ->where('conformity_token', $token)
            ->firstOrFail();

        // confirmConformity() ya es idempotente — usamos ese mismo estado
        // para no reenviar el aviso al admin en un doble click o recarga.
        $wasAlreadyConfirmed = $workOrder->conformity_confirmed_at !== null;

        $workOrder->confirmConformity();

        if (! $wasAlreadyConfirmed) {
            Mail::to(self::ADMIN_EMAILS)->send(new WorkOrderConformityMailable($workOrder));
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->route('conformity.show', $token);
    }
}
