<?php

namespace App\Http\Controllers\Relevador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Relevador\Concerns\AuthorizesRelevamientoEditing;
use App\Models\Relevamiento;
use App\Models\RelevamientoWorkItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelevamientoWorkItemController extends Controller
{
    use AuthorizesRelevamientoEditing;

    public function store(Request $request, Relevamiento $relevamiento): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);

        $item = $relevamiento->workItems()->create([
            'order' => $relevamiento->workItems()->count(),
        ]);

        return response()->json([
            'id' => $item->id,
            'html' => view('relevador.relevamientos._work_item', ['item' => $item])->render(),
        ]);
    }

    public function update(Request $request, Relevamiento $relevamiento, RelevamientoWorkItem $item): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);
        abort_unless($item->relevamiento_id === $relevamiento->id, 404);

        $data = $request->validate([
            'description' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
        ]);

        $item->update($data);

        return response()->json(['status' => 'ok']);
    }

    public function destroy(Request $request, Relevamiento $relevamiento, RelevamientoWorkItem $item): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);
        abort_unless($item->relevamiento_id === $relevamiento->id, 404);

        $item->delete();

        return response()->json(['status' => 'ok']);
    }

    public function uploadPhoto(Request $request, Relevamiento $relevamiento, RelevamientoWorkItem $item): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);
        abort_unless($item->relevamiento_id === $relevamiento->id, 404);

        $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
        ]);

        $media = $item->addMedia($request->file('photo'))->toMediaCollection('photos');

        return response()->json([
            'id' => $media->id,
            'url' => $media->getUrl(),
        ]);
    }

    public function deletePhoto(Request $request, Relevamiento $relevamiento, RelevamientoWorkItem $item, int $media): JsonResponse
    {
        $this->authorizeEditable($request, $relevamiento);
        abort_unless($item->relevamiento_id === $relevamiento->id, 404);

        $item->media()->findOrFail($media)->delete();

        return response()->json(['status' => 'ok']);
    }
}
