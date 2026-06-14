<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('name', 'type', 'is_active')]
class Insurer extends Model
{
    /** @use HasFactory<\Database\Factories\InsurerFactory> */
    use HasFactory, SoftDeletes;

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
