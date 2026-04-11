@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">

        {{-- DM Sidebar --}}
        <aside class="w-full xl:w-80 shrink-0 bg-slate-950 text-slate-100 p-6">
            <div class="mb-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-3xl bg-white p-1.5"><img src="/images/sgpro-logo.webp" alt="SGpro Logo" class="h-full w-full object-contain"></div>
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
                    <a href="{{ route('dm.projects') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-500 text-white">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('dm.tasks.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">✅</span>
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

        {{-- Main Content --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Projects</h2>
                    <p class="mt-2 text-sm text-slate-500">Projects you are assigned tasks on.</p>
                </div>
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                {{-- Summary cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Total projects</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $projects->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Average completion</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ round($projects->avg('progress') ?? 0) }}%</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2 lg:col-span-1">
                        <p class="text-sm text-slate-500">Active projects</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $projects->where('status', 'active')->count() }}</p>
                    </div>
                </div>

                {{-- Table header + controls --}}
                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Project table</h3>
                        <p class="text-sm text-slate-500">Search by keyword and filter by status or progress.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <input id="projectSearch" type="text" placeholder="Search projects..." class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" />
                        <button id="clearFilters" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear filters</button>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Status</label>
                        <select id="filterStatus" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All Statuses</option>
                            @foreach($projects->pluck('status')->unique()->filter()->sort()->values() as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Owner</label>
                        <select id="filterOwner" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All Owners</option>
                            @foreach($projects->pluck('creator.name')->unique()->filter()->sort()->values() as $owner)
                                <option value="{{ $owner }}">{{ $owner }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Progress</label>
                        <select id="filterProgress" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                            <option value="all">All progress</option>
                            <option value="0-25">0–25%</option>
                            <option value="26-50">26–50%</option>
                            <option value="51-75">51–75%</option>
                            <option value="76-100">76–100%</option>
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table id="projectsTable" class="mt-6 min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-4 pr-8 font-semibold text-slate-900 whitespace-nowrap">Project <button onclick="sortTable('projectsTable',0,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',0,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900 whitespace-nowrap">Owner <button onclick="sortTable('projectsTable',1,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',1,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900 whitespace-nowrap">Progress <button onclick="sortTable('projectsTable',2,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',2,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900 whitespace-nowrap">Status <button onclick="sortTable('projectsTable',3,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',3,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900 whitespace-nowrap">Tasks <button onclick="sortTable('projectsTable',4,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',4,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900 whitespace-nowrap">Ends <button onclick="sortTable('projectsTable',5,'asc')" class="text-[9px] text-slate-300 hover:text-brand-500">▲</button><button onclick="sortTable('projectsTable',5,'desc')" class="text-[9px] text-slate-300 hover:text-brand-500">▼</button></th>
                                <th class="pb-4 font-semibold text-slate-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="projectsTableBody">
                            @forelse($projects as $project)
                                <tr class="hover:bg-slate-50 project-row"
                                    data-status="{{ $project->status }}"
                                    data-owner="{{ $project->creator?->name ?? 'Unassigned' }}"
                                    data-progress="{{ $project->progress }}">
                                    <td class="py-5 pr-8">
                                        <div class="font-semibold text-slate-900">{{ $project->name }}</div>
                                        <div class="text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($project->description, 80) }}</div>
                                    </td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->creator?->name ?? 'Unassigned' }}</td>
                                    <td class="py-5 pr-8">
                                        <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100">
                                            @if($project->progress > 0)
                                                <div class="absolute inset-y-0 left-0 rounded-full bg-violet-500 transition-all" style="width: {{ $project->progress }}%"></div>
                                            @endif
                                            <span class="absolute inset-0 flex items-center justify-center text-[11px] font-semibold text-slate-700">{{ $project->progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-5 pr-8">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            {{ $project->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($project->status === 'on_hold' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->tasks_count }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : 'TBD' }}</td>
                                    <td class="py-5 text-slate-700">
                                        <a href="{{ route('dm.projects.show', $project->id) }}" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-white transition hover:bg-brand-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-slate-500">No projects assigned to you yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="noResultsMessage" class="hidden py-8 text-center text-sm text-slate-500">No projects match your filters.</div>
            </section>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('projectSearch');
    const filterStatus   = document.getElementById('filterStatus');
    const filterOwner    = document.getElementById('filterOwner');
    const filterProgress = document.getElementById('filterProgress');
    const clearFilters   = document.getElementById('clearFilters');
    const rows           = Array.from(document.querySelectorAll('.project-row'));
    const noResults      = document.getElementById('noResultsMessage');

    function parseProgressRange(value) {
        if (value === 'all') return null;
        const [min, max] = value.split('-').map(Number);
        return { min, max };
    }

    function filterProjects() {
        const keyword = searchInput.value.toLowerCase().trim();
        const status  = filterStatus.value;
        const owner   = filterOwner.value;
        const range   = parseProgressRange(filterProgress.value);
        let visible   = 0;

        rows.forEach(row => {
            const name      = row.querySelector('td:first-child .font-semibold').textContent.toLowerCase();
            const desc      = row.querySelector('td:first-child .text-sm').textContent.toLowerCase();
            const rowStatus = row.dataset.status.toLowerCase();
            const rowOwner  = row.dataset.owner.toLowerCase();
            const rowProg   = Number(row.dataset.progress);

            const ok = (keyword === '' || name.includes(keyword) || desc.includes(keyword) || rowStatus.includes(keyword) || rowOwner.includes(keyword))
                && (status === 'all' || rowStatus === status.toLowerCase())
                && (owner  === 'all' || rowOwner  === owner.toLowerCase())
                && (!range || (rowProg >= range.min && rowProg <= range.max));

            row.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });

        noResults.classList.toggle('hidden', visible > 0 || rows.length === 0);
    }

    searchInput.addEventListener('input', filterProjects);
    filterStatus.addEventListener('change', filterProjects);
    filterOwner.addEventListener('change', filterProjects);
    filterProgress.addEventListener('change', filterProjects);
    clearFilters.addEventListener('click', e => {
        e.preventDefault();
        searchInput.value    = '';
        filterStatus.value   = 'all';
        filterOwner.value    = 'all';
        filterProgress.value = 'all';
        filterProjects();
    });
});

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
