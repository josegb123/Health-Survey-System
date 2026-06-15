<?php

use App\Livewire\Admin\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
});

// Ruta Fallback para redirección inteligente
Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
