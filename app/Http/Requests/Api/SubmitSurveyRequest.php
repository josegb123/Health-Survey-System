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
            'patient.dni' => ['required', 'integer'],
            'patient.document_type' => ['required', 'string', 'max:4'],
            'patient.email' => ['nullable', 'email', 'max:255'],
            'patient.nationality' => ['nullable', 'string', 'max:50'],
            'patient.address' => ['nullable', 'string', 'max:150'],
            'patient.phone' => ['nullable', 'string', 'max:50'],
            'patient.insurer_id' => ['nullable', 'string', 'max:50'],

            // Firma digital en base64
            'signature' => ['required', 'string'],

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

                if (! $answer || is_null($answer['value']) || $answer['value'] === '') {
                    $fail(__('Question with ID :id is required.', ['id' => $requiredId]));
                }
            }
        };

        return $rules;
    }
}
