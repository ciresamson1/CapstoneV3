<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubTaskController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('projects', ProjectController::class);

    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');

    Route::post('/tasks/{id}/update-dates', [TaskController::class, 'updateDates'])->name('tasks.update-dates');
    Route::post('/tasks/{id}/toggle', [\App\Http\Controllers\TaskController::class, 'toggle']);
    Route::post('/subtasks/{id}/toggle', [SubTaskController::class, 'toggle'])->name('subtasks.toggle');
});

require __DIR__.'/auth.php';