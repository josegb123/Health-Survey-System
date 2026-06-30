<?php

namespace App\Actions\Survey;

use App\Models\Survey;

class CreateSurveyAction
{
    /**
     * Registra la cabecera de una encuesta en el sistema.
     *
     * @param  array  $data  Datos validados de la encuesta.
     */
    public function execute(array $data): Survey
    {
        return Survey::create([
            'patient_id' => $data['patient_id'],
            'survey_template_id' => $data['survey_template_id'],
            'signature_path' => $data['signature_path'] ?? null,
            'status' => $data['status'] ?? 'completed', // completed, under_review, archived
        ]);
    }
}
