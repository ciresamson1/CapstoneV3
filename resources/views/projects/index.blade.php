@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Projects</h2>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
</div>

@foreach($projects as $project)
<div class="card mb-3 p-3">
    <h4>{{ $project->name }}</h4>
    <p>{{ $project->description }}</p>

    <div class="progress mb-2">
        <div class="progress-bar" style="width: {{ $project->progress }}%">
            {{ $project->progress }}%
        </div>
    </div>

    <!-- ✅ RESTORE BUTTONS -->
    <div class="d-flex gap-2">
        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-primary btn-sm">View</a>

        <a href="{{ route('tasks.create', $project->id) }}" class="btn btn-success btn-sm">
            + Add Task
        </a>
    </div>
</div>
@endforeach
@endsection