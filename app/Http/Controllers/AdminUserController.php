<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\InviteUserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('admin.users', compact('users'));
    }

    public function invite(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,pm,dm,client'],
        ]);

        $inviteUrl = route('register', ['email' => $request->email, 'role' => $request->role]);

        Mail::to($request->email)->send(new InviteUserMail($inviteUrl, $request->role));

        return back()->with('status', 'Invitation sent to ' . $request->email);
    }
}
