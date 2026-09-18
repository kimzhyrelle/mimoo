<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/register', function () {
    return Inertia::render('Register');
});

Route::get('/homepage', fn () => Inertia::render('Homepage'))->name('homepage');

Route::get('/product/{id}', fn (string $id) => Inertia::render('ProductDetail', [
    'productId' => $id,
]))->name('product.show');

require __DIR__.'/settings.php';