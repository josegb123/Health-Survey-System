<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->foreignId('default_survey_template_id')
                ->nullable()
                ->constrained('survey_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropForeign(['default_survey_template_id']);
            $table->dropColumn('default_survey_template_id');
        });
    }
};
