<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->role === 'admin') {
            $defaultRoute = route('admin.dashboard');
        } elseif ($user->role === 'pm') {
            $defaultRoute = route('pm.dashboard');
        } else {
            // dm, client — no dedicated dashboard, send to projects list
            $defaultRoute = route('projects.index');
        }

        return redirect()->intended($defaultRoute);
    }

    return back()->withErrors([
        'email' => 'Invalid credentials',
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);