<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\CommentController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::resource('projects', ProjectController::class);

    /*
    |--------------------------------------------------------------------------
    | Tasks
    |--------------------------------------------------------------------------
    */

    Route::get('/projects/{project}/tasks/create',
        [TaskController::class, 'create']
    )->name('tasks.create');

    Route::post('/projects/{project}/tasks',
        [TaskController::class, 'store']
    )->name('tasks.store');

    Route::post('/tasks/{id}/toggle',
        [TaskController::class, 'toggle']
    );

    /*
    |--------------------------------------------------------------------------
    | Task Comments
    |--------------------------------------------------------------------------
    */

    Route::post('/tasks/{task}/comments',
        [TaskCommentController::class, 'store']
    )->name('tasks.comments.store');

    Route::get('/projects/{project}/comments/poll',
        [TaskCommentController::class, 'poll']
    )->name('projects.comments.poll');

    Route::get('/task-comments/{comment}/download',
        [TaskCommentController::class, 'download']
    )->name('task-comments.download');

    /*
    |--------------------------------------------------------------------------
    | General Comments (old system)
    |--------------------------------------------------------------------------
    */

    Route::post('/comments',
        [CommentController::class, 'store']
    )->name('comments.store');

    Route::get('/comments/{id}/download',
        [CommentController::class,'download']
    )->name('comments.download');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications/read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    });

});

require __DIR__.'/auth.php';