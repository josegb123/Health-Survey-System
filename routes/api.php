<?php

use App\Http\Controllers\Api\PublicConfigController;
use App\Http\Controllers\Api\PublicInsurerController;
use App\Http\Controllers\Api\PublicSurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/config', PublicConfigController::class);

Route::get('/survey-templates/{id}', [PublicSurveyController::class, 'show']);
Route::post('/surveys/{templateId}/submit', [PublicSurveyController::class, 'store']);

Route::get('/insurers', [PublicInsurerController::class, 'index']);
