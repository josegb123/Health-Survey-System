<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('name', 'document_type', 'dni', 'email', 'nationality', 'address', 'phone', 'insurer_id')]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Retorna los tipos de documento soportados por el sistema y sus traducciones.
     */
    public static function documentTypes(): array
    {
        return [
            'CC' => __('Citizenship ID'),
            'CE' => __('Foreigner ID'),
            'PA' => __('Passport'),
            'TI' => __('Identity Card'),
        ];
    }

    /**
     * Relación inversa: Un paciente pertenece a una aseguradora (EPS).
     */
    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class);
    }

    /**
     * Summary of surveys
     *
     * @return HasMany<Survey, Patient>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
