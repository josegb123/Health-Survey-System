<?php

namespace Database\Factories;

use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $satisfactionQuestions = [
            'How would you rate the quality of the service received?',
            'Was the waiting time acceptable to you?',
            'Would you recommend our services to family and friends?',
            'How clear was the information provided by our staff?',
            'Please leave any additional comments or suggestions to improve.'
        ];

        return [
            'survey_template_id' => SurveyTemplate::factory(),
            // Guardamos la llave en inglés dentro de __() para la traducción universal
            'question_text' => $this->faker->randomElement($satisfactionQuestions),
            'field_type' => $this->faker->randomElement(['text', 'number', 'radio', 'select']),
            'order' => $this->faker->numberBetween(1, 5),
        ];
    }
}
