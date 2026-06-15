<?php

namespace App\Actions\SurveyQuestion;

use App\Models\SurveyQuestion;

class UpdateSurveyQuestionAction
{
    /**
     * Modifica los parámetros o el orden de una pregunta existente.
     *
     * @param int $id ID de la pregunta.
     * @param array $data Datos a modificar.
     * @return SurveyQuestion
     */
    public function execute(int $id, array $data): SurveyQuestion
    {
        $question = SurveyQuestion::findOrFail($id);

        $question->update([
            'question_text' => $data['question_text'] ?? $question->question_text,
            'field_type' => $data['field_type'] ?? $question->field_type,
            'options' => $data['options'] ?? $question->options,
            'order' => $data['order'] ?? $question->order,
            'is_required' => $data['is_required'] ?? $question->is_required,
        ]);

        return $question;
    }
}
