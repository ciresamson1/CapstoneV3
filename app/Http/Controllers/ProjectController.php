<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['tasks', 'creator'])
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function pmIndex()
    {
        $projects = Project::with(['tasks', 'creator'])
            ->withCount('tasks')
            ->where('created_by', auth()->id())
            ->orderBy('name')
            ->get();

        return view('pm.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store()
    {
        request()->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        Project::create([
            'name'        => request('name'),
            'description' => request('description'),
            'start_date'  => request('start_date'),
            'end_date'    => request('end_date'),
            'status'      => request('status', 'active'),
            'created_by'  => auth()->id(),
        ]);

        ActivityLog::record('created_project', 'Created project "' . request('name') . '"');

        $redirect = auth()->user()->role === 'pm' ? 'pm.projects' : 'projects.index';
        return redirect()->route($redirect)->with('status', 'Project created successfully.');
    }

    public function show($id)
    {
        $project = Project::with([
            'tasks.comments.user',
            'tasks.comments.reactions',
            'tasks.assignedTo',
        ])->findOrFail($id);

        $users = User::orderBy('name')->get();

        return view('projects.show', compact('project', 'users'));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return redirect()->route('projects.index')->with('editProject', $project->id);
    }

    public function update($id)
    {
        $project = Project::findOrFail($id);

        request()->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'status'      => 'required|in:active,on_hold,completed',
        ]);

        $project->update([
            'name'        => request('name'),
            'description' => request('description'),
            'start_date'  => request('start_date'),
            'end_date'    => request('end_date'),
            'status'      => request('status'),
        ]);

        $redirect = auth()->user()->role === 'pm' ? 'pm.projects' : 'projects.index';
        return redirect()->route($redirect)->with('status', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        $redirect = auth()->user()->role === 'pm' ? 'pm.projects' : 'projects.index';
        return redirect()->route($redirect)->with('status', 'Project deleted.');
    }
}