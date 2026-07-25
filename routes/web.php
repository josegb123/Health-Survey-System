<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Acceso Público / Autenticación
Route::view('/', 'livewire.auth.login')->name('home');

// Grupo Protegido Global
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard Principal Común
    Route::get('admin/dashboard', Dashboard::class)->name('dashboard');

    // Módulo de Administración
    require __DIR__ . '/admin.php';

    // Ajustes del sistema
    require __DIR__ . '/settings.php';

    // Servir firma digital privada (solo usuarios autenticados)
    Route::get('/survey/{survey}/signature', function (Survey $survey) {
        if (!$survey->signature_path || !Storage::disk('local')->exists($survey->signature_path)) {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk->response($survey->signature_path);
    })->name('surveys.signature');
});

// Ruta Fallback para redirección inteligente
Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});
