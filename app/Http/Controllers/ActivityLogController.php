<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs  = $query->get();
        $users = User::orderBy('name')->pluck('name', 'id');

        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.activity-log', compact('logs', 'users', 'actions'));
    }

    public function pmIndex(Request $request)
    {
        // Active (non-completed) projects owned by this PM
        $myProjectIds = Project::where('created_by', auth()->id())
            ->where('status', '!=', 'completed')
            ->pluck('id');

        $myTaskIds = Task::whereIn('project_id', $myProjectIds)->pluck('id');

        $taskClass   = \App\Models\Task::class;
        $projectClass = \App\Models\Project::class;

        $query = ActivityLog::with('user')
            ->where(function ($q) use ($myTaskIds, $myProjectIds, $taskClass, $projectClass) {
                // Logs tied to a task in the PM's active projects (created_task, updated_task, posted_comment)
                $q->where(function ($inner) use ($myTaskIds, $taskClass) {
                    $inner->where('subject_type', $taskClass)
                          ->whereIn('subject_id', $myTaskIds);
                })
                // Logs tied to a project created by this PM (created_project)
                ->orWhere(function ($inner) use ($myProjectIds, $projectClass) {
                    $inner->where('subject_type', $projectClass)
                          ->whereIn('subject_id', $myProjectIds);
                })
                // Fallback: old logs with no subject recorded by the PM themselves
                ->orWhere(function ($inner) {
                    $inner->whereNull('subject_type')
                          ->where('user_id', auth()->id());
                });
            })
            ->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        // Build user list from the matched logs for the filter dropdown
        $userIds = $logs->pluck('user_id')->unique();
        $users   = User::whereIn('id', $userIds)->orderBy('name')->pluck('name', 'id');

        $actions = $logs->pluck('action')->unique()->sort()->values();

        return view('pm.activity-log', compact('logs', 'users', 'actions'));
    }
}
