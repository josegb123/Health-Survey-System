<?php

namespace App\Services;

use App\Actions\Patient\CreatePatientAction;
use App\Actions\Patient\UpdatePatientAction;
use App\Actions\Survey\CreateSurveyAction;
use App\Actions\SurveyAnswer\CreateSurveyAnswersAction;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SurveyProcessorService
{
    /**
     * Coordina la persistencia transaccional de todo el flujo de la encuesta.
     *
     * @param array $patientData Datos del paciente (document_type, dni, name, etc.)
     * @param int $templateId ID de la plantilla de encuesta que se está respondiendo.
     * @param array $answers Respuestas mapeadas [question_id => valor].
     * @param string|null $signaturePath Ruta del archivo de la firma guardada en el storage.
     * @return Survey
     * @throws \Exception
     */
    public function process(array $patientData, int $templateId, array $answers, ?string $signaturePath = null): Survey
    {
        // Iniciamos una transacción de base de datos para asegurar atomisidad global
        return DB::transaction(function () use ($patientData, $templateId, $answers, $signaturePath) {

            // 1. Gestionar el Paciente (Búsqueda manual para evitar condicionales dentro de los Actions)
            $patient = Patient::where('document_type', $patientData['document_type'])
                ->where('dni', $patientData['dni'])
                ->first();

            if ($patient) {
                // Si ya existe, actualizamos sus datos de contacto/cobertura
                $patient = app(UpdatePatientAction::class)->execute($patient->id, $patientData);
            } else {
                // Si es nuevo, lo creamos desde cero
                $patient = app(CreatePatientAction::class)->execute($patientData);
            }

            // 2. Crear la cabecera de la Encuesta (Survey)
            $survey = app(CreateSurveyAction::class)->execute([
                'patient_id' => $patient->id,
                'survey_template_id' => $templateId,
                'signature_path' => $signaturePath,
                'status' => 'completed'
            ]);

            // 3. Guardar las respuestas de forma masiva (Bulk Insert)
            $answersSaved = app(CreateSurveyAnswersAction::class)->execute($survey->id, $answers);

            if (!$answersSaved && !empty($answers)) {
                throw new \Exception("Error al procesar el bloque de respuestas de la encuesta.");
            }

            Log::info("Encuesta procesada con éxito", [
                'survey_id' => $survey->id,
                'patient_id' => $patient->id
            ]);

            return $survey;

        }, 3); // El '3' indica que intentará la transacción hasta 3 veces en caso de Deadlocks en la BD
    }

    /**
     * Orquesta la creación del paciente, calcula el rating y persiste las respuestas de forma atómica.
     */
    public function processPublicSubmission(int $templateId, array $patientData, array $answers): Survey
    {
        // 1. Convertimos las respuestas a formato plano para reutilizar tu método de cálculo de rating
        $answersPayload = collect($answers)->pluck('value', 'question_id')->toArray();
        $calculatedRating = $this->calculateSurveyRating($templateId, $answersPayload);

        return DB::transaction(function () use ($templateId, $patientData, $answers, $calculatedRating) {

            // 2. Buscamos al paciente por su identificación fiscal/DNI, si no existe lo creamos
            // (Ajusta los campos según la estructura real de tu tabla de usuarios/pacientes)
            $patient = Patient::firstOrCreate(
                ['dni' => $patientData['dni']],
                [
                    'name' => $patientData['name'],
                    'email' => $patientData['email'],
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)), // Contraseña aleatoria por seguridad
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
    /**
     * Calcula el promedio de las respuestas numéricas basándose en la estructura de la plantilla.
     * * @param int $templateId ID de la plantilla para validar tipos de campo
     * @param array $answers Estructura tipica de respuestas: [$questionId => ['value' => X]] o [$questionId => X]
     * @return float|null Retorna el promedio o null si no se respondieron preguntas numéricas
     */
    public function calculateSurveyRating(int $templateId, array $answers): ?float
    {
        // 1. Cargamos las preguntas de la plantilla que sean estrictamente de tipo numérico
        $numericQuestionIds = \App\Models\SurveyQuestion::where('survey_template_id', $templateId)
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
            if (!isset($answers[$questionId])) {
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
