<?php

use App\Http\Controllers\Api\PublicSurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/survey-templates/{id}', [PublicSurveyController::class, 'show']);
Route::post('/surveys/{templateId}/submit', [PublicSurveyController::class, 'store']);
