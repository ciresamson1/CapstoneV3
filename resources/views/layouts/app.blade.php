<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bg: #f5f5f5;
            --card: #ffffff;
            --text: #212121;
            --muted: #6c757d;
            --primary: #6200ea;
            --secondary: #03dac6;
        }

        body.dark-mode {
            --bg: #121212;
            --card: #1e1e1e;
            --text: #ffffff;
            --muted: #cccccc;
            --primary: #bb86fc;
            --secondary: #03dac6;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .card {
            background: var(--card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        /* 🔥 FORCE TEXT VISIBILITY */
        .card h4,
        .card p,
        .card span {
            color: var(--text) !important;
        }

        .text-muted {
            color: var(--muted) !important;
        }

        .navbar {
            background: linear-gradient(45deg, #6200ea, #bb86fc) !important;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
        }

        .btn-success {
            background: var(--secondary);
            border: none;
            color: black;
        }

        .progress {
            background: #2c2c2c;
            height: 10px;
            border-radius: 10px;
        }

        .progress-bar {
            background: var(--primary);
        }
    </style>
</head>
<body id="appBody">

<nav class="navbar navbar-dark px-4">
    <a href="/" class="navbar-brand">PCMS</a>

    <div class="ms-auto d-flex align-items-center gap-2">
        <button onclick="toggleDarkMode()" class="btn btn-light btn-sm">🌙</button>

        @auth
            <span class="text-white">{{ auth()->user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger btn-sm ms-2">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-light btn-sm">Login</a>
            <a href="{{ route('register') }}" class="btn btn-success btn-sm">Register</a>
        @endauth
    </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

<script>
    function toggleDarkMode() {
        let body = document.getElementById('appBody');
        body.classList.toggle('dark-mode');
        localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light');
    }

    window.onload = function () {
        if (localStorage.getItem('theme') === 'dark') {
            document.getElementById('appBody').classList.add('dark-mode');
        }
    }
</script>

</body>
</html>