<?php

namespace App\Models;

use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('survey_template_id', 'question_text', 'field_type', 'options', 'order', 'is_required')]
class SurveyQuestion extends Model
{
    /** @use HasFactory<SurveyQuestionFactory> */
    use HasFactory;

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Una pregunta pertenece a una plantilla.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    /**
     * Summary of answers
     *
     * @return HasMany<SurveyAnswer, SurveyQuestion>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
