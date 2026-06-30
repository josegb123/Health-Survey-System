<?php

namespace App\Models;

use Database\Factories\SurveyAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('survey_id', 'survey_question_id', 'answer_value')]
class SurveyAnswer extends Model
{
    /** @use HasFactory<SurveyAnswerFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Una respuesta pertenece a un intento de encuesta específico.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Una respuesta mapea a una pregunta específica.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
