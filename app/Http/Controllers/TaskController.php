<?php

/**
 * TaskController
 *
 * Handles all CRUD operations for Tasks, scoped appropriately for each
 * user role. This is the most central controller in the application.
 *
 * ─── Methods & Routes ──────────────────────────────────────────────────
 *  index()       GET /admin/tasks              admin task list
 *  clientIndex() GET /client/tasks             tasks in client's projects
 *  dmIndex()     GET /dm/tasks                 tasks assigned to current DM
 *  pmIndex()     GET /pm/tasks                 tasks in PM's own projects
 *  create()      GET /projects/{id}/tasks/create   form (rarely used; AJAX preferred)
 *  store()       POST /projects/{id}/tasks     create task + broadcast 'created'
 *  updateDates() PATCH /tasks/{id}/dates       Gantt drag-and-drop date update
 *  toggle()      POST /tasks/{id}/toggle       flip progress 0 ⇄ 100 + broadcast 'toggled'
 *  update()      PUT /tasks/{id}               full update + broadcast 'updated'
 *  taskCard()    GET /projects/{p}/tasks/{t}/card  returns '_task-card' partial HTML
 *
 * ─── Real-time Broadcasting ────────────────────────────────────────────
 *  store(), toggle(), and update() all broadcast a TaskChanged event
 *  on the public Laravel Reverb channel 'project.{project_id}' via
 *  broadcast()->toOthers() so the sender's own tab is NOT updated twice.
 *
 *  The front-end (projects/show.blade.php) listens on that channel and
 *  calls fetchAndReplaceTask() or fetchAndInjectTask() to request the
 *  updated card HTML from the taskCard() endpoint.
 *
 * ─── AJAX Responses ──────────────────────────────────────────────────
 *  store() and update() return JSON when the request carries:
 *    - Accept: application/json   (wantsJson)
 *    - X-Requested-With: XMLHttpRequest
 *
 * ─── Side Effects ────────────────────────────────────────────────────
 *  Every mutation also writes to:
 *    ActivityLog  — human-readable action description
 *    ProgressLog  — appended only when progress value actually changes
 *
 * @see \App\Events\TaskChanged
 * @see \App\Models\Task
 * @see \App\Models\ActivityLog
 * @see \App\Models\ProgressLog
 */

namespace App\Http\Controllers;

use App\Events\TaskChanged;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\ProgressLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Admin task list — all tasks across all projects, sorted by due date.
     * View: resources/views/admin/tasks.blade.php
     */
    public function index()
    {
        $tasks = Task::with(['project', 'assignedTo'])
            ->orderBy('end_date')
            ->get();

        $projects = Project::orderBy('name')->pluck('name', 'id');
        $users    = User::orderBy('name')->pluck('name', 'id');

        return view('admin.tasks', compact('tasks', 'projects', 'users'));
    }

    /**
     * Client task list — scoped to projects where client_id = current user.
     * View: resources/views/client/tasks.blade.php
     */
    public function clientIndex()
    {
        $myProjectIds = Project::where('client_id', auth()->id())->pluck('id');

        $tasks = Task::with(['project', 'assignedTo'])
            ->whereIn('project_id', $myProjectIds)
            ->orderBy('end_date')
            ->get();

        $projects = Project::whereIn('id', $myProjectIds)->orderBy('name')->pluck('name', 'id');
        $users    = User::orderBy('name')->pluck('name', 'id');

        return view('client.tasks', compact('tasks', 'projects', 'users'));
    }

    /**
     * DM task list — only tasks where assigned_to = current user.
     * View: resources/views/dm/tasks.blade.php
     */
    public function dmIndex()
    {
        $tasks = Task::with(['project', 'assignedTo'])
            ->where('assigned_to', auth()->id())
            ->orderBy('end_date')
            ->get();

        $myProjectIds = $tasks->pluck('project_id')->unique();
        $projects = Project::whereIn('id', $myProjectIds)->orderBy('name')->pluck('name', 'id');
        $users    = User::orderBy('name')->pluck('name', 'id');

        return view('dm.tasks', compact('tasks', 'projects', 'users'));
    }

    /**
     * PM task list — tasks in projects created by the current PM.
     * View: resources/views/pm/tasks.blade.php
     */
    public function pmIndex()
    {
        $myProjectIds = Project::where('created_by', auth()->id())->pluck('id');

        $tasks = Task::with(['project', 'assignedTo'])
            ->whereIn('project_id', $myProjectIds)
            ->orderBy('end_date')
            ->get();

        $projects = Project::whereIn('id', $myProjectIds)->orderBy('name')->pluck('name', 'id');
        $users    = User::orderBy('name')->pluck('name', 'id');

        return view('pm.tasks', compact('tasks', 'projects', 'users'));
    }

    /**
     * Show the create-task form (traditional full-page form).
     * Note: the project's show page uses an inline AJAX modal instead;
     * this route is kept as a fallback.
     */
    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $users = User::all();

        return view('tasks.create', compact('project', 'users'));
    }

    /**
     * Create and persist a new task, then broadcast the creation to all
     * other users viewing the same project.
     *
     * Validates: title (required), start_date, end_date (must be >= start).
     * Writes an ActivityLog entry.
     * Returns JSON for AJAX callers, or redirects for traditional POST.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $projectId
     */
    public function store(Request $request, $projectId)
    {
        $request->validate([
            'title'      => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $task = Task::create([
            'project_id'  => $projectId,
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => $request->input('status', 'pending'),
            'progress'    => $request->input('status') === 'completed' ? 100 : ($request->input('status') === 'in_progress' ? 50 : 0),
        ]);

        $project = Project::find($projectId);
        ActivityLog::record('created_task', 'Created task "' . $request->title . '" in project "' . ($project?->name ?? 'Unknown') . '"', $task);

        try {
            broadcast(new TaskChanged($task, 'created'))->toOthers();
        } catch (\Throwable $e) {}

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['status' => 'created', 'task_id' => $task->id]);
        }

        return redirect()->route('projects.show', $projectId)->with('task_created', 'Task created successfully.');
    }

    /**
     * Update only the start/end dates (used by Gantt drag-and-drop).
     * Does NOT broadcast a TaskChanged event — Gantt handles its own UI.
     */
    public function updateDates(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $task->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json(['status' => 'updated']);
    }

    /**
     * Toggle a task's completion state (progress 0 ⇄ 100).
     *
     * When toggled to 100 → status becomes 'completed'.
     * When toggled back to 0  → status becomes 'pending'.
     *
     * Records a ProgressLog entry and an ActivityLog entry.
     * Broadcasts a TaskChanged event with change_type = 'toggled'.
     * Returns JSON { status, progress } always.
     *
     * @param  int  $id  Task primary key
     */
    public function toggle($id)
    {
        $task = \App\Models\Task::findOrFail($id);

        $oldProgress    = $task->progress;
        $task->progress = $task->progress == 100 ? 0 : 100;
        $task->status   = $task->progress == 100 ? 'completed' : 'pending';
        $task->save();

        ProgressLog::create([
            'type'         => 'task',
            'reference_id' => $task->id,
            'old_progress' => $oldProgress,
            'new_progress' => $task->progress,
            'updated_by'   => auth()->id(),
        ]);

        $label = $task->progress == 100 ? 'completed' : 'reopened';
        ActivityLog::record('updated_task', 'Marked task "' . $task->title . '" as ' . $label, $task);

        try {
            broadcast(new TaskChanged($task, 'toggled'))->toOthers();
        } catch (\Throwable $e) {}

        return response()->json(['status' => 'ok', 'progress' => $task->progress]);
    }

    /**
     * Update all task fields (called from the Edit modal on the project page).
     *
     * Validates all fields. If status is set to 'completed', forces progress = 100.
     * Records a ProgressLog entry only when progress actually changes.
     * Broadcasts TaskChanged with change_type = 'updated'.
     * Returns JSON for AJAX callers, or redirects with flash message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Task primary key
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'title'      => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'progress'   => 'required|integer|min:0|max:100',
            'status'     => 'required|in:pending,in_progress,completed',
        ]);

        $oldProgress = $task->progress;

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to ?: null,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'progress'    => $request->status === 'completed' ? 100 : $request->progress,
            'status'      => $request->status,
        ]);

        if ($task->progress !== $oldProgress) {
            ProgressLog::create([
                'type'         => 'task',
                'reference_id' => $task->id,
                'old_progress' => $oldProgress,
                'new_progress' => $task->progress,
                'updated_by'   => auth()->id(),
            ]);
        }

        ActivityLog::record('updated_task', 'Updated task "' . $task->title . '"', $task);

        try {
            broadcast(new TaskChanged($task->fresh('assignedTo'), 'updated'))->toOthers();
        } catch (\Throwable $e) {}

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['status' => 'updated', 'task_id' => $task->id]);
        }

        return redirect()->back()->with('status', 'Task updated.');
    }

    /**
     * Return the rendered HTML for a single task card partial.
     *
     * Used by the front-end's fetchAndReplaceTask() and fetchAndInjectTask()
     * helpers after receiving a real-time TaskChanged WebSocket event.
     * Eager-loads all comment data (replies + reactions) in one query.
     *
     * Returns 404 if the task does not belong to the given project.
     * View: resources/views/projects/_task-card.blade.php
     *
     * @param  int  $projectId
     * @param  int  $taskId
     */
    public function taskCard($projectId, $taskId)
    {
        $task = Task::with([
            'assignedTo',
            'comments' => function ($q) {
                $q->with(['user', 'reactions', 'replies.user', 'replies.reactions']);
            },
        ])->findOrFail($taskId);

        if ((int) $task->project_id !== (int) $projectId) {
            abort(404);
        }

        return view('projects._task-card', compact('task'));
    }
}