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

        {{-- Client Sidebar --}}
        @include('partials.sidebar')

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
            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @php
                    $totalTasks      = $tasks->count();
                    $completedTasks  = $tasks->filter(fn($t) => $t->effective_status === 'completed')->count();
                    $inProgressTasks = $tasks->filter(fn($t) => $t->effective_status === 'in_progress')->count();
                    $pendingTasks    = $tasks->filter(fn($t) => $t->effective_status === 'pending')->count();
                    $overdueTasks    = $tasks->filter(fn($t) => $t->effective_status === 'overdue')->count();
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
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Overdue</p>
                    <p class="mt-4 text-3xl font-bold text-red-600">{{ $overdueTasks }}</p>
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
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {{-- Tasks Table --}}
            <section class="overflow-hidden rounded-3xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-600" id="tasksTable">
                        <thead class="border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Task <button onclick="sortTable('tasksTable',0,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('tasksTable',0,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Project <button onclick="sortTable('tasksTable',1,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('tasksTable',1,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Assigned To <button onclick="sortTable('tasksTable',2,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('tasksTable',2,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Status <button onclick="sortTable('tasksTable',3,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('tasksTable',3,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Due Date <button onclick="sortTable('tasksTable',4,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('tasksTable',4,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($tasks as $task)
                                <tr class="task-row hover:bg-slate-50"
                                    data-title="{{ strtolower($task->title) }}"
                                    data-project="{{ $task->project_id }}"
                                    data-status="{{ $task->effective_status }}">
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ $task->title }}</td>
                                    <td class="px-6 py-4">{{ $task->project->name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            @if($task->effective_status === 'completed') bg-emerald-100 text-emerald-700
                                            @elseif($task->effective_status === 'overdue') bg-red-100 text-red-700
                                            @elseif($task->effective_status === 'in_progress') bg-sky-100 text-sky-700
                                            @elseif($task->effective_status === 'pending') bg-amber-100 text-amber-700
                                            @else bg-rose-100 text-rose-700 @endif">
                                            {{ $task->effective_status === 'overdue' ? 'Overdue' : ucfirst(str_replace('_', ' ', $task->effective_status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('client.projects.show', $task->project_id) }}#task-wrapper-{{ $task->id }}" title="View task & comments" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-white transition hover:bg-brand-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">No tasks found for your projects.</td>
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

function sortTable(tableId, colIndex, dir) {
    const tbody = document.querySelector('#' + tableId + ' tbody');
    const rows  = Array.from(tbody.rows).filter(r => r.cells.length > 1);
    rows.sort(function(a, b) {
        const get = c => (c ? c.textContent.trim().split('\n').map(s => s.trim()).filter(Boolean)[0] : '') || '';
        const av = get(a.cells[colIndex]), bv = get(b.cells[colIndex]);
        if (av === 'TBD' || av === '\u2014') return 1;
        if (bv === 'TBD' || bv === '\u2014') return -1;
        const an = parseFloat(av.replace(/[^0-9.-]/g, '')), bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
        if (!isNaN(an) && !isNaN(bn)) return dir === 'asc' ? an - bn : bn - an;
        const ad = new Date(av), bd = new Date(bv);
        if (!isNaN(ad.getTime()) && !isNaN(bd.getTime())) return dir === 'asc' ? ad - bd : bd - ad;
        return dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    rows.forEach(r => tbody.appendChild(r));
}
</script>
@endsection
