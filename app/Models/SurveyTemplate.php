<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'is_active'])]
class SurveyTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyTemplateFactory> */
    use HasFactory, SoftDeletes;

}
