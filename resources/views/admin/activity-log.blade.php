@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    {{-- Mobile top bar (visible below xl breakpoint) --}}
    <header class="sticky top-0 z-50 flex items-center justify-between bg-slate-950 px-4 py-3 xl:hidden">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white p-1.5">
                <img src="/images/sgpro-logo.webp" alt="SGpro Logo" class="h-full w-full object-contain">
            </div>
            <span class="text-sm font-semibold text-white">PCMS</span>
        </div>
        <button id="sidebarOpen" type="button" aria-label="Open menu"
            class="inline-flex items-center justify-center rounded-2xl bg-slate-800 p-2.5 text-slate-300 transition hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </header>

    {{-- Backdrop overlay (mobile sidebar) --}}
    <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-black/50 xl:hidden"></div>

    <div class="flex xl:min-h-screen xl:flex-row">

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Activity Log</h2>
                    <p class="mt-2 text-sm text-slate-500">Monitor every action performed by team members across all projects.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                </form>
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                {{-- Summary cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Total actions</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $logs->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                        <p class="text-sm text-sky-600">Projects created</p>
                        <p class="mt-2 text-3xl font-semibold text-sky-700">{{ $logs->where('action', 'created_project')->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-sm text-emerald-600">Tasks created</p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $logs->where('action', 'created_task')->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-violet-100 bg-violet-50 p-4">
                        <p class="text-sm text-violet-600">Comments posted</p>
                        <p class="mt-2 text-3xl font-semibold text-violet-700">{{ $logs->where('action', 'posted_comment')->count() }}</p>
                    </div>
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.activity-log.index') }}">
                    <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Log table</h3>
                            <p class="text-sm text-slate-500">Filter by member, action type, or date range.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <input id="logSearch" type="text" placeholder="Search descriptions..." class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" />
                            <a href="{{ route('admin.activity-log.index') }}" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear</a>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Member</label>
                            <select name="user_id" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option value="">All Members</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Action</label>
                            <select name="action" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">From date</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">To date</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="rounded-3xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Apply filters</button>
                    </div>
                </form>

                {{-- Table --}}
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Member</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Action</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Description</th>
                                <th class="pb-4 font-semibold text-slate-900">Date & time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="activityLogBody">
                            @forelse($logs as $log)
                                <tr class="log-row hover:bg-slate-50">
                                    <td class="py-4 pr-6">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                                                {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                                            </span>
                                            <div>
                                                <div class="font-semibold text-slate-900">{{ $log->user?->name ?? 'Unknown' }}</div>
                                                <div class="text-xs text-slate-400">{{ ucfirst($log->user?->role ?? '') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6">
                                        @php
                                            $badgeColor = match($log->action) {
                                                'created_project' => 'bg-sky-100 text-sky-700',
                                                'created_task'    => 'bg-emerald-100 text-emerald-700',
                                                'updated_task'    => 'bg-amber-100 text-amber-700',
                                                'posted_comment'  => 'bg-violet-100 text-violet-700',
                                                default           => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColor }}">
                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 pr-6 text-slate-700 td-description">{{ $log->description }}</td>
                                    <td class="py-4 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('M d, Y · h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-sm text-slate-400">No activity found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="noResultsMessage" class="hidden py-8 text-center text-sm text-slate-500">No activity matches your search.</div>
            </section>
        </main>
    </div>
</div>

@if(auth()->user()->role === 'admin')
{{-- Assign Role Modal --}}
<div id="assignRoleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-900">Invite a new user</h3>
                <p class="mt-2 text-sm text-slate-500">Send a registration link with a preselected role.</p>
            </div>
            <button id="closeAssignRoleModal" type="button" class="rounded-3xl border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>
        <form method="POST" action="{{ route('admin.users.invite') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                <input type="email" name="email" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                <select name="role" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
                    <option value="admin">Admin</option>
                    <option value="pm">Project Manager</option>
                    <option value="dm">Digital Marketer</option>
                    <option value="client">Client</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full rounded-3xl bg-emerald-500 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Send Invite</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('logSearch');
    const rows        = Array.from(document.querySelectorAll('.log-row'));
    const noResults   = document.getElementById('noResultsMessage');

    searchInput.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const ok = keyword === '' || text.includes(keyword);
            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });
        noResults.classList.toggle('hidden', visible > 0);
    });

    const modal = document.getElementById('assignRoleModal');
    if (modal) {
        document.getElementById('openAssignRoleModal').addEventListener('click', () => modal.classList.remove('hidden'));
        document.getElementById('closeAssignRoleModal').addEventListener('click', () => modal.classList.add('hidden'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
    }
});
</script>
@endsection
