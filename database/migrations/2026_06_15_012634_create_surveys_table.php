<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            // Llaves foráneas estructuradas
            $table->foreignId('survey_template_id')->constrained('survey_templates')->onDelete('restrict');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('restrict');
            $table->string('signature_path')->nullable();

            // Metadatos de control y estado
            $table->string('status')->default('completed'); // 'draft', 'completed'
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices de optimización para búsquedas por rango y purga automatizada
            $table->index(['deleted_at', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
