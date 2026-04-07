@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">

        {{-- Sidebar --}}
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
                    <a href="{{ route('admin.tasks.index') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-500 text-white">✅</span>
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
                    <button id="openAssignRoleModal" type="button" class="flex w-full items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">👥</span>
                        Assign Role
                    </button>
                </nav>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Tasks</h2>
                    <p class="mt-2 text-sm text-slate-500">Search tasks, see who is working on them, their status and which project they belong to.</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                    </form>
                </div>
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                {{-- Summary cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Total tasks</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $tasks->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-sm text-emerald-600">Completed</p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $tasks->where('progress', 100)->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-sm text-amber-600">In progress</p>
                        <p class="mt-2 text-3xl font-semibold text-amber-700">{{ $tasks->whereBetween('progress', [1, 99])->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                        <p class="text-sm text-rose-600">Not started</p>
                        <p class="mt-2 text-3xl font-semibold text-rose-700">{{ $tasks->where('progress', 0)->count() }}</p>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Task table</h3>
                        <p class="text-sm text-slate-500">Filter by project, assignee, or status.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input id="taskSearch" type="text" placeholder="Search tasks..." class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" />
                        <button id="clearFilters" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear filters</button>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Project</label>
                        <select id="filterProject" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All Projects</option>
                            @foreach($projects as $id => $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Assigned to</label>
                        <select id="filterAssignee" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All Members</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Status</label>
                        <select id="filterStatus" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All Statuses</option>
                            <option value="not-started">Not started</option>
                            <option value="in-progress">In progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div class="mt-6 overflow-x-auto">
                    <table id="tasksTable" class="min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Task</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Project</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Assigned to</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Progress</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Status</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Due date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($tasks as $task)
                                @php
                                    $progress = $task->progress;
                                    $statusLabel = $progress >= 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');
                                    $assignee = $task->assignedTo?->name ?? 'Unassigned';
                                    $projectName = $task->project?->name ?? 'None';
                                @endphp
                                <tr class="task-row hover:bg-slate-50"
                                    data-project="{{ $projectName }}"
                                    data-assignee="{{ $assignee }}"
                                    data-status="{{ $statusLabel }}">
                                    <td class="py-4 pr-6">
                                        <div class="font-semibold text-slate-900">{{ $task->title }}</div>
                                        @if($task->description)
                                            <div class="text-xs text-slate-400 mt-0.5">{{ \Illuminate\Support\Str::limit($task->description, 70) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 pr-6">
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">{{ $projectName }}</span>
                                    </td>
                                    <td class="py-4 pr-6">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                                                {{ strtoupper(substr($assignee, 0, 1)) }}
                                            </span>
                                            <span class="text-slate-700">{{ $assignee }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6">
                                        <div class="min-w-[110px]">
                                            @if($progress >= 100)
                                                <div class="flex items-center gap-1.5">
                                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white text-[10px] font-bold">✓</span>
                                                    <span class="text-xs font-semibold text-emerald-600">100%</span>
                                                </div>
                                            @else
                                                <div class="mb-1 flex items-center justify-between">
                                                    <span class="text-xs font-semibold {{ $progress > 0 ? 'text-sky-600' : 'text-slate-400' }}">{{ $progress }}%</span>
                                                </div>
                                                <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-2 rounded-full {{ $progress > 0 ? 'bg-sky-500' : 'bg-slate-200' }}" style="width:{{ max(3, $progress) }}%"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6">
                                        @if($statusLabel === 'completed')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Completed</span>
                                        @elseif($statusLabel === 'in-progress')
                                            <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">In progress</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Not started</span>
                                        @endif
                                    </td>
                                    <td class="py-4 pr-6 text-slate-700">
                                        {{ $task->end_date ? \Illuminate\Support\Carbon::parse($task->end_date)->format('M d, Y') : 'TBD' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="noResultsMessage" class="hidden py-8 text-center text-sm text-slate-500">No tasks match your filters.</div>
            </section>
        </main>
    </div>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput   = document.getElementById('taskSearch');
    const filterProject = document.getElementById('filterProject');
    const filterAssignee= document.getElementById('filterAssignee');
    const filterStatus  = document.getElementById('filterStatus');
    const clearFilters  = document.getElementById('clearFilters');
    const rows          = Array.from(document.querySelectorAll('.task-row'));
    const noResults     = document.getElementById('noResultsMessage');

    function applyFilters() {
        const keyword  = searchInput.value.toLowerCase().trim();
        const project  = filterProject.value;
        const assignee = filterAssignee.value;
        const status   = filterStatus.value;
        let visible = 0;

        rows.forEach(row => {
            const title      = row.querySelector('td:first-child .font-semibold').textContent.toLowerCase();
            const desc       = row.querySelector('td:first-child .text-xs')?.textContent.toLowerCase() ?? '';
            const rowProject = row.dataset.project;
            const rowAssignee= row.dataset.assignee;
            const rowStatus  = row.dataset.status;

            const ok =
                (keyword === '' || title.includes(keyword) || desc.includes(keyword) || rowProject.toLowerCase().includes(keyword) || rowAssignee.toLowerCase().includes(keyword)) &&
                (project  === 'all' || rowProject  === project) &&
                (assignee === 'all' || rowAssignee === assignee) &&
                (status   === 'all' || rowStatus   === status);

            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        noResults.classList.toggle('hidden', visible > 0);
    }

    searchInput.addEventListener('input', applyFilters);
    filterProject.addEventListener('change', applyFilters);
    filterAssignee.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);
    clearFilters.addEventListener('click', e => {
        e.preventDefault();
        searchInput.value = '';
        filterProject.value = 'all';
        filterAssignee.value = 'all';
        filterStatus.value = 'all';
        applyFilters();
    });

    const modal = document.getElementById('assignRoleModal');
    document.getElementById('openAssignRoleModal').addEventListener('click', () => modal.classList.remove('hidden'));
    document.getElementById('closeAssignRoleModal').addEventListener('click', () => modal.classList.add('hidden'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
});
</script>
@endsection
