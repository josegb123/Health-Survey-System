<?php

namespace Database\Seeders;

use App\Helpers\CalculateSurveyRating;
use App\Models\Insurer;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    protected int $patientCount;

    protected int $surveysPerTemplate;

    protected int $monthsBack;

    public function __construct(
        int $patients = 30,
        int $surveysPerTemplate = 20,
        int $monthsBack = 6,
    ) {
        $this->patientCount = $patients;
        $this->surveysPerTemplate = $surveysPerTemplate;
        $this->monthsBack = $monthsBack;
    }

    public function run(): void
    {
        $this->command->info('Creating test data...');

        $insurers = $this->ensureInsurers();
        $patients = $this->createPatients($insurers);
        $templates = $this->createTemplates();

        $totalSurveys = 0;
        foreach ($templates as $template) {
            $totalSurveys += $this->createSurveysForTemplate($template, $patients);
        }

        $this->command->info("Done! Created {$totalSurveys} surveys across ".count($templates).' templates with '.count($patients).' patients.');
    }

    private function ensureInsurers()
    {
        if (Insurer::count() > 0) {
            return Insurer::all();
        }

        $data = [
            ['name' => 'EPS Sura', 'type' => 'contributory'],
            ['name' => 'EPS Sanitas', 'type' => 'contributory'],
            ['name' => 'Nueva EPS', 'type' => 'contributory'],
            ['name' => 'Mutual Ser', 'type' => 'subsidized'],
            ['name' => 'Coosalud', 'type' => 'subsidized'],
        ];

        foreach ($data as $item) {
            Insurer::updateOrCreate(
                ['name' => $item['name'], 'type' => $item['type']],
                ['is_active' => true]
            );
        }

        return Insurer::all();
    }

    private function createPatients($insurers): Collection
    {
        $existing = Patient::count();
        $toCreate = max(0, $this->patientCount - $existing);

        if ($toCreate === 0) {
            $this->command->info("Patient count already meets target ({$existing}). Skipping patient creation.");

            return Patient::all();
        }

        $patients = collect();
        $usedDnis = [];
        $documentTypes = ['CC', 'CE', 'PA', 'TI'];
        $nationalities = ['Colombiana', 'Venezolana', 'Ecuatoriana', 'Peruana'];

        for ($i = 0; $i < $toCreate; $i++) {
            do {
                $dni = random_int(10000000, 99999999);
            } while (in_array($dni, $usedDnis));

            $usedDnis[] = $dni;

            $patients->push(Patient::create([
                'document_type' => $documentTypes[array_rand($documentTypes)],
                'dni' => $dni,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'nationality' => $nationalities[array_rand($nationalities)],
                'address' => fake()->boolean(80) ? fake()->address() : null,
                'phone' => '3'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
                'insurer_id' => $insurers->random()->id,
            ]));
        }

        $this->command->info("Created {$toCreate} new patients (".Patient::count().' total).');

        return $patients;
    }

    private function createTemplates(): Collection
    {
        $templates = SurveyTemplate::with('questions')->get();

        $this->command->info('Using '.count($templates).' existing template(s).');

        return $templates;
    }

    private function createSurveysForTemplate(SurveyTemplate $template, $patients): int
    {
        $questions = SurveyQuestion::where('survey_template_id', $template->id)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            $this->command->warn("Template [{$template->title}] has no questions. Skipping.");

            return 0;
        }

        $answerableQuestions = $questions->filter(fn ($q) => $q->field_type !== 'text');

        $surveysToCreate = $this->surveysPerTemplate;
        $created = 0;
        $startDate = now()->subMonths($this->monthsBack);
        $endDate = now();

        DB::transaction(function () use ($template, $patients, $questions, $surveysToCreate, $startDate, $endDate, &$created) {
            for ($i = 0; $i < $surveysToCreate; $i++) {
                $patient = $patients->random();
                $createdAt = fake()->dateTimeBetween($startDate, $endDate);

                $survey = Survey::create([
                    'survey_template_id' => $template->id,
                    'patient_id' => $patient->id,
                    'status' => 'completed',
                    'completed_at' => $createdAt,
                    'signature_path' => fake()->boolean(70)
                        ? 'signatures/'.Str::uuid().'.png'
                        : null,
                ]);

                Survey::where('id', $survey->id)->update([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $answerWeights = [];
                foreach ($questions as $question) {
                    $answerValue = $this->generateAnswer($question);
                    $weightedValue = $this->computeWeight($question, $answerValue);

                    $answerWeights[$question->id] = ['value' => $answerValue, 'weight' => $weightedValue];

                    SurveyAnswer::create([
                        'survey_id' => $survey->id,
                        'survey_question_id' => $question->id,
                        'answer_value' => $answerValue,
                        'weighted_value' => $weightedValue,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                $validWeights = collect($answerWeights)
                    ->filter(fn ($a) => $a['weight'] !== null && $a['weight'] > 0);

                if ($validWeights->isNotEmpty()) {
                    $rating = round($validWeights->avg('weight'), 2);
                    $survey->update(['rating' => $rating]);
                }

                $created++;
            }
        });

        $this->command->info("  [{$template->title}] {$created} surveys created.");

        return $created;
    }

    private function generateAnswer(SurveyQuestion $question): string
    {
        return match ($question->field_type) {
            'number' => (string) random_int(1, 5),
            'radio', 'select' => $this->weightedRandomOption($question),
            default => fake()->sentence(4),
        };
    }

    private function weightedRandomOption(SurveyQuestion $question): string
    {
        $options = $question->options ?? [];

        if (empty($options)) {
            return 'Sí';
        }

        $labels = array_map(fn ($opt) => $opt['label'] ?? $opt, $options);
        $weights = array_map(fn ($opt) => $opt['weight'] ?? 5, $options);

        $totalWeight = array_sum($weights);
        $random = random_int(1, (int) ($totalWeight * 100)) / 100;

        $cumulative = 0;
        foreach ($labels as $index => $label) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $label;
            }
        }

        return end($labels);
    }

    private function computeWeight(SurveyQuestion $question, string $answerValue): ?float
    {
        $options = $question->options ?? [];

        if (empty($options)) {
            return null;
        }

        $normalizedAnswer = CalculateSurveyRating::normalize($answerValue);

        foreach ($options as $opt) {
            $optLabel = $opt['label'] ?? '';
            if (CalculateSurveyRating::normalize($optLabel) === $normalizedAnswer) {
                return (float) ($opt['weight'] ?? 0);
            }
        }

        return null;
    }
}
