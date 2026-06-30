<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $template = SurveyTemplate::where('is_active', true)->first();

        if ($patients->isEmpty() || ! $template) {
            $this->command->warn('Ensure PatientSeeder and SurveyTemplateSeeder are run before SurveySeeder.');

            return;
        }

        // Generamos un set masivo controlado para pruebas de rendimiento local (ej. 50 encuestas)
        Survey::factory()
            ->count(50)
            ->create([
                'survey_template_id' => $template->id,
            ]);
    }
}
