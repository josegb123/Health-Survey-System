<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Fillable('theme', 'language', 'turnstile_site_key', 'turnstile_secret_key', 'rate_limit_requests', 'company_name', 'company_dni', 'entity_type', 'registry_type', 'mail_from_address', 'mail_from_name', 'session_timeout_minutes', 'is_maintenance_mode', 'survey_monthly_goal', 'surveys_purge_last_run', 'default_survey_template_id')]
class SystemSetting extends Model
{
    const CACHE_KEY = 'global_system_settings';

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    public static function purgeOldSurveys(): string
    {
        $cutoff = now()->subMonths(6);
        $settings = self::set();

        try {
            return DB::transaction(function () use ($cutoff, $settings) {
                $surveys = Survey::where('status', 'completed')
                    ->where('created_at', '<', $cutoff)
                    ->get();

                $count = $surveys->count();
                if ($count === 0) {
                    return __('No surveys older than 6 months were found for deletion.');
                }

                $surveyIds = $surveys->pluck('id');

                foreach ($surveys as $survey) {
                    if ($survey->signature_path && Storage::disk('local')->exists($survey->signature_path)) {
                        Storage::disk('local')->delete($survey->signature_path);
                    }
                }

                SurveyAnswer::whereIn('survey_id', $surveyIds)->forceDelete();

                $patientIds = $surveys->pluck('patient_id')->unique();

                Survey::whereIn('id', $surveyIds)->forceDelete();

                foreach ($patientIds as $patientId) {
                    $remaining = Survey::withTrashed()->where('patient_id', $patientId)->count();
                    if ($remaining === 0) {
                        $patient = Patient::withTrashed()->find($patientId);
                        if ($patient) {
                            $patient->forceDelete();
                        }
                    }
                }

                $settings->update(['surveys_purge_last_run' => now()]);

                Cache::forget(self::CACHE_KEY);

                Log::info("Purged {$count} old surveys and their relations.");

                return __(':count surveys older than 6 months have been permanently deleted, along with their associated responses, signatures and orphan patients.', ['count' => $count]);
            });
        } catch (\Exception $e) {
            Log::error('Survey purge failed: '.$e->getMessage());

            return __('An error occurred while trying to purge old surveys: :error', ['error' => $e->getMessage()]);
        }
    }

    public function defaultTemplate(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'default_survey_template_id');
    }

    public static function set(): self
    {
        $data = Cache::rememberForever(self::CACHE_KEY, function () {
            $setting = self::firstOrCreate(['id' => 1]);

            return $setting->toArray();
        });

        $instance = new self;
        $instance->forceFill($data);
        $instance->exists = true;

        return $instance;
    }
}
