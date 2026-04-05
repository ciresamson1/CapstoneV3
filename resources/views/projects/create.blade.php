@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Project</h2>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf

        <input type="text" name="name" class="form-control mb-2" placeholder="Project Name" required>

        <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>

        <input type="date" name="start_date" class="form-control mb-2" required>
        <input type="date" name="end_date" class="form-control mb-2" required>

        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection