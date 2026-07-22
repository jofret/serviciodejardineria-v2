<?php

namespace App\Http\Controllers;

use App\Mail\BudgetAcceptedMailable;
use App\Models\ServiceOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PublicBudgetController extends Controller
{
    private const ADMIN_EMAILS = [
        'info@serviciodejardineria.com.ar',
        'jofretjofret@gmail.com',
    ];

    public function show(string $token)
    {
        $order = ServiceOrder::with(['customer', 'property', 'category', 'items.media', 'relevamiento.workItems.media'])
            ->where('budget_token', $token)
            ->firstOrFail();

        return view('budget.show', ['order' => $order]);
    }

    public function accept(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $order = ServiceOrder::with(['customer', 'category'])
            ->where('budget_token', $token)
            ->firstOrFail();

        $request->validate([
            'payment_method' => ['required', 'array', 'min:1'],
            'payment_method.*' => [Rule::in(array_keys(ServiceOrder::PAYMENT_METHODS))],
        ]);

        // acceptBudget() ya es idempotente (no pisa budget_accepted_at si ya
        // estaba seteado) — usamos ese mismo estado para no reenviar el
        // aviso al admin en un doble click o si el cliente recarga la página.
        $wasAlreadyAccepted = $order->budget_accepted_at !== null;

        $order->acceptBudget((array) $request->input('payment_method', []));

        if (! $wasAlreadyAccepted) {
            Mail::to(self::ADMIN_EMAILS)->send(new BudgetAcceptedMailable($order));
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->route('budget.show', $token);
    }

    public function download(string $token): Response
    {
        $order = ServiceOrder::with(['customer', 'property', 'category', 'items', 'relevamiento.workItems'])
            ->where('budget_token', $token)
            ->firstOrFail();

        return Pdf::loadView('budget.pdf', ['order' => $order])
            ->download('presupuesto-'.$order->id.'.pdf');
    }
}
