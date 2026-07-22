<?php

namespace App\Http\Controllers\Relevador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Relevador\Concerns\AuthorizesRelevamientoEditing;
use App\Http\Controllers\Relevador\Concerns\CapitalizesFreeText;
use App\Models\Property;
use App\Models\Relevamiento;
use App\Models\WorkTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelevamientoController extends Controller
{
    use AuthorizesRelevamientoEditing;
    use CapitalizesFreeText;

    public function index(Request $request): View
    {
        $estado = $request->query('estado', 'pendiente');

        // El relevador nunca ve relevamientos en 'pendiente' (todavía no
        // enviados por el admin, ver la acción 'enviar' de RelevamientoResource).
        // Dentro de lo ya asignado, submitted_at distingue lo que todavía
        // tiene que visitar/completar de lo que ya mandó.
        $query = $request->user()->relevamientos()
            ->where('status', 'enviado_a_relevador')
            ->with('property.customer', 'category', 'serviceOrder');

        if ($estado === 'pendiente') {
            $query->whereNull('submitted_at');
        } elseif ($estado === 'enviado') {
            $query->whereNotNull('submitted_at');
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
        abort_if($relevamiento->status !== 'enviado_a_relevador', 404);

        $relevamiento->pruneEmptyWorkItems();
        $relevamiento->load('property.customer', 'category', 'serviceOrder', 'workItems.media', 'workTools');

        return view('relevador.relevamientos.show', [
            'relevamiento' => $relevamiento,
        ]);
    }

    public function autosave(Request $request, Relevamiento $relevamiento): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);

        $this->applyChanges($request, $relevamiento);

        return response()->json(['status' => 'ok']);
    }

    public function update(Request $request, Relevamiento $relevamiento): RedirectResponse
    {
        $this->authorizeEditable($request, $relevamiento);

        $this->applyChanges($request, $relevamiento, enforceRequired: true);
        $relevamiento->markAsSubmitted();

        return redirect()
            ->route('relevador.show', $relevamiento)
            ->with('status', 'Relevamiento enviado correctamente.');
    }

    public function requestReopen(Request $request, Relevamiento $relevamiento): RedirectResponse
    {
        abort_unless($relevamiento->assigned_to === $request->user()->id, 403);
        abort_if($relevamiento->status !== 'enviado_a_relevador', 403);
        abort_if($relevamiento->submitted_at === null, 403);

        if (! $relevamiento->reopen_requested_at) {
            $relevamiento->requestReopen();
        }

        return redirect()
            ->route('relevador.show', $relevamiento)
            ->with('status', 'Solicitud de reapertura enviada. Vas a poder editar el relevamiento cuando el administrador la apruebe.');
    }

    public function uploadPhoto(Request $request, Relevamiento $relevamiento): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);

        $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
        ]);

        $media = $relevamiento->addMedia($request->file('photo'))->toMediaCollection('photos');

        return response()->json([
            'id' => $media->id,
            'url' => $media->getUrl(),
        ]);
    }

    public function deletePhoto(Request $request, Relevamiento $relevamiento, int $media): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);

        $relevamiento->media()->findOrFail($media)->delete();

        return response()->json(['status' => 'ok']);
    }

    private function applyChanges(Request $request, Relevamiento $relevamiento, bool $enforceRequired = false): void
    {
        $requiredOrNullable = $enforceRequired ? 'required' : 'nullable';

        $data = $request->validate([
            'property_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(Property::PROPERTY_TYPES))],
            'property_type_other' => ['nullable', 'string', 'max:255'],
            'total_area' => ['nullable', 'numeric'],
            'estimated_price' => [$requiredOrNullable, 'numeric'],
            'workers_count' => [$requiredOrNullable, 'integer', 'min:0'],
            'estimated_duration_days' => [$requiredOrNullable, 'integer', 'min:0'],

            'requires_non_compete_clause' => ['nullable', 'boolean'],

            'work_tools' => ['nullable', 'array'],
            'work_tools.*' => ['string', 'max:255'],

            'notes' => ['nullable', 'string'],
        ]);

        $property = $relevamiento->property;

        $propertyTypeOther = $this->capitalizeFirst($data['property_type_other'] ?? null);
        $propertyType = $data['property_type'] ?? $relevamiento->property_type;
        if ($propertyType === 'otro' && filled($propertyTypeOther)) {
            $propertyType = $propertyTypeOther;
        }

        $property->update([
            'total_area' => $data['total_area'] ?? null,
        ]);

        $workToolIds = collect($data['work_tools'] ?? [])
            ->map(fn (string $name) => $this->capitalizeFirst(trim($name)))
            ->filter()
            ->unique()
            ->map(fn (string $name) => WorkTool::firstOrCreate(['name' => $name])->id);

        $relevamiento->workTools()->sync($workToolIds);

        $relevamiento->update([
            'property_type' => $propertyType,
            'requires_non_compete_clause' => $request->boolean('requires_non_compete_clause'),
            'estimated_price' => $data['estimated_price'] ?? null,
            'workers_count' => $data['workers_count'] ?? null,
            'estimated_duration_days' => $data['estimated_duration_days'] ?? null,
            'notes' => $this->capitalizeFirst($data['notes'] ?? null) ?? $relevamiento->notes,
        ]);
    }
}
