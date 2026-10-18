<?php

/**
 * Fix timezone: convert UTC timestamps to America/Bogota for existing records.
 * Run once: php artisan tinker --include=fix-timezones-helper
 *
 * Usage: php fix-timezones.php
 */

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->handleCommand(new \Symfony\Component\Console\Input\ArgvInput(['argv0' => 'fix']));

use Illuminate\Support\Facades\DB;

$offset = 5;

echo "=== Fixing UTC -> Bogota timestamps ===\n\n";

// surveys table
$count = DB::table('surveys')->count();
echo "Surveys total: {$count}\n";

DB::statement('UPDATE surveys SET created_at = DATE_SUB(created_at, INTERVAL 5 HOUR), updated_at = DATE_SUB(updated_at, INTERVAL 5 HOUR)');
echo "  Updated created_at and updated_at\n";

DB::statement('UPDATE surveys SET completed_at = DATE_SUB(completed_at, INTERVAL 5 HOUR) WHERE completed_at IS NOT NULL');
echo "  Updated completed_at\n";

// Check for other timestamp columns in other tables that might need fixing
$tables = ['survey_answers', 'patients', 'insurers', 'survey_templates'];
foreach ($tables as $table) {
    if (DB::getSchemaBuilder()->hasTable($table)) {
        $cols = DB::getSchemaBuilder()->getColumnListing($table);
        $timestampCols = array_filter($cols, fn ($c) => in_array($c, ['created_at', 'updated_at', 'completed_at', 'deleted_at']));
        if (! empty($timestampCols)) {
            $tableCount = DB::table($table)->count();
            echo "\n{$table} total: {$tableCount}\n";
            foreach ($timestampCols as $col) {
                DB::statement("UPDATE {$table} SET {$col} = DATE_SUB({$col}, INTERVAL 5 HOUR) WHERE {$col} IS NOT NULL");
                echo "  Updated {$table}.{$col}\n";
            }
        }
    }
}

echo "\n=== Done ===\n";
