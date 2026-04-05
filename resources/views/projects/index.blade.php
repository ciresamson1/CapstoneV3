@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Projects</h2>

    <a href="{{ route('projects.create') }}" class="btn btn-primary mb-3">Create Project</a>

    @foreach($projects as $project)
        <div class="card mb-3 p-3 shadow-sm">
            <h4>{{ $project->name }}</h4>
            <p>{{ $project->description }}</p>

            <p><strong>Progress:</strong> {{ $project->progress }}%</p>

            <a href="{{ route('tasks.create', $project->id) }}" class="btn btn-success btn-sm">Add Task</a>
        </div>
    @endforeach
</div>
@endsection