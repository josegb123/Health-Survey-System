<?php

use App\Livewire\Admin\MinistrySettings;
use App\Livewire\Admin\SurveyIndex;
use App\Livewire\Admin\SurveyTemplateCreate;
use App\Livewire\Admin\SurveyTemplateIndex;
use App\Livewire\Admin\SystemSettings;
use App\Livewire\User\Index;
use Illuminate\Support\Facades\Route;

// Sub-módulo de Administración
Route::prefix('admin')->name('admin.')->group(function () {

    // Listado de Encuestas Completadas
    Route::get('/surveys', SurveyIndex::class)->name('surveys.index');

    // Gestión de Plantillas de Encuestas
    Route::get('/survey-templates', SurveyTemplateIndex::class)->name('survey-templates.index');
    Route::get('/survey-templates/create', SurveyTemplateCreate::class)->name('survey-templates.create');
    // Route::get('/survey-templates/{templateId}/edit', SurveyTemplateCreate::class)->name('survey-templates.edit');

    // Configuración Global de la fila única del sistema
    Route::get('/settings', SystemSettings::class)->name('settings');

    // Configuración del reporte de Ministerio
    Route::get('/ministry-settings', MinistrySettings::class)->name('ministry-settings');

});

Route::prefix('users')->name('users.')->group(function () {

    // Listado e índice de usuarios del sistema
    Route::get('/index', Index::class)->name('index'); // Genera la ruta: users.index

});
