<?php

namespace App\Services;

use App\Helpers\CalculateSurveyRating;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use DB;
use Str;

class SurveyPublicProccessorService
{
    /**
     * Orquesta la creación del paciente, calcula el rating y persiste las respuestas de forma atómica.
     */
    public function processPublicSubmission(int $templateId, array $patientData, array $answers): Survey
    {
        // 1. Convertimos las respuestas a formato plano para reutilizar tu método de cálculo de rating
        $answersPayload = collect($answers)->pluck('value', 'question_id')->toArray();
        $calculatedRating = CalculateSurveyRating::execute($templateId, $answersPayload);

        return DB::transaction(function () use ($templateId, $patientData, $answers, $calculatedRating) {

            // 2. Buscamos al paciente por su identificación fiscal/DNI, si no existe lo creamos
            // (Ajusta los campos según la estructura real de tu tabla de usuarios/pacientes)
            $patient = Patient::firstOrCreate(
                ['dni' => $patientData['dni']],
                [
                    'name' => $patientData['name'],
                    'email' => $patientData['email'],
                    'password' => bcrypt(Str::random(16)), // Contraseña aleatoria por seguridad
                ]
            );

            // 3. Creamos la cabecera vinculando el ID del paciente obtenido
            $survey = Survey::create([
                'survey_template_id' => $templateId,
                'patient_id' => $patient->id, // Foránea resuelta
                'status' => 'completed',
                'rating' => $calculatedRating,
            ]);

            // 4. Guardamos las respuestas detalladas controlando Eloquent y SoftDeletes
            foreach ($answers as $answer) {
                $value = $answer['value'];

                SurveyAnswer::create([
                    'survey_id' => $survey->id,
                    'survey_question_id' => $answer['question_id'],
                    'answer_value' => is_array($value) ? json_encode($value) : (string) $value,
                ]);
            }

            return $survey;
        });
    }
}
