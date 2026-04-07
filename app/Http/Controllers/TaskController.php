<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ActivityLog;
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

        Task::create([
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
        ActivityLog::record('created_task', 'Created task "' . $request->title . '" in project "' . ($project?->name ?? 'Unknown') . '"');

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

        $task->progress = $task->progress == 100 ? 0 : 100;
        $task->status   = $task->progress == 100 ? 'completed' : 'pending';
        $task->save();

        $label = $task->progress == 100 ? 'completed' : 'reopened';
        ActivityLog::record('updated_task', 'Marked task "' . $task->title . '" as ' . $label);

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

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to ?: null,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'progress'    => $request->status === 'completed' ? 100 : $request->progress,
            'status'      => $request->status,
        ]);

        ActivityLog::record('updated_task', 'Updated task "' . $task->title . '"');

        return redirect()->back()->with('status', 'Task updated.');
    }

}