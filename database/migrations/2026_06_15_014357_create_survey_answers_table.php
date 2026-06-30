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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();

            // Relación con la cabecera de la encuesta (Si se borra la encuesta, se borran las respuestas)
            $table->foreignId('survey_id')
                ->constrained('surveys')
                ->onDelete('cascade');

            // Relación con la pregunta específica (Se bloquea el borrado de la pregunta si ya tiene respuestas)
            $table->foreignId('survey_question_id')
                ->constrained('survey_questions')
                ->onDelete('restrict');

            // Almacenamos la respuesta como texto largo para soportar inputs tipo 'text' o JSON de respuestas múltiples
            $table->text('answer_value')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índice compuesto para acelerar reportes analíticos agregados
            $table->index(['survey_question_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
