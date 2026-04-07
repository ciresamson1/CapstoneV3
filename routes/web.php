<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications/read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    });

    Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/metrics', [AdminDashboardController::class, 'metrics'])->name('dashboard.metrics');
        Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'chartData'])->name('dashboard.chart-data');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/invite', [AdminUserController::class, 'invite'])->name('users.invite');
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    });

});

require __DIR__.'/auth.php';