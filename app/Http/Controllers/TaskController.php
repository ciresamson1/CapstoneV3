<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $users = User::all();

        return view('tasks.create', compact('project', 'users'));
    }

    public function store(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        Task::create([
            'project_id' => $projectId,
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('projects.show', $projectId);
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
        $task->save();

        return response()->json(['status' => 'ok']);
    }

}