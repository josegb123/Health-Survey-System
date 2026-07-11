<?php

namespace Database\Seeders;

use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Services\SurveyReportService;
use Illuminate\Database\Seeder;

class SurveyQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $templates = SurveyTemplate::all();

        if ($templates->isEmpty()) {
            $template = SurveyTemplate::create([
                'title' => 'General Satisfaction Survey',
                'is_active' => true,
            ]);
            $templates = collect([$template]);
        }

        foreach ($templates as $template) {
            if ($template->title === SurveyReportService::MINISTRY_TEMPLATE_TITLE) {
                $this->createMinistryQuestions($template);
            } else {
                $this->createStandardQuestions($template);
            }
        }
    }

    private function createMinistryQuestions(SurveyTemplate $template): void
    {
        $questions = [
            [
                'question_text' => '¿Cómo califica su experiencia global con la IPS?',
                'field_type' => 'radio',
                'options' => [
                    ['label' => 'MUY BUENA', 'weight' => 5],
                    ['label' => 'BUENA', 'weight' => 4],
                    ['label' => 'REGULAR', 'weight' => 3],
                    ['label' => 'MALA', 'weight' => 2],
                    ['label' => 'MUY MALA', 'weight' => 1],
                ],
                'is_required' => true,
                'order' => 1,
            ],
            [
                'question_text' => '¿Recomendaría esta IPS a otras personas?',
                'field_type' => 'radio',
                'options' => [
                    ['label' => 'DEFINITIVAMENTE SÍ', 'weight' => 5],
                    ['label' => 'PROBABLEMENTE SÍ', 'weight' => 4],
                    ['label' => 'DEFINITIVAMENTE NO', 'weight' => 2],
                    ['label' => 'PROBABLEMENTE NO', 'weight' => 1],
                ],
                'is_required' => true,
                'order' => 2,
            ],
        ];

        foreach ($questions as $question) {
            SurveyQuestion::updateOrCreate(
                [
                    'survey_template_id' => $template->id,
                    'question_text' => $question['question_text'],
                ],
                [
                    'field_type' => $question['field_type'],
                    'options' => $question['options'],
                    'is_required' => $question['is_required'],
                    'order' => $question['order'],
                ]
            );
        }
    }

    private function createStandardQuestions(SurveyTemplate $template): void
    {
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
                'options' => [
                    ['label' => 'Yes', 'weight' => 5],
                    ['label' => 'No', 'weight' => 1],
                ],
                'is_required' => true,
                'order' => 2,
            ],
            [
                'question_text' => 'What aspects of our service could be improved?',
                'field_type' => 'text',
                'options' => null,
                'is_required' => false,
                'order' => 3,
            ],
        ];

        foreach ($questions as $question) {
            SurveyQuestion::updateOrCreate(
                [
                    'survey_template_id' => $template->id,
                    'question_text' => $question['question_text'],
                ],
                [
                    'field_type' => $question['field_type'],
                    'options' => $question['options'],
                    'is_required' => $question['is_required'],
                    'order' => $question['order'],
                ]
            );
        }
    }
}
