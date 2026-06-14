<?php

namespace Database\Seeders;

use App\Models\SurveyTemplate;
use App\Models\SurveyQuestion;
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
                'description' => 'Survey to evaluate the quality of service provided to the patient.',
                'is_active' => true
            ]);
            $templates = collect([$template]);
        }

        foreach ($templates as $template) {
            // Set de preguntas fijas de satisfacción con traducción universal
            $questions = [
                [
                    'question_text' => 'Rate your overall satisfaction with the service (1 to 5)',
                    'field_type' => 'number',
                    'order' => 1
                ],
                [
                    'question_text' => 'Was the staff courteous and professional?',
                    'field_type' => 'radio', // Para respuestas tipo Sí/No
                    'order' => 2
                ],
                [
                    'question_text' => 'What aspects of our service could be improved?',
                    'field_type' => 'text',
                    'order' => 3
                ],
            ];

            foreach ($questions as $question) {
                // Al usar updateOrCreate evitamos duplicados si se corre el seeder más de una vez
                SurveyQuestion::updateOrCreate(
                    [
                        'survey_template_id' => $template->id,
                        'question_text' => $question['question_text']
                    ],
                    [
                        'field_type' => $question['field_type'],
                        'order' => $question['order']
                    ]
                );
            }
        }
    }
}
