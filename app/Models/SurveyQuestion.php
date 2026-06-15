<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable('survey_template_id', 'question_text', 'field_type', 'order')]
class SurveyQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyQuestionFactory> */
    use HasFactory;

    /**
     * Una pregunta pertenece a una plantilla.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    /**
     * Summary of answer
     * @return HasOne<SurveyAnswer, SurveyQuestion>
     */
    public function answer(): HasOne
    {
        return $this->hasOne(SurveyAnswer::class);
    }
}
