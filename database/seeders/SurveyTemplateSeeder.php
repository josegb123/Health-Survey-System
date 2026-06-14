<?php

namespace Database\Seeders;


use App\Models\SurveyTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SurveyTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SurveyTemplate::factory()->count(12)->create();
    }
}
