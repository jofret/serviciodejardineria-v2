<?php

namespace App\Http\Controllers\Relevador;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyTag;
use App\Models\Relevamiento;
use Illuminate\Http\RedirectResponse;
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

        $relevamiento->load('property.customer', 'property.tags', 'serviceOrder');

        return view('relevador.relevamientos.show', [
            'relevamiento' => $relevamiento,
        ]);
    }

    public function update(Request $request, Relevamiento $relevamiento): RedirectResponse
    {
        abort_unless($relevamiento->assigned_to === $request->user()->id, 403);
        abort_if($relevamiento->status === 'enviado', 403);

        $data = $request->validate([
            'property_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(Property::PROPERTY_TYPES))],
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

            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'image', 'max:10240'],

            'notes' => ['nullable', 'string'],
        ]);

        $property = $relevamiento->property;

        $property->update([
            'property_type' => $data['property_type'] ?? $property->property_type,
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

        foreach ($request->file('photos', []) as $photo) {
            $relevamiento->addMedia($photo)->toMediaCollection('photos');
        }

        $relevamiento->update(['notes' => $data['notes'] ?? $relevamiento->notes]);
        $relevamiento->markAsSubmitted();

        return redirect()
            ->route('relevador.show', $relevamiento)
            ->with('status', 'Relevamiento enviado correctamente.');
    }

    private function cleanRows(array $rows): array
    {
        return array_values(array_filter($rows, fn (array $row) => collect($row)->filter(fn ($value) => filled($value))->isNotEmpty()));
    }
}
