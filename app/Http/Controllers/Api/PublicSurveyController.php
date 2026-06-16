<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyTemplate;
use App\Http\Requests\Api\SubmitSurveyRequest;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Services\SurveyProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PublicSurveyController extends Controller
{
    /**
     * Devuelve la estructura completa de una plantilla y sus preguntas en formato JSON.
     */
    public function show(int $id): JsonResponse
    {
        // 1. Buscamos la plantilla cargando sus preguntas asociadas en una sola consulta SQL
        $template = SurveyTemplate::with([
            'questions' => function ($query) {
                $query->orderBy('id', 'asc'); // Mantenemos el orden secuencial de creación
            }
        ])->find($id);

        // 2. Flujo alternativo: Si la plantilla no existe o está inactiva, bloqueamos con un 404
        if (!$template || !$template->is_active) {
            return response()->json([
                'success' => false,
                'message' => __('La plantilla solicitada no está disponible o no existe.')
            ], 404);
        }

        // 3. Retornamos la data estructurada de forma clara para el consumo de Astro
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $template->id,
                'title' => $template->title,
                'questions' => $template->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question_text' => $question->question_text,
                        'field_type' => $question->field_type,
                        'is_required' => (bool) $question->is_required,
                        // Decodificamos las opciones si es un campo de selección (radio/select)
                        'options' => is_array($question->options)
                            ? $question->options
                            : json_decode($question->options, true) ?? []
                    ];
                })
            ]
        ], 200);
    }

    /**
     * Delega el procesamiento completo de la encuesta y el paciente al servicio especializado.
     */
    public function store(SubmitSurveyRequest $request, int $templateId, SurveyProcessorService $processor): JsonResponse
    {
        try {
            $survey = $processor->processPublicSubmission(
                $templateId,
                $request->input('patient'),
                $request->input('answers')
            );

            return response()->json([
                'success' => true,
                'message' => __('Encuesta y registro de paciente procesados con éxito.'),
                'data' => [
                    'survey_id' => $survey->id,
                    'patient_id' => $survey->patient_id,
                    'rating_assigned' => $survey->rating
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Ocurrió un error interno al procesar la solicitud.'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
