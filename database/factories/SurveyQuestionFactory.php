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

        // Definimos el tipo de campo primero para condicionar las opciones
        $fieldType = $this->faker->randomElement(['text', 'number', 'radio', 'select']);

        // Flujo alternativo: Si es radio o select, generamos un array de opciones válidas
        $options = in_array($fieldType, ['radio', 'select'])
            ? ['Excellent', 'Good', 'Regular', 'Bad']
            : null;

        return [
            'survey_template_id' => SurveyTemplate::factory(),
            'question_text'      => $this->faker->randomElement($satisfactionQuestions),
            'field_type'         => $fieldType,
            'options'            => $options, // Sincronizado con la migración
            'is_required'        => $this->faker->boolean(80), // 80% de probabilidad de ser obligatoria
            'order'              => $this->faker->numberBetween(1, 10),
        ];
    }
}
