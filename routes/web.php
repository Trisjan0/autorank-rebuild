<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;

// --- GOOGLE AUTHENTICATION ROUTES ---
// These routes handle the OAuth flow for Google login.
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');


// --- HOMEPAGE REDIRECT TO INSTRUCTOR PANEL ---
Route::get('/', function () {
    return redirect()->route('filament.instructor.auth.login');
});
