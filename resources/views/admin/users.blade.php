@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">
        <div class="flex items-center justify-between bg-slate-950 px-4 py-3 text-slate-100 xl:hidden">
            <div class="text-sm font-semibold">PCMS Admin</div>
            <button type="button" data-sidebar-toggle class="inline-flex items-center justify-center rounded-xl border border-slate-700 p-2 text-slate-100" aria-label="Open navigation menu" aria-controls="adminSidebar" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>

        <div id="adminSidebarBackdrop" class="fixed inset-0 z-30 hidden bg-slate-950/60 xl:hidden"></div>

        <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-40 w-80 max-w-[85vw] shrink-0 -translate-x-full overflow-y-auto bg-slate-950 p-6 text-slate-100 transition-transform duration-200 xl:static xl:min-h-screen xl:w-80 xl:translate-x-0">
            <div class="mb-10">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-3xl bg-white p-1.5"><img src="/images/sgpro-logo.webp" alt="SGpro Logo" class="h-full w-full object-contain"></div>
                        <div>
                            <h1 class="text-lg font-semibold">PCMS Admin</h1>
                            <p class="text-sm text-slate-400">Project Coordination</p>
                        </div>
                    </div>
                    <button type="button" data-sidebar-close class="inline-flex items-center justify-center rounded-xl border border-slate-700 p-2 text-slate-100 xl:hidden" aria-label="Close navigation menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
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
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Joined</th>
                                <th class="pb-4 font-semibold text-slate-900">Actions</th>
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
                                    <td class="py-5 pr-8 text-slate-700">{{ $user->created_at?->format('M d, Y') }}</td>
                                    <td class="py-5">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                onclick="openEditModal({{ $user->id }}, {{ json_encode($user->name) }}, {{ json_encode($user->first_name) }}, {{ json_encode($user->last_name) }}, {{ json_encode($user->email) }}, {{ json_encode($user->role) }}, {{ json_encode($user->position ?? '') }}, {{ json_encode($user->company ?? '') }})"
                                                class="inline-flex items-center gap-1 rounded-2xl bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                                                ✏️ Edit
                                            </button>
                                            @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-2xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                    🗑 Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
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

{{-- Edit User Modal --}}
<div id="editUserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-xl mx-4">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Edit User</h2>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">First Name</label>
                    <input type="text" name="first_name" id="edit_first_name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none"/>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="edit_email" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Role</label>
                    <select name="role" id="edit_role" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none">
                        <option value="admin">Admin</option>
                        <option value="pm">Project Manager</option>
                        <option value="dm">Digital Marketer</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Position</label>
                    <input type="text" name="position" id="edit_position"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none"/>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Company</label>
                    <input type="text" name="company" id="edit_company"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">New Password <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="password" name="password" id="edit_password"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none" autocomplete="new-password"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="edit_password_confirmation"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-sky-400 focus:outline-none" autocomplete="new-password"/>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="rounded-3xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-3xl bg-sky-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-400">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, firstName, lastName, email, role, position, company) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_first_name').value = firstName;
    document.getElementById('edit_last_name').value = lastName;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_position').value = position;
    document.getElementById('edit_company').value = company;
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirmation').value = '';
    document.getElementById('editUserForm').action = '/admin/users/' + id;
    const modal = document.getElementById('editUserModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeEditModal() {
    const modal = document.getElementById('editUserModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('editUserModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
@endsection
