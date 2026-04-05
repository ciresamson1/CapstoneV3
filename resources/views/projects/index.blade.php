@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Projects</h2>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
</div>

<div class="row">
@foreach($projects as $project)
    <div class="col-md-4">
        <div class="card p-3 mb-3">
            <h4>{{ $project->name }}</h4>
            <p>{{ $project->description }}</p>

            <div class="progress mb-2">
                <div class="progress-bar" style="width: {{ $project->progress }}%">
                    {{ $project->progress }}%
                </div>
            </div>

            <a href="{{ route('tasks.create', $project->id) }}" class="btn btn-success btn-sm">Add Task</a>
        </div>
    </div>
@endforeach
</div>
@endsection