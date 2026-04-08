@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">

        {{-- DM Sidebar --}}
        <aside class="w-full xl:w-80 shrink-0 bg-slate-950 text-slate-100 p-6">
            <div class="mb-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-slate-100 text-slate-950 font-bold">PC</div>
                    <div>
                        <h1 class="text-lg font-semibold">PCMS Portal</h1>
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
                    <a href="{{ route('dm.dashboard') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🏠</span>
                        Dashboard
                    </a>
                    <a href="{{ route('dm.projects') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('dm.tasks.index') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-500 text-white">✅</span>
                        Tasks
                    </a>
                    <a href="{{ route('dm.report.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📊</span>
                        Reports
                    </a>
                </nav>
            </div>

            <div class="mt-10 border-t border-slate-800 pt-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🚪</span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">My Tasks</h2>
                    <p class="mt-2 text-sm text-slate-500">Tasks assigned to you. Search, filter by project or status.</p>
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
                        <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $tasks->where('status', 'completed')->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-sm text-amber-600">In progress</p>
                        <p class="mt-2 text-3xl font-semibold text-amber-700">{{ $tasks->where('status', 'in_progress')->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                        <p class="text-sm text-rose-600">Pending</p>
                        <p class="mt-2 text-3xl font-semibold text-rose-700">{{ $tasks->where('status', 'pending')->count() }}</p>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Task table</h3>
                        <p class="text-sm text-slate-500">Filter by project or status.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input id="taskSearch" type="text" placeholder="Search tasks..." class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" />
                        <button id="clearFilters" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear filters</button>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
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
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Status</label>
                        <select id="filterStatus" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
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
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Status</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Progress</th>
                                <th class="pb-4 pr-6 font-semibold text-slate-900">Due date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($tasks as $task)
                                @php
                                    $taskStatus  = $task->progress >= 100 ? 'completed' : ($task->status ?? 'pending');
                                    $projectName = $task->project?->name ?? 'None';
                                @endphp
                                <tr class="task-row hover:bg-slate-50"
                                    data-project="{{ $projectName }}"
                                    data-status="{{ $taskStatus }}">
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
                                        @if($taskStatus === 'completed')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Completed</span>
                                        @elseif($taskStatus === 'in_progress')
                                            <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">In Progress</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-4 pr-6">
                                        <div class="flex items-center gap-2">
                                            <div class="relative h-3 w-24 overflow-hidden rounded-full bg-slate-200">
                                                <div class="absolute inset-y-0 left-0 rounded-full bg-violet-500" style="width: {{ $task->progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-slate-600">{{ $task->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6 text-slate-700">
                                        {{ $task->end_date ? \Illuminate\Support\Carbon::parse($task->end_date)->format('M d, Y') : 'TBD' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-sm text-slate-500">No tasks assigned to you yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="noResultsMessage" class="hidden py-8 text-center text-sm text-slate-500">No tasks match your filters.</div>
            </section>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput   = document.getElementById('taskSearch');
    const filterProject = document.getElementById('filterProject');
    const filterStatus  = document.getElementById('filterStatus');
    const clearFilters  = document.getElementById('clearFilters');
    const rows          = Array.from(document.querySelectorAll('.task-row'));
    const noResults     = document.getElementById('noResultsMessage');

    function applyFilters() {
        const keyword  = searchInput.value.toLowerCase().trim();
        const project  = filterProject.value;
        const status   = filterStatus.value;
        let visible = 0;

        rows.forEach(row => {
            const title      = row.querySelector('td:first-child .font-semibold').textContent.toLowerCase();
            const desc       = row.querySelector('td:first-child .text-xs')?.textContent.toLowerCase() ?? '';
            const rowProject = row.dataset.project;
            const rowStatus  = row.dataset.status;

            const ok =
                (keyword === '' || title.includes(keyword) || desc.includes(keyword) || rowProject.toLowerCase().includes(keyword)) &&
                (project === 'all' || rowProject === project) &&
                (status  === 'all' || rowStatus  === status);

            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        noResults.classList.toggle('hidden', visible > 0);
    }

    searchInput.addEventListener('input', applyFilters);
    filterProject.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);
    clearFilters.addEventListener('click', e => {
        e.preventDefault();
        searchInput.value    = '';
        filterProject.value  = 'all';
        filterStatus.value   = 'all';
        applyFilters();
    });
});
</script>
@endsection
