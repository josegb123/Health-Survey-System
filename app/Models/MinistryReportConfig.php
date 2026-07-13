<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinistryReportConfig extends Model
{
    protected $fillable = ['survey_template_id', 'pipe_mapping'];

    protected function casts(): array
    {
        return [
            'pipe_mapping' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public static function set(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }
}
