<?php

namespace Database\Seeders;

use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use Illuminate\Database\Seeder;

class SurveyQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $templates = SurveyTemplate::all();

        if ($templates->isEmpty()) {
            // Si no hay plantillas, creamos la de satisfacción por defecto
            $template = SurveyTemplate::create([
                'title' => 'General Satisfaction Survey',
                'is_active' => true,
            ]);
            $templates = collect([$template]);
        }

        foreach ($templates as $template) {
            // Set de preguntas fijas de satisfacción estructuradas correctamente
            $questions = [
                [
                    'question_text' => 'Rate your overall satisfaction with the service (1 to 5)',
                    'field_type' => 'number',
                    'options' => null,
                    'is_required' => true,
                    'order' => 1,
                ],
                [
                    'question_text' => 'Was the staff courteous and professional?',
                    'field_type' => 'radio',
                    'options' => ['Yes', 'No'], // ¡Corregido! Opciones para el componente de la UI
                    'is_required' => true,
                    'order' => 2,
                ],
                [
                    'question_text' => 'What aspects of our service could be improved?',
                    'field_type' => 'text',
                    'options' => null,
                    'is_required' => false, // Opcional para que el paciente no esté obligado a escribir
                    'order' => 3,
                ],
            ];

            foreach ($questions as $question) {
                // Al usar updateOrCreate evitamos duplicados si se corre el seeder más de una vez
                SurveyQuestion::updateOrCreate(
                    [
                        'survey_template_id' => $template->id,
                        'question_text' => $question['question_text'],
                    ],
                    [
                        'field_type' => $question['field_type'],
                        'options' => $question['options'],     // Sincronizado
                        'is_required' => $question['is_required'], // Sincronizado
                        'order' => $question['order'],
                    ]
                );
            }
        }
    }
}
