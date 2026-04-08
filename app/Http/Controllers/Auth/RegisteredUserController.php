<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,pm,dm,client'],
        ]);

        $plainPassword = $request->password;

        $user = User::create([
            'name' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'position' => $request->position,
            'company' => $request->company,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role' => $request->role,
        ]);

        event(new Registered($user));

        // Send welcome email with credentials and role guide
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user, $plainPassword));
        } catch (\Throwable $e) {
            // Mail failure should not block account creation
        }

        // Only auto-login when this is a self-registration (no one is currently authenticated)
        if (!Auth::check()) {
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        // Admin created the account — stay logged in as admin and go back to users page
        return redirect()->route('admin.users.index')->with('status', 'User account created successfully.');
    }
}