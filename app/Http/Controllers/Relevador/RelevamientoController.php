<?php

namespace App\Http\Controllers\Relevador;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyTag;
use App\Models\Relevamiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelevamientoController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->query('estado', 'pendiente');

        // El relevador nunca ve relevamientos en 'borrador': todavía no fueron
        // asignados/publicados por el admin, ver RelevamientoResource::asignar.
        $query = $request->user()->relevamientos()
            ->whereIn('status', ['pendiente', 'enviado'])
            ->with('property.customer', 'category', 'serviceOrder');

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
        abort_if($relevamiento->status === 'borrador', 404);

        $relevamiento->load('property.customer', 'property.tags', 'category', 'serviceOrder');

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

        $this->applyChanges($request, $relevamiento);
        $relevamiento->markAsSubmitted();

        return redirect()
            ->route('relevador.show', $relevamiento)
            ->with('status', 'Relevamiento enviado correctamente.');
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

    private function authorizeEditable(Request $request, Relevamiento $relevamiento): void
    {
        abort_unless($relevamiento->assigned_to === $request->user()->id, 403);
        abort_if(in_array($relevamiento->status, ['borrador', 'enviado'], true), 403);
    }

    private function applyChanges(Request $request, Relevamiento $relevamiento): void
    {
        $data = $request->validate([
            'property_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(Property::PROPERTY_TYPES))],
            'property_type_other' => ['nullable', 'string', 'max:255'],
            'total_area' => ['nullable', 'numeric'],

            'has_garden' => ['nullable', 'boolean'],
            'garden_areas' => ['nullable', 'array'],
            'garden_areas.*.name' => ['nullable', 'string', 'max:255'],
            'garden_areas.*.size' => ['nullable', 'numeric'],

            'has_pool' => ['nullable', 'boolean'],
            'pools' => ['nullable', 'array'],
            'pools.*.type' => ['nullable', 'string', 'in:'.implode(',', array_keys(Property::POOL_TYPES))],
            'pools.*.liters' => ['nullable', 'numeric'],
            'pools.*.size_m2' => ['nullable', 'numeric'],

            'has_trees' => ['nullable', 'boolean'],
            'trees' => ['nullable', 'array'],
            'trees.*.species' => ['nullable', 'string', 'max:255'],
            'trees.*.quantity' => ['nullable', 'numeric'],
            'trees.*.height' => ['nullable', 'numeric'],

            'has_plants' => ['nullable', 'boolean'],
            'plants' => ['nullable', 'array'],
            'plants.*.species' => ['nullable', 'string', 'max:255'],
            'plants.*.quantity' => ['nullable', 'numeric'],

            'has_sport_areas' => ['nullable', 'boolean'],
            'sport_areas' => ['nullable', 'array'],
            'sport_areas.*.type' => ['nullable', 'string', 'in:'.implode(',', array_keys(Property::SPORT_AREA_TYPES))],
            'sport_areas.*.quantity' => ['nullable', 'numeric'],

            'tags' => ['nullable', 'string', 'max:1000'],

            'notes' => ['nullable', 'string'],
        ]);

        $property = $relevamiento->property;

        $propertyType = $data['property_type'] ?? $relevamiento->property_type;
        if ($propertyType === 'otro' && filled($data['property_type_other'] ?? null)) {
            $propertyType = $data['property_type_other'];
        }

        $property->update([
            'total_area' => $data['total_area'] ?? null,
            'has_garden' => $request->boolean('has_garden'),
            'garden_areas' => $this->cleanRows($data['garden_areas'] ?? []),
            'has_pool' => $request->boolean('has_pool'),
            'pools' => $this->cleanRows($data['pools'] ?? []),
            'has_trees' => $request->boolean('has_trees'),
            'trees' => $this->cleanRows($data['trees'] ?? []),
            'has_plants' => $request->boolean('has_plants'),
            'plants' => $this->cleanRows($data['plants'] ?? []),
            'has_sport_areas' => $request->boolean('has_sport_areas'),
            'sport_areas' => $this->cleanRows($data['sport_areas'] ?? []),
        ]);

        $tagIds = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => PropertyTag::firstOrCreate(['name' => $name])->id);

        $property->tags()->sync($tagIds);

        $relevamiento->update([
            'property_type' => $propertyType,
            'notes' => $data['notes'] ?? $relevamiento->notes,
        ]);
    }

    private function cleanRows(array $rows): array
    {
        return array_values(array_filter($rows, fn (array $row) => collect($row)->filter(fn ($value) => filled($value))->isNotEmpty()));
    }
}
