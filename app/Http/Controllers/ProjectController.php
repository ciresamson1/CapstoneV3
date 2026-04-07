<?php

namespace App\Http\Controllers;

use App\Models\Project;

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

    public function create()
    {
        return view('projects.create');
    }

    public function store()
    {
        request()->validate([
            'name' => 'required',
            'description' => 'required'
        ]);

        Project::create([
            'name' => request('name'),
            'description' => request('description'),
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'status' => 'active',
            'created_by' => auth()->id() // ✅ FIX
        ]);

        return redirect()->route('projects.index');
    }

    public function show($id)
    {
        $project = Project::with([
            'tasks.comments.user'
        ])->findOrFail($id);

        return view('projects.show', compact('project'));
    }
}