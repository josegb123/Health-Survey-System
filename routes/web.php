<?php

use App\Livewire\User\Index;
use Illuminate\Support\Facades\Route;

Route::view('/', 'livewire.auth.login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('users/index', Index::class)->name('users.index');
});

require __DIR__ . '/settings.php';

// fallback route for smart redirection
Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Cambia 'dashboard' por el nombre de tu ruta del panel
    }
    return redirect()->route('login');
});
