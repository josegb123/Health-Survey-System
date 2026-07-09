<?php

namespace App\Services;

use App\Helpers\CalculateSurveyRating;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SurveyPublicProccessorService
{
    public function processPublicSubmission(int $templateId, array $patientData, array $answers, ?string $signature = null): Survey
    {
        $answersPayload = collect($answers)->pluck('value', 'question_id')->toArray();
        $calculatedRating = CalculateSurveyRating::execute($templateId, $answersPayload);

        return DB::transaction(function () use ($templateId, $patientData, $answers, $calculatedRating, $signature) {

            $patient = Patient::firstOrCreate(
                ['dni' => $patientData['dni']],
                [
                    'name' => $patientData['name'],
                    'email' => $patientData['email'] ?? null,
                    'document_type' => $patientData['document_type'] ?? null,
                    'nationality' => $patientData['nationality'] ?? null,
                    'address' => $patientData['address'] ?? null,
                    'phone' => $patientData['phone'] ?? null,
                    'insurer_id' => $patientData['insurer_id'] ?? null,
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            $signaturePath = $signature ? $this->storeSignatureFile($signature) : null;

            $survey = Survey::create([
                'survey_template_id' => $templateId,
                'patient_id' => $patient->id,
                'status' => 'completed',
                'rating' => $calculatedRating,
                'signature_path' => $signaturePath,
            ]);

            foreach ($answers as $answer) {
                $value = $answer['value'];

                SurveyAnswer::create([
                    'survey_id' => $survey->id,
                    'survey_question_id' => $answer['question_id'],
                    'answer_value' => is_array($value) ? json_encode($value) : (string) $value,
                ]);
            }

            return $survey;
        });
    }

    private function storeSignatureFile(string $base64Signature): string
    {
        if (str_contains($base64Signature, ';base64,')) {
            $base64Signature = explode(';base64,', $base64Signature)[1];
        }

        $decoded = base64_decode($base64Signature, true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('The provided signature is not a valid base64 string.');
        }

        $filename = Str::uuid() . '.png';
        $path = 'signatures/' . $filename;

        Storage::disk('local')->put($path, $decoded);

        return $path;
    }
}
