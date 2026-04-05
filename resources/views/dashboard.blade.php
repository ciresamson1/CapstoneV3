@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card p-4 shadow rounded-4">
        <h3>Dashboard</h3>

        <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
        <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
        <p><strong>Role:</strong> {{ auth()->user()->role }}</p>
    </div>
</div>
@endsection