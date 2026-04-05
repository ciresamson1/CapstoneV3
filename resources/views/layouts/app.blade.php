<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4">
    <a href="/" class="navbar-brand">PCMS</a>

    <div class="ms-auto">
        @auth
            <span class="text-white me-3">{{ auth()->user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button class="btn btn-danger btn-sm">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
            <a href="{{ route('register') }}" class="btn btn-success btn-sm ms-2">Register</a>
        @endauth
    </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

</body>
</html>