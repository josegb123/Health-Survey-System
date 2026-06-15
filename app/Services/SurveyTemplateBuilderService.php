<?php

namespace App\Services;

use App\Actions\SurveyTemplate\CreateSurveyTemplateAction;
use App\Actions\SurveyQuestion\CreateSurveyQuestionAction;
use App\Models\SurveyTemplate;
use Illuminate\Support\Facades\DB;

class SurveyTemplateBuilderService
{
    /**
     * Construye una plantilla completa con su juego de preguntas en una sola transacción.
     *
     * @param array $templateData ['title' => string, 'is_active' => bool]
     * @param array $questions Array de preguntas [['question_text' => ..., 'field_type' => ..., 'options' => ..., 'is_required' => ...]]
     * @return SurveyTemplate
     * @throws \Exception
     */
    public function createWithQuestions(array $templateData, array $questions): SurveyTemplate
    {
        return DB::transaction(function () use ($templateData, $questions) {

            // 1. Crear la cabecera de la plantilla usando la Action atómica
            $template = app(CreateSurveyTemplateAction::class)->execute($templateData);

            // 2. Iterar e insertar cada pregunta asignándole su orden correspondiente
            foreach ($questions as $index => $question) {
                app(CreateSurveyQuestionAction::class)->execute([
                    'survey_template_id' => $template->id,
                    'question_text' => $question['question_text'],
                    'field_type' => $question['field_type'],
                    'options' => $question['options'] ?? null,
                    'is_required' => $question['is_required'] ?? true,
                    'order' => $index + 1
                ]);
            }

            return $template;
        });
    }
}
