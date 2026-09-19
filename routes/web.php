<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/matematyka/tabliczka', fn () => Inertia::render('Math/Multiplication'))->name('math.multiplication');

Route::get('/angielski/alfabet', fn () => Inertia::render('English/Alphabet'))->name('english.alphabet');
