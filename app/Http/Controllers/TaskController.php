<?php

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
    public function index()
    {
        $tasks = Task::with(['project', 'assignedTo'])
            ->orderBy('end_date')
            ->get();

        $projects = Project::orderBy('name')->pluck('name', 'id');
        $users    = User::orderBy('name')->pluck('name', 'id');

        return view('admin.tasks', compact('tasks', 'projects', 'users'));
    }

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

    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $users = User::all();

        return view('tasks.create', compact('project', 'users'));
    }

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

    public function updateDates(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $task->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json(['status' => 'updated']);
    }

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