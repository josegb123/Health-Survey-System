<?php

namespace App\Actions\SurveyTemplate;

use App\Models\SurveyTemplate;

class CreateSurveyTemplateAction
{
    /**
     * Crea una nueva plantilla de encuesta.
     *
     * @param array $data Datos validados (title, is_active).
     * @return SurveyTemplate
     */
    public function execute(array $data): SurveyTemplate
    {
        return SurveyTemplate::create([
            'title' => $data['title'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
