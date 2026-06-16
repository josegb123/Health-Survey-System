<?php

namespace App\Http\Requests\Api;

use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;

class SubmitSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templateId = $this->route('templateId');

        $rules = [
            // Validaciones para la creación/búsqueda del paciente
            'patient' => ['required', 'array'],
            'patient.name' => ['required', 'string', 'max:255'],
            'patient.dni' => ['required', 'string', 'max:50'],
            'patient.email' => ['required', 'email', 'max:255'],

            // Validaciones de las respuestas
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:survey_questions,id'],
            'answers.*.value' => ['nullable'],
        ];

        $requiredQuestionIds = SurveyQuestion::where('survey_template_id', $templateId)
            ->where('is_required', true)
            ->pluck('id')
            ->toArray();

        $rules['answers'][] = function ($attribute, $value, $fail) use ($requiredQuestionIds) {
            foreach ($requiredQuestionIds as $requiredId) {
                $answer = collect($value)->firstWhere('question_id', $requiredId);

                if (!$answer || is_null($answer['value']) || $answer['value'] === '') {
                    $fail(__('La pregunta con ID :id es requerida obligatoriamente.', ['id' => $requiredId]));
                }
            }
        };

        return $rules;
    }
}
