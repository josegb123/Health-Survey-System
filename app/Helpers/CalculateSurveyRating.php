<?php

namespace App\Helpers;

use App\Models\SurveyQuestion;

class CalculateSurveyRating
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Calcula el promedio de las respuestas numéricas basándose en la estructura de la plantilla.
     *
     * * @param int $templateId ID de la plantilla para validar tipos de campo
     * @param  array  $answers  Estructura tipica de respuestas: [$questionId => ['value' => X]] o [$questionId => X]
     * @return float|null Retorna el promedio o null si no se respondieron preguntas numéricas
     */
    public static function execute(int $templateId, array $answers): ?float
    {
        // 1. Cargamos las preguntas de la plantilla que sean estrictamente de tipo numérico
        $numericQuestionIds = SurveyQuestion::where('survey_template_id', $templateId)
            ->where('field_type', 'number')
            ->pluck('id')
            ->toArray();

        if (empty($numericQuestionIds)) {
            return null;
        }

        $sum = 0;
        $count = 0;

        // 2. Iteramos solo sobre las respuestas numéricas válidas enviadas por el front-end
        foreach ($numericQuestionIds as $questionId) {
            if (! isset($answers[$questionId])) {
                continue;
            }

            // Normalizamos el formato de la data (por si viene directo o dentro de un sub-array 'value')
            $rawData = $answers[$questionId];
            $value = is_array($rawData) ? ($rawData['value'] ?? null) : $rawData;

            // Validamos que sea un número real para evitar brechas de tipos en PHP
            if (is_numeric($value)) {
                $sum += (float) $value;
                $count++;
            }
        }

        // 3. Retornamos el promedio redondeado a dos decimales (puerta abierta a cualquier escala: 1-5, 1-10, etc.)
        return $count > 0 ? round($sum / $count, 2) : null;
    }
}
