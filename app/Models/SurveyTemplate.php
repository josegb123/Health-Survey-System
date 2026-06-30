<?php

namespace App\Models;

use Database\Factories\SurveyTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'is_active'])]
class SurveyTemplate extends Model
{
    /** @use HasFactory<SurveyTemplateFactory> */
    use HasFactory, SoftDeletes;

    /**
     * (Summary of surveyQuestions)
     *
     * @return HasMany<SurveyQuestion, SurveyTemplate>
     */
    public function surveyQuestions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    /**
     * Summary of surveys
     *
     * @return HasMany<Survey, SurveyTemplate>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Summary of questions
     *
     * @return HasMany<SurveyQuestion, SurveyTemplate>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }
}
