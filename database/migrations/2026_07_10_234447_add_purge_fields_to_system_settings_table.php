<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->timestamp('surveys_purge_last_run')->nullable()->after('survey_monthly_goal');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn('surveys_purge_last_run');
        });
    }
};
