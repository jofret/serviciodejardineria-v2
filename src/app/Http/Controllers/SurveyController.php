<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /**
     * Muestra el formulario de encuesta
     */
    public function show($token)
    {
        \Log::info('=== INTENTANDO ACCEDER A ENCUESTA ===');
        \Log::info('Token recibido: ' . $token);
        
        try {
            $survey = \App\Models\Survey::with('customer')
                ->where('token', $token)
                ->whereNull('answered_at')
                ->first();
            
            if (!$survey) {
                \Log::error('Encuesta no encontrada para token: ' . $token);
                
                // Buscar si existe pero ya fue respondida
                $respondida = \App\Models\Survey::where('token', $token)
                    ->whereNotNull('answered_at')
                    ->first();
                    
                if ($respondida) {
                    \Log::error('La encuesta ya fue respondida el: ' . $respondida->answered_at);
                    return response('Esta encuesta ya fue respondida', 410);
                }
                
                abort(404);
            }
            
            \Log::info('Encuesta encontrada - ID: ' . $survey->id . ', Customer: ' . $survey->customer_id);
            \Log::info('Vista a cargar: survey.show');
            
            return view('survey.show', compact('survey'));
            
        } catch (\Exception $e) {
            \Log::error('ERROR: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Guarda las respuestas de la encuesta
     */
    public function store(Request $request, $token)
    {
        $survey = Survey::where('token', $token)
            ->whereNull('answered_at')
            ->firstOrFail();
        
        $validated = $request->validate([
            'gender' => 'nullable|in:masculino,femenino',
            'occupation' => 'nullable|string|max:255',
            'birthday_day' => 'nullable|numeric|min:1|max:31',
            'birthday_month' => 'nullable|string',
            'comment' => 'required|min:10|max:1000',
        ]);
        
        $survey->update([
            'gender' => $validated['gender'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'birthday_day' => $validated['birthday_day'] ?? null,
            'birthday_month' => $validated['birthday_month'] ?? null,
            'comment' => $validated['comment'],
            'answered_at' => now(),
        ]);
        
        return redirect()->back()->with('success', '¡Gracias por tu opinión! Tu comentario nos ayuda a mejorar.');
    }
}