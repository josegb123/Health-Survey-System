<?php

namespace App\Models;

use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('survey_template_id', 'patient_id', 'signature_path', 'status', 'rating', 'completed_at', )]
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mapeo de estados para la UI de forma agnóstica al idioma.
     */
    public static function statuses(): array
    {
        return [
            'draft' => __('Draft'),
            'completed' => __('Completed'),
        ];
    }

    public function signatureUrl(): ?string
    {
        if (! $this->signature_path) {
            return null;
        }

        return route('surveys.signature', $this);
    }

    /**
     * Casts automáticos de Eloquent para el manejo de fechas con Carbon.
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Una encuesta pertenece a una plantilla.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    /**
     * Una encuesta es respondida por un paciente.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Summary of answers
     *
     * @return HasMany<SurveyAnswer, Survey>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
