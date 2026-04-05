@extends('layouts.app')

@section('content')
<div class="card p-4">
    <h3>Create Task</h3>

    <form method="POST" action="{{ route('tasks.store', $project->id) }}">
        @csrf

        <input type="text" name="title" class="form-control mb-2" placeholder="Task Title" required>
        <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>

        <select name="assigned_to" class="form-control mb-2">
            <option value="">Assign User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
            @endforeach
        </select>

        <input type="date" name="start_date" class="form-control mb-2" required>
        <input type="date" name="end_date" class="form-control mb-3" required>

        <button class="btn btn-success w-100">Create Task</button>
    </form>
</div>
@endsection