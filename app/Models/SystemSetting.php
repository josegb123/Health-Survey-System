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

    public static function deleteAllSignatures(): string
    {
        try {
            $directory = Storage::disk('local')->path('signatures');

            if (! is_dir($directory)) {
                return __('The signatures directory does not exist.');
            }

            $files = glob($directory.'/*.png');

            if ($files === false) {
                return __('Could not read the signatures directory.');
            }

            $count = count($files);

            if ($count === 0) {
                return __('No signature files found to delete.');
            }

            foreach ($files as $file) {
                unlink($file);
            }

            Log::info("Deleted {$count} signature files from disk.");

            return __(':count signature file(s) have been permanently deleted.', ['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Signature cleanup failed: '.$e->getMessage());

            return __('An error occurred while deleting signatures: :error', ['error' => $e->getMessage()]);
        }
    }

    public static function resetDatabase(): string
    {
        try {
            return DB::transaction(function () {
                $surveys = Survey::withTrashed()->get();
                $signatureCount = 0;

                foreach ($surveys as $survey) {
                    if ($survey->signature_path && Storage::disk('local')->exists($survey->signature_path)) {
                        Storage::disk('local')->delete($survey->signature_path);
                        $signatureCount++;
                    }
                }

                $answerCount = SurveyAnswer::withTrashed()->forceDelete();
                $surveyCount = Survey::withTrashed()->forceDelete();
                $patientCount = Patient::withTrashed()->forceDelete();

                Cache::forget(self::CACHE_KEY);

                Log::info("Database reset: deleted {$surveyCount} surveys, {$answerCount} answers, {$patientCount} patients, {$signatureCount} signatures.");

                return __('Database has been reset: :surveys surveys, :answers answers, :patients patients, and :signatures signature files have been deleted.', [
                    'surveys' => $surveyCount,
                    'answers' => $answerCount,
                    'patients' => $patientCount,
                    'signatures' => $signatureCount,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Database reset failed: '.$e->getMessage());

            return __('An error occurred while resetting the database: :error', ['error' => $e->getMessage()]);
        }
    }

    public static function exportAllSettings(): array
    {
        $systemSettings = self::set()->toArray();
        $ministryConfig = MinistryReportConfig::set()->toArray();

        return [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'system_settings' => $systemSettings,
            'ministry_report_config' => $ministryConfig,
        ];
    }

    public static function importSettings(array $data): string
    {
        try {
            return DB::transaction(function () use ($data) {
                if (isset($data['system_settings'])) {
                    $settings = self::set();
                    $allowed = [
                        'theme', 'language', 'turnstile_site_key', 'turnstile_secret_key',
                        'rate_limit_requests', 'company_name', 'company_dni', 'entity_type',
                        'registry_type', 'mail_from_address', 'mail_from_name',
                        'session_timeout_minutes', 'is_maintenance_mode', 'survey_monthly_goal',
                        'default_survey_template_id',
                    ];
                    $filtered = array_intersect_key($data['system_settings'], array_flip($allowed));
                    $settings->update($filtered);
                }

                if (isset($data['ministry_report_config'])) {
                    $config = MinistryReportConfig::set();
                    $ministryAllowed = ['survey_template_id', 'pipe_mapping'];
                    $filtered = array_intersect_key($data['ministry_report_config'], array_flip($ministryAllowed));
                    $config->update($filtered);
                }

                Cache::forget(self::CACHE_KEY);

                Log::info('Settings imported successfully from JSON.');

                return __('Configuration has been imported and applied successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Settings import failed: '.$e->getMessage());

            return __('An error occurred while importing settings: :error', ['error' => $e->getMessage()]);
        }
    }
}
