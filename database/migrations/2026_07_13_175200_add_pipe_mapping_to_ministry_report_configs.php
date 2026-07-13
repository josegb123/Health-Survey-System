<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministry_report_configs', function (Blueprint $table) {
            $table->json('pipe_mapping')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ministry_report_configs', function (Blueprint $table) {
            $table->dropColumn('pipe_mapping');
        });
    }
};
