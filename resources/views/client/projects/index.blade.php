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
                    <h2 class="text-2xl font-semibold text-slate-900">My Projects</h2>
                    <p class="mt-2 text-sm text-slate-500">All projects assigned to your account.</p>
                </div>
            </div>

            {{-- Summary Cards --}}
            <section class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Total Projects</p>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ $projects->count() }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Avg. Completion</p>
                    @php
                        $avgCompletion = $projects->count()
                            ? round($projects->avg(fn($p) => $p->tasks_count > 0 ? ($p->completed_tasks_count / $p->tasks_count * 100) : 0))
                            : 0;
                    @endphp
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ $avgCompletion }}%</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Active Projects</p>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ $projects->where('status', 'active')->count() }}</p>
                </div>
            </section>

            {{-- Filters --}}
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <input type="text" id="searchInput" placeholder="Search project…"
                    class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 sm:w-auto sm:min-w-[200px]">
                <select id="statusFilter" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="on_hold">On Hold</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="progressFilter" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="">All Progress</option>
                    <option value="0">0%</option>
                    <option value="25">≥ 25%</option>
                    <option value="50">≥ 50%</option>
                    <option value="75">≥ 75%</option>
                    <option value="100">100%</option>
                </select>
            </div>

            {{-- Projects Table --}}
            <section class="overflow-hidden rounded-3xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-600" id="projectsTable">
                        <thead class="border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Project <button onclick="sortTable('projectsTable',0,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',0,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Company <button onclick="sortTable('projectsTable',1,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',1,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Progress <button onclick="sortTable('projectsTable',2,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',2,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Status <button onclick="sortTable('projectsTable',3,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',3,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Tasks <button onclick="sortTable('projectsTable',4,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',4,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">Ends <button onclick="sortTable('projectsTable',5,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',5,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($projects as $project)
                                @php
                                    $total     = $project->tasks_count ?? 0;
                                    $completed = $project->completed_tasks_count ?? 0;
                                    $pct       = $total > 0 ? round($completed / $total * 100) : 0;
                                @endphp
                                <tr class="project-row hover:bg-slate-50"
                                    data-name="{{ strtolower($project->name) }}"
                                    data-status="{{ $project->status }}"
                                    data-progress="{{ $pct }}">
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ $project->name }}</td>
                                    <td class="px-6 py-4">{{ $project->client->company ?? '—' }}</td>
                                    <td class="px-6 py-4 min-w-[160px]">
                                        <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100 progress-bar-inner">
                                            @if($pct > 0)
                                                <div class="absolute inset-y-0 left-0 rounded-full bg-violet-500 transition-all" style="width: {{ $pct }}%"></div>
                                            @endif
                                            <span class="absolute inset-0 flex items-center justify-center text-[11px] font-semibold text-slate-700">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            @if($project->status === 'active') bg-emerald-100 text-emerald-700
                                            @elseif($project->status === 'completed') bg-sky-100 text-sky-700
                                            @elseif($project->status === 'on_hold') bg-amber-100 text-amber-700
                                            @else bg-rose-100 text-rose-700 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $completed }}/{{ $total }}</td>
                                    <td class="px-6 py-4">
                                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('client.projects.show', $project->id) }}" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-white transition hover:bg-brand-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">No projects assigned to you yet.</td>
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
    const searchInput    = document.getElementById('searchInput');
    const statusFilter   = document.getElementById('statusFilter');
    const progressFilter = document.getElementById('progressFilter');
    const rows           = document.querySelectorAll('.project-row');

    function applyFilters() {
        const q        = searchInput.value.trim().toLowerCase();
        const status   = statusFilter.value;
        const progress = progressFilter.value !== '' ? Number(progressFilter.value) : null;

        rows.forEach(row => {
            const nameMatch     = !q        || row.dataset.name.includes(q);
            const statusMatch   = !status   || row.dataset.status === status;
            const progressMatch = progress === null || Number(row.dataset.progress) >= progress;
            row.style.display = (nameMatch && statusMatch && progressMatch) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    progressFilter.addEventListener('change', applyFilters);

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
