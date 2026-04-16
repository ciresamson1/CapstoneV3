@extends('layouts.app')

@section('content')
<div class="card p-4">
    <h3>Create Project</h3>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf

        <input type="text" name="name" class="form-control mb-2" placeholder="Project Name" required>
        <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>

        <input type="date" name="start_date" class="form-control mb-2" required>
        <input type="date" name="end_date" class="form-control mb-2" required>

        <select name="client_id" class="form-control mb-3">
            <option value="">Assign client (optional)</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
            @endforeach
        </select>

        <button class="btn btn-primary w-100">Create</button>
    </form>
</div>
@endsection