@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">
        <aside class="w-full xl:w-80 shrink-0 bg-slate-950 text-slate-100 p-6">
            <div class="mb-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-slate-100 text-slate-950 font-bold">PC</div>
                    <div>
                        <h1 class="text-lg font-semibold">PCMS Admin</h1>
                        <p class="text-sm text-slate-400">Project Coordination</p>
                    </div>
                </div>
                <div class="mt-6 rounded-3xl border border-slate-800 bg-slate-900 p-4">
                    <div class="text-sm text-slate-400">Signed in as</div>
                    <div class="mt-2 text-base font-semibold text-white">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-slate-500">{{ auth()->user()->role }}</div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Navigation</div>
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🏠</span>
                        Dashboard
                    </a>
                    <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('admin.tasks.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">✅</span>
                        Tasks
                    </a>
                    <a href="{{ route('admin.activity-log.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📋</span>
                        Activity Log
                    </a>
                    <a href="{{ route('admin.report.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📊</span>
                        Reports
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-500 text-slate-950">👥</span>
                        Manage Users
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Manage Users</h2>
                    <p class="mt-2 text-sm text-slate-500">Review users, roles, and profile details across the organisation.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#assign-invite" class="rounded-3xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Invite User</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                    </form>
                </div>
            </div>

            @if(session('status'))
                <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif

            <div class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Username</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Name</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Email</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Role</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Position</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Company</th>
                                <th class="pb-4 font-semibold text-slate-900">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($users as $user)
                                <tr>
                                    <td class="py-5 pr-8 font-semibold text-slate-900">{{ $user->name }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ trim($user->first_name . ' ' . $user->last_name) }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $user->email }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ ucfirst($user->role) }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $user->position }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $user->company }}</td>
                                    <td class="py-5 text-slate-700">{{ $user->created_at?->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="assign-invite" class="mt-8 rounded-3xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Invite a user</h3>
                <p class="mt-2 text-sm text-slate-500">Send a registration link with a preselected role.</p>
                <form method="POST" action="{{ route('admin.users.invite') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
                        @error('email')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                        <select name="role" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
                            <option value="admin">Admin</option>
                            <option value="pm">Project Manager</option>
                            <option value="dm">Digital Marketer</option>
                            <option value="client">Client</option>
                        </select>
                        @error('role')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rounded-3xl bg-emerald-500 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Send Invite</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
