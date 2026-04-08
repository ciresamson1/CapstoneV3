<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ProjectController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'dm') {
            return redirect()->route('dm.projects');
        }

        if (auth()->user()->role === 'client') {
            return redirect()->route('client.projects');
        }

        $query = Project::with(['tasks', 'creator', 'client'])->withCount('tasks')->orderBy('name');

        if (auth()->user()->role === 'client') {
            $query->where('client_id', auth()->id());
        }

        $projects = $query->get();

        return view('projects.index', compact('projects'));
    }

    public function clientIndex()
    {
        $projects = Project::with(['tasks', 'creator', 'client'])
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed')])
            ->where('client_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('client.projects.index', compact('projects'));
    }

    public function dmIndex()
    {
        $myProjectIds = Task::where('assigned_to', auth()->id())->pluck('project_id')->unique();

        $projects = Project::with(['tasks', 'creator', 'client'])
            ->withCount('tasks')
            ->whereIn('id', $myProjectIds)
            ->orderBy('name')
            ->get();

        return view('dm.projects.index', compact('projects'));
    }

    public function pmIndex()
    {
        $projects = Project::with(['tasks', 'creator', 'client'])
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

        $project = Project::create([
            'name'        => request('name'),
            'description' => request('description'),
            'start_date'  => request('start_date'),
            'end_date'    => request('end_date'),
            'status'      => request('status', 'active'),
            'created_by'  => auth()->id(),
            'client_id'   => request('client_id') ?: null,
        ]);

        ActivityLog::record('created_project', 'Created project "' . $project->name . '"', $project);

        $redirect = auth()->user()->role === 'pm' ? 'pm.projects' : 'projects.index';
        return redirect()->route($redirect)->with('status', 'Project created successfully.');
    }

    public function searchClients()
    {
        $q = request('q', '');
        $clients = User::where('role', 'client')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($clients);
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
            'client_id'   => request('client_id') ?: null,
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