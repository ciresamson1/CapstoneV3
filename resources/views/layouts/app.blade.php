<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PCMS</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.jsx'])

</head>
<body>

@unless(request()->routeIs('dashboard'))
<nav class="navbar navbar-dark px-4" style="background:#6200ea;">
<a href="/" class="navbar-brand">PCMS</a>

<div class="ms-auto d-flex align-items-center gap-2">

@auth
<span class="text-white">{{ auth()->user()->name }}</span>

<form method="POST" action="{{ route('logout') }}">
@csrf
<button class="btn btn-danger btn-sm">Logout</button>
</form>
@endauth

</div>
</nav>
@endunless

<div class="container mt-4">
@yield('content')
</div>

</body>
</html>