<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/matematyka/tabliczka', fn () => Inertia::render('Math/Multiplication'))->name('math.multiplication');

Route::get('/angielski/alfabet', fn () => Inertia::render('English/Alphabet'))->name('english.alphabet');
Route::get('/angielski/czasowniki-nieregularne', fn () => Inertia::render('English/IrregularVerbs'))->name('english.irregular-verbs');
Route::get('/angielski/conditionals', fn () => Inertia::render('English/Conditionals'))->name('english.conditionals');
Route::get('/angielski/wishes', fn () => Inertia::render('English/Wishes'))->name('english.wishes');

Route::get('/prywatnosc', fn () => Inertia::render('Legal/PrivacyPolicy'))->name('privacy');

Route::middleware('guest')->group(function () {
    Route::get('/rejestracja', [AuthController::class, 'create'])->name('register');
    Route::post('/rejestracja', [AuthController::class, 'store']);
    Route::get('/logowanie', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/logowanie', [AuthController::class, 'login']);

    Route::get('/zapomnialem-hasla', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/zapomnialem-hasla', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-hasla/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-hasla', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/wyloguj', [AuthController::class, 'destroy'])->name('logout');
    Route::post('/attempts', [AttemptController::class, 'store'])->middleware('throttle:60,1')->name('attempts.store');
});
