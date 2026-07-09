<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use Illuminate\Database\Seeder;

class SurveyAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscamos todas las encuestas que estén completadas y no tengan respuestas aún
        $surveys = Survey::with('template.questions')->get();

        if ($surveys->isEmpty()) {
            $this->command->warn(__('No surveys found to seed answers for. Run SurveySeeder first.'));

            return;
        }

        foreach ($surveys as $survey) {
            $questions = $survey->template->questions;

            foreach ($questions as $question) {
                // Generamos una respuesta realista simulada según el tipo
                $answerValue = match ($question->field_type) {
                    'number' => (string) rand(4, 5), // Sesgado a buena calificación en satisfacción
                    'radio' => rand(0, 1) ? 'Yes' : 'No',
                    default => __('Everything was excellent during the medical appointment.'),
                };

                SurveyAnswer::updateOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'survey_question_id' => $question->id,
                    ],
                    [
                        'answer_value' => $answerValue,
                        'created_at' => $survey->created_at, // Sincronizamos fechas para consistencia de reportes
                        'updated_at' => $survey->created_at,
                    ]
                );
            }
        }
    }
}
