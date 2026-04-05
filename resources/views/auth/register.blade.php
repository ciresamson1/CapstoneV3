@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 p-4">
        <h3 class="mb-4">Register</h3>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <input type="password" name="password_confirmation" class="form-control mb-2" placeholder="Confirm Password" required>

            <select name="role" class="form-control mb-3" required>
                <option value="admin">Admin</option>
                <option value="pm">Project Manager</option>
                <option value="dm">Digital Marketer</option>
                <option value="client">Client</option>
            </select>

            <button class="btn btn-success w-100">Register</button>
        </form>
    </div>
</div>
@endsection