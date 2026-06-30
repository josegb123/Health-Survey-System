<?php

namespace App\Actions\Survey;

use App\Models\Survey;

class UpdateSurveyAction
{
    /**
     * Actualiza los metadatos o estado de una encuesta existente.
     *
     * @param  int  $id  ID de la encuesta.
     * @param  array  $data  Datos a modificar.
     */
    public function execute(int $id, array $data): Survey
    {
        $survey = Survey::findOrFail($id);

        $survey->update([
            'signature_path' => $data['signature_path'] ?? $survey->signature_path,
            'status' => $data['status'] ?? $survey->status,
        ]);

        return $survey;
    }
}
