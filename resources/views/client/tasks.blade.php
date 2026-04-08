@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">

        {{-- Client Sidebar --}}
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
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🏠</span>
                        Dashboard
                    </a>
                    <a href="{{ route('client.projects') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('client.tasks.index') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-500 text-white">✅</span>
                        Tasks
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

        {{-- Main Content --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Project Tasks</h2>
                    <p class="mt-2 text-sm text-slate-500">All tasks across your assigned projects.</p>
                </div>
            </div>

            {{-- Summary Cards --}}
            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @php
                    $totalTasks     = $tasks->count();
                    $completedTasks = $tasks->where('status', 'completed')->count();
                    $inProgressTasks = $tasks->where('status', 'in_progress')->count();
                    $pendingTasks   = $tasks->where('status', 'pending')->count();
                @endphp
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Total Tasks</p>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ $totalTasks }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Completed</p>
                    <p class="mt-4 text-3xl font-bold text-emerald-600">{{ $completedTasks }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">In Progress</p>
                    <p class="mt-4 text-3xl font-bold text-sky-600">{{ $inProgressTasks }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Pending</p>
                    <p class="mt-4 text-3xl font-bold text-amber-600">{{ $pendingTasks }}</p>
                </div>
            </section>

            {{-- Filters --}}
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <input type="text" id="searchInput" placeholder="Search task…"
                    class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 sm:w-auto sm:min-w-[200px]">
                <select id="projectFilter" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="">All Projects</option>
                    @foreach($projects as $projectId => $projectName)
                        <option value="{{ $projectId }}">{{ $projectName }}</option>
                    @endforeach
                </select>
                <select id="statusFilter" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {{-- Tasks Table --}}
            <section class="overflow-hidden rounded-3xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-600" id="tasksTable">
                        <thead class="border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-slate-900">Task</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Project</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Assigned To</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Status</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Due Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($tasks as $task)
                                <tr class="task-row hover:bg-slate-50"
                                    data-title="{{ strtolower($task->title) }}"
                                    data-project="{{ $task->project_id }}"
                                    data-status="{{ $task->status }}">
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ $task->title }}</td>
                                    <td class="px-6 py-4">{{ $task->project->name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            @if($task->status === 'completed') bg-emerald-100 text-emerald-700
                                            @elseif($task->status === 'in_progress') bg-sky-100 text-sky-700
                                            @elseif($task->status === 'pending') bg-amber-100 text-amber-700
                                            @else bg-rose-100 text-rose-700 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('M d, Y') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">No tasks found for your projects.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
</div>

<script>
    const searchInput  = document.getElementById('searchInput');
    const projectFilter = document.getElementById('projectFilter');
    const statusFilter = document.getElementById('statusFilter');
    const rows         = document.querySelectorAll('.task-row');

    function applyFilters() {
        const q       = searchInput.value.trim().toLowerCase();
        const project = projectFilter.value;
        const status  = statusFilter.value;

        rows.forEach(row => {
            const titleMatch   = !q       || row.dataset.title.includes(q);
            const projectMatch = !project || row.dataset.project === project;
            const statusMatch  = !status  || row.dataset.status === status;
            row.style.display = (titleMatch && projectMatch && statusMatch) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);
    projectFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
</script>
@endsection
