<?php

namespace App\Services;

use App\Actions\Patient\CreatePatientAction;
use App\Actions\Patient\UpdatePatientAction;
use App\Actions\Survey\CreateSurveyAction;
use App\Actions\SurveyAnswer\CreateSurveyAnswersAction;
use App\Models\Patient;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SurveyProcessorService
{
    /**
     * Coordina la persistencia transaccional de todo el flujo de la encuesta.
     *
     * @param  array  $patientData  Datos del paciente (document_type, dni, name, etc.)
     * @param  int  $templateId  ID de la plantilla de encuesta que se está respondiendo.
     * @param  array  $answers  Respuestas mapeadas [question_id => valor].
     * @param  string|null  $signaturePath  Ruta del archivo de la firma guardada en el storage.
     *
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
                'status' => 'completed',
            ]);

            // 3. Guardar las respuestas de forma masiva (Bulk Insert)
            $answersSaved = app(CreateSurveyAnswersAction::class)->execute($survey->id, $answers);

            if (! $answersSaved && ! empty($answers)) {
                throw new \Exception('Error processing the survey answers block.');
            }

            Log::info('Encuesta procesada con éxito', [
                'survey_id' => $survey->id,
                'patient_id' => $patient->id,
            ]);

            return $survey;

        }, 3); // El '3' indica que intentará la transacción hasta 3 veces en caso de Deadlocks en la BD
    }
}
