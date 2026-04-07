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
                    <button id="openAssignRoleModal" type="button" class="flex w-full items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">👥</span>
                        Assign Role
                    </button>
                </nav>
            </div>
        </aside>

        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Projects</h2>
                    <p class="mt-2 text-sm text-slate-500">Browse and filter active projects with a dashboard-first interface.</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                    </form>
                </div>
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Total projects</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $projects->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Average completion</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $projects->avg('progress') ?? 0 }}%</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2 lg:col-span-1">
                        <p class="text-sm text-slate-500">Most active owner</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ collect($projects)->groupBy('creator.name')->sortByDesc(fn($items) => $items->count())->keys()->first() ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Project table</h3>
                        <p class="text-sm text-slate-500">Search by keyword and filter by status, owner, or progress.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input id="projectSearch" type="text" placeholder="Search projects..." class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" />
                        <button id="clearFilters" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear filters</button>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
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

                <div class="overflow-x-auto">
                    <table id="projectsTable" class="mt-6 min-w-full text-left text-sm text-slate-600">
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
    document.addEventListener('DOMContentLoaded', function() {
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

        const assignRoleModal = document.getElementById('assignRoleModal');
        const openAssignRoleModal = document.getElementById('openAssignRoleModal');
        const closeAssignRoleModal = document.getElementById('closeAssignRoleModal');

        openAssignRoleModal.addEventListener('click', () => {
            assignRoleModal.classList.remove('hidden');
        });

        closeAssignRoleModal.addEventListener('click', () => {
            assignRoleModal.classList.add('hidden');
        });

        assignRoleModal.addEventListener('click', (event) => {
            if (event.target === assignRoleModal) {
                assignRoleModal.classList.add('hidden');
            }
        });
    });
</script>

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
@endsection