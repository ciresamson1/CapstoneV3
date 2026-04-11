<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentReactionController;
use App\Http\Controllers\TaskCommentController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    // Role-based dashboard redirect
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    // PM dedicated dashboard
    Route::get('/pm/dashboard', [DashboardController::class, 'index'])->middleware('role:pm')->name('pm.dashboard');

    // DM dedicated dashboard
    Route::get('/dm/dashboard', [DashboardController::class, 'dmIndex'])->middleware('role:dm')->name('dm.dashboard');

    // Client dedicated dashboard
    Route::get('/client/dashboard', [DashboardController::class, 'clientIndex'])->middleware('role:client')->name('client.dashboard');

    // Client projects (filtered to client_id)
    Route::get('/client/projects', [ProjectController::class, 'clientIndex'])->middleware('role:client')->name('client.projects');

    // Client single project view
    Route::get('/client/projects/{project}', [ProjectController::class, 'show'])->middleware('role:client')->name('client.projects.show');

    // Client tasks (tasks in client's assigned projects)
    Route::get('/client/tasks', [TaskController::class, 'clientIndex'])->middleware('role:client')->name('client.tasks.index');

    // DM projects (filtered to DM's assigned tasks' projects)
    Route::get('/dm/projects', [ProjectController::class, 'dmIndex'])->middleware('role:dm')->name('dm.projects');

    // DM single project view
    Route::get('/dm/projects/{project}', [ProjectController::class, 'show'])->middleware('role:dm')->name('dm.projects.show');

    // DM tasks (filtered to DM's assigned tasks)
    Route::get('/dm/tasks', [TaskController::class, 'dmIndex'])->middleware('role:dm')->name('dm.tasks.index');

    // DM report (client cards scoped to DM's projects)
    Route::get('/dm/report', [ReportController::class, 'dmReport'])->middleware('role:dm')->name('dm.report.index');

    // PM projects (filtered to PM's own projects)
    Route::get('/pm/projects', [ProjectController::class, 'pmIndex'])->middleware('role:pm')->name('pm.projects');

    // PM tasks (filtered to PM's own projects)
    Route::get('/pm/tasks', [TaskController::class, 'pmIndex'])->middleware('role:pm')->name('pm.tasks.index');

    // PM activity log (filtered to PM's own projects)
    Route::get('/pm/activity-log', [ActivityLogController::class, 'pmIndex'])->middleware('role:pm')->name('pm.activity-log.index');

    // PM report (DMs + clients scoped to PM's projects)
    Route::get('/pm/report', [ReportController::class, 'pmReport'])->middleware('role:pm')->name('pm.report.index');

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::get('/projects/clients/search', [ProjectController::class, 'searchClients'])->name('projects.clients.search');
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

    Route::put('/tasks/{id}',
        [TaskController::class, 'update']
    )->name('tasks.update');

    Route::get('/projects/{project}/tasks/{task}/card',
        [TaskController::class, 'taskCard']
    )->name('tasks.card');

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

    Route::post('/task-comments/{comment}/react',
        [CommentReactionController::class, 'toggle']
    )->name('task-comments.react');

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
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

        // Shared routes: accessible by admin, pm, dm and client roles (for PDF)
    Route::prefix('admin')->middleware(['auth', 'role:admin,pm,dm,client'])->name('admin.')->group(function () {
        Route::get('/tasks', function () {
            if (auth()->user()->role === 'pm') {
                return redirect()->route('pm.tasks.index');
            }
            if (auth()->user()->role === 'dm') {
                return redirect()->route('dm.tasks.index');
            }
            if (auth()->user()->role === 'client') {
                return redirect()->route('client.tasks.index');
            }
            return app(\App\Http\Controllers\TaskController::class)->index();
        })->name('tasks.index');
        Route::get('/activity-log', function (Illuminate\Http\Request $request) {
            if (auth()->user()->role === 'pm') {
                return redirect()->route('pm.activity-log.index', $request->query());
            }
            return app(\App\Http\Controllers\ActivityLogController::class)->index($request);
        })->name('activity-log.index');
        Route::get('/report', function () {
            if (auth()->user()->role === 'pm') {
                return redirect()->route('pm.report.index');
            }
            if (auth()->user()->role === 'dm') {
                return redirect()->route('dm.report.index');
            }
            return app(\App\Http\Controllers\ReportController::class)->index();
        })->name('report.index');
        Route::get('/report/pdf/{userId}', [ReportController::class, 'pdf'])->name('report.pdf');
    });

});

require __DIR__.'/auth.php';