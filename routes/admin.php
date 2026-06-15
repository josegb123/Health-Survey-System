<?php

use App\Livewire\Admin\SystemSettings;
use App\Livewire\Admin\SurveyTemplateIndex;
use App\Livewire\User\Index;
use Illuminate\Support\Facades\Route;

// Sub-módulo de Configuración y Plantillas Administrativas
Route::prefix('admin')->name('admin.')->group(function () {

    // Gestión de Plantillas de Encuestas
    Route::get('/survey-templates', SurveyTemplateIndex::class)->name('survey-templates.index');

    // Configuración Global de la fila única del sistema
    Route::get('/settings', SystemSettings::class)->name('settings');

});

Route::prefix('users')->name('users.')->group(function () {

    // Listado e índice de usuarios del sistema
    Route::get('/index', Index::class)->name('index'); // Genera la ruta: users.index

});
