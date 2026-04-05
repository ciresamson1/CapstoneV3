@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 p-4">
        <h3 class="mb-4">Register</h3>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin">Admin</option>
                    <option value="pm">Project Manager</option>
                    <option value="dm">Digital Marketer</option>
                    <option value="client">Client</option>
                </select>
            </div>

            <button class="btn btn-primary w-100">Register</button>
        </form>
    </div>
</div>
@endsection