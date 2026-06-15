<?php

namespace App\Actions\SurveyQuestion;

use App\Models\SurveyQuestion;

class CreateSurveyQuestionAction
{
    /**
     * Registra una pregunta y la vincula a una plantilla.
     *
     * @param array $data Datos validados de la pregunta.
     * @return SurveyQuestion
     */
    public function execute(array $data): SurveyQuestion
    {
        return SurveyQuestion::create([
            'survey_template_id' => $data['survey_template_id'],
            'question_text' => $data['question_text'],
            'field_type' => $data['field_type'],
            'options' => $data['options'] ?? null,
            'order' => $data['order'] ?? 0,
            'is_required' => $data['is_required'] ?? true,
        ]);
    }
}
