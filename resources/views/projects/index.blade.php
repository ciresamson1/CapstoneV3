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
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white shadow-lg' : 'text-slate-300' }}">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🏠</span>
                        Dashboard
                    </a>
                    <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 {{ request()->routeIs('projects.index') ? 'bg-slate-800 text-white shadow-lg' : 'text-slate-300' }}">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('projects.create') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">➕</span>
                        Create Project
                    </a>
                </nav>
            </div>

            <div class="mt-10 rounded-3xl border border-slate-800 bg-slate-900 p-5">
                <div class="mb-3 text-sm uppercase tracking-[0.24em] text-slate-500">Quick Actions</div>
                <div class="space-y-3 text-sm text-slate-300">
                    <a href="{{ route('projects.create') }}" class="block rounded-2xl bg-emerald-500 px-3 py-2 text-center font-semibold text-slate-950 hover:bg-emerald-400">Create Project</a>
                    <a href="{{ route('register') }}" class="block rounded-2xl border border-slate-800 px-3 py-2 text-center hover:bg-slate-800">Manage Users</a>
                    <a href="#" class="block rounded-2xl border border-slate-800 px-3 py-2 text-center hover:bg-slate-800">Assign Roles</a>
                    <a href="#" class="block rounded-2xl border border-slate-800 px-3 py-2 text-center hover:bg-slate-800">View Reports</a>
                </div>
            </div>
        </aside>

        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Projects</h2>
                    <p class="mt-2 text-sm text-slate-500">Browse and filter active projects with a dashboard-first interface.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('projects.create') }}" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">New Project</a>
                </div>
            </div>

            <section class="grid gap-4 lg:grid-cols-[0.6fr_0.4fr] xl:grid-cols-[0.65fr_0.35fr]">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Project table</h3>
                            <p class="text-sm text-slate-500">Search by keyword and filter by status or owner.</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input id="projectSearch" type="text" placeholder="Search projects..." class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" />
                            <button id="clearFilters" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear filters</button>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Status</label>
                            <select id="filterStatus" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option value="all">All Statuses</option>
                                @foreach(collect($projects)->pluck('status')->unique()->filter()->sort()->values() as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Owner</label>
                            <select id="filterOwner" class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option value="all">All Owners</option>
                                @foreach(collect($projects)->pluck('creator.name')->unique()->filter()->sort()->values() as $owner)
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
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Project summary</h3>
                            <p class="text-sm text-slate-500">Quick insights into the active portfolio.</p>
                        </div>
                        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Overview</span>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Total projects</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $projects->count() }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Average completion</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $projects->avg('progress') ?? 0 }}%</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Most active owner</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ collect($projects)->groupBy('creator.name')->sortByDesc(fn($items) => $items->count())->keys()->first() ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-6 rounded-3xl bg-white p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table id="projectsTable" class="min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Project</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Owner</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Progress</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Status</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Tasks</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Ends</th>
                                <th class="pb-4 font-semibold text-slate-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" id="projectsTableBody">
                            @foreach($projects as $project)
                                <tr class="hover:bg-slate-50 project-row" data-status="{{ $project->status }}" data-owner="{{ $project->creator?->name ?? 'Unassigned' }}" data-progress="{{ $project->progress }}">
                                    <td class="py-5 pr-8">
                                        <div class="font-semibold text-slate-900">{{ $project->name }}</div>
                                        <div class="text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($project->description, 80) }}</div>
                                    </td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->creator?->name ?? 'Unassigned' }}</td>
                                    <td class="py-5 pr-8">
                                        <div class="w-full rounded-full bg-slate-100">
                                            <div class="rounded-full bg-slate-900 text-[11px] text-white" style="width: {{ $project->progress }}%; padding: .45rem .6rem;">{{ $project->progress }}%</div>
                                        </div>
                                    </td>
                                    <td class="py-5 pr-8 text-slate-700">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $project->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($project->status === 'on-hold' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">{{ ucfirst($project->status) }}</span>
                                    </td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->tasks_count }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->end_date ? \Illuminate\Support\Carbon::parse($project->end_date)->format('M d, Y') : 'TBD' }}</td>
                                    <td class="py-5 text-slate-700">
                                        <a href="{{ route('projects.show', $project->id) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="noResultsMessage" class="hidden py-8 text-center text-sm text-slate-500">No projects match your filters.</div>
            </section>
        </main>
    </div>
</div>

<script>
    const searchInput = document.getElementById('projectSearch');
    const filterStatus = document.getElementById('filterStatus');
    const filterOwner = document.getElementById('filterOwner');
    const filterProgress = document.getElementById('filterProgress');
    const clearFilters = document.getElementById('clearFilters');
    const rows = Array.from(document.querySelectorAll('.project-row'));
    const noResultsMessage = document.getElementById('noResultsMessage');

    function parseProgressRange(value) {
        if (value === 'all') return null;
        const [min, max] = value.split('-').map(Number);
        return { min, max };
    }

    function filterProjects() {
        const keyword = searchInput.value.toLowerCase().trim();
        const status = filterStatus.value;
        const owner = filterOwner.value;
        const progressRange = parseProgressRange(filterProgress.value);

        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('td:first-child .font-semibold').textContent.toLowerCase();
            const description = row.querySelector('td:first-child .text-sm').textContent.toLowerCase();
            const rowStatus = row.dataset.status.toLowerCase();
            const rowOwner = row.dataset.owner.toLowerCase();
            const rowProgress = Number(row.dataset.progress);

            const matchesKeyword = keyword === '' || name.includes(keyword) || description.includes(keyword) || rowStatus.includes(keyword) || rowOwner.includes(keyword);
            const matchesStatus = status === 'all' || rowStatus === status.toLowerCase();
            const matchesOwner = owner === 'all' || rowOwner === owner.toLowerCase();
            const matchesProgress = !progressRange || (rowProgress >= progressRange.min && rowProgress <= progressRange.max);

            const visible = matchesKeyword && matchesStatus && matchesOwner && matchesProgress;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount += 1;
        });

        noResultsMessage.classList.toggle('hidden', visibleCount > 0);
    }

    searchInput.addEventListener('input', filterProjects);
    filterStatus.addEventListener('change', filterProjects);
    filterOwner.addEventListener('change', filterProjects);
    filterProgress.addEventListener('change', filterProjects);
    clearFilters.addEventListener('click', (event) => {
        event.preventDefault();
        searchInput.value = '';
        filterStatus.value = 'all';
        filterOwner.value = 'all';
        filterProgress.value = 'all';
        filterProjects();
    });
</script>
@endsection