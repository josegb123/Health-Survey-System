<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('name', 'dni', 'dni', 'name', 'email', 'nationality', 'address', 'phone', 'insurer_id')]
class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Relación inversa: Un paciente pertenece a una aseguradora (EPS).
     */
    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class);
    }
}
