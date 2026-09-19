<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/matematyka/tabliczka', fn () => Inertia::render('Math/Multiplication'))->name('math.multiplication');

Route::get('/angielski/alfabet', fn () => Inertia::render('English/Alphabet'))->name('english.alphabet');
Route::get('/angielski/czasowniki-nieregularne', fn () => Inertia::render('English/IrregularVerbs'))->name('english.irregular-verbs');
Route::get('/angielski/conditionals', fn () => Inertia::render('English/Conditionals'))->name('english.conditionals');
Route::get('/angielski/wishes', fn () => Inertia::render('English/Wishes'))->name('english.wishes');
