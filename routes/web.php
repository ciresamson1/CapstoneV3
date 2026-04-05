<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return 'Admin Only';
    });
});

Route::middleware(['auth', 'role:pm'])->group(function () {
    Route::get('/pm', function () {
        return 'Project Manager Only';
    });
});

Route::middleware(['auth', 'role:dm'])->group(function () {
    Route::get('/dm', function () {
        return 'Digital Marketer Only';
    });
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client', function () {
        return 'Client Only';
    });
});

require __DIR__.'/auth.php';