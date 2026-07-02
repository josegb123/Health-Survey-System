<?php

namespace Database\Seeders;

use App\Models\SurveyTemplate;
use App\Services\SurveyReportService;
use Illuminate\Database\Seeder;

class SurveyTemplateSeeder extends Seeder
{
    public function run(): void
    {
        SurveyTemplate::firstOrCreate(
            ['title' => SurveyReportService::MINISTRY_TEMPLATE_TITLE],
            ['is_active' => true],
        );

        SurveyTemplate::factory()->count(12)->create();
    }
}
