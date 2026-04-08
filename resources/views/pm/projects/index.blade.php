@extends('layouts.admin')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">

        {{-- PM Sidebar --}}
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
                    <a href="{{ route('pm.dashboard') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🏠</span>
                        Dashboard
                    </a>
                    <a href="{{ route('pm.projects') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-500 text-white">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('pm.tasks.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">✅</span>
                        Tasks
                    </a>
                    <a href="{{ route('pm.activity-log.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">📋</span>
                        Activity Log
                    </a>
                    <a href="{{ route('pm.report.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
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
                    <p class="mt-2 text-sm text-slate-500">Browse and filter active projects with a dashboard-first interface.</p>
                </div>
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                @if(session('status'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('status') }}</div>
                @endif

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
                        <p class="text-sm text-slate-500">Most active owner</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">
                            {{ $projects->groupBy(fn($p) => $p->creator?->name ?? 'Unassigned')->sortByDesc(fn($items) => $items->count())->keys()->first() ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Table header + controls --}}
                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Project table</h3>
                        <p class="text-sm text-slate-500">Search by keyword and filter by status, owner, or progress.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button id="openCreateProjectModal" type="button" class="rounded-3xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">+ Add Project</button>
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
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Project</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Owner</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Client</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Company</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Progress</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Status</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Tasks</th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">Ends</th>
                                <th class="pb-4 font-semibold text-slate-900">Actions</th>
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
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->client?->name ?? '—' }}</td>
                                    <td class="py-5 pr-8 text-slate-700">{{ $project->client?->company ?? '—' }}</td>
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
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('projects.show', $project->id) }}"
                                               class="rounded-full bg-violet-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-600">View</a>
                                            <button type="button"
                                                onclick="openEditProject({{ $project->id }}, '{{ addslashes($project->name) }}', '{{ addslashes($project->description) }}', '{{ $project->start_date }}', '{{ $project->end_date }}', '{{ $project->status }}', {{ $project->client_id ?? 'null' }}, '{{ addslashes($project->client?->name ?? '') }}', '{{ addslashes($project->client?->email ?? '') }}')"
                                                class="rounded-full bg-sky-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-600">Edit</button>
                                            <form method="POST" action="{{ route('projects.destroy', $project->id) }}" class="inline"
                                                  onsubmit="return confirm('Delete project &quot;{{ addslashes($project->name) }}&quot;? This cannot be undone.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="rounded-full bg-rose-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-600">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-slate-500">No projects yet. Create your first project above.</td>
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

{{-- Create Project Modal --}}
<div id="createProjectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-900">Create new project</h3>
                <p class="mt-2 text-sm text-slate-500">Fill in the details below to add a project.</p>
            </div>
            <button id="closeCreateProjectModal" type="button" class="rounded-3xl border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>

        @if($errors->any())
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('projects.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Project name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. SEO Campaign Q2"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="3" placeholder="Brief project overview..."
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 resize-none">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Start date <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date') }}"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">End date <span class="text-rose-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select name="status" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="on_hold"  {{ old('status') === 'on_hold'  ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="sm:col-span-2" id="createClientWrapper">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Assign Client <span class="font-normal text-slate-400">(optional)</span></label>
                <div class="relative">
                    <input type="text" id="createClientSearch" autocomplete="off" placeholder="e.g. eric | eric@sgpro.co"
                        class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <input type="hidden" name="client_id" id="createClientId">
                    <ul id="createClientDropdown" class="absolute z-50 mt-1 hidden w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg"></ul>
                </div>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full rounded-3xl bg-emerald-500 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Create Project</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Project Modal --}}
<div id="editProjectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-900">Edit project</h3>
                <p class="mt-2 text-sm text-slate-500">Update the project details below.</p>
            </div>
            <button id="closeEditProjectModal" type="button" class="rounded-3xl border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>
        <form id="editProjectForm" method="POST" action="" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf @method('PUT')
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Project name <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_name" name="name"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                <textarea id="edit_description" name="description" rows="3"
                    class="w-full resize-none rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"></textarea>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Start date <span class="text-rose-500">*</span></label>
                <input type="date" id="edit_start_date" name="start_date"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">End date <span class="text-rose-500">*</span></label>
                <input type="date" id="edit_end_date" name="end_date"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select id="edit_status" name="status" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Assign Client <span class="font-normal text-slate-400">(optional)</span></label>
                <div class="relative">
                    <input type="text" id="editClientSearch" autocomplete="off" placeholder="e.g. eric | eric@sgpro.co"
                        class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <input type="hidden" name="client_id" id="editClientId">
                    <ul id="editClientDropdown" class="absolute z-50 mt-1 hidden w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg"></ul>
                </div>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full rounded-3xl bg-sky-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-sky-600">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // -- Filters --
        const searchInput   = document.getElementById('projectSearch');
        const filterStatus  = document.getElementById('filterStatus');
        const filterOwner   = document.getElementById('filterOwner');
        const filterProgress = document.getElementById('filterProgress');
        const clearFilters  = document.getElementById('clearFilters');
        const rows          = Array.from(document.querySelectorAll('.project-row'));
        const noResults     = document.getElementById('noResultsMessage');

        function parseProgressRange(value) {
            if (value === 'all') return null;
            const [min, max] = value.split('-').map(Number);
            return { min, max };
        }

        function filterProjects() {
            const keyword  = searchInput.value.toLowerCase().trim();
            const status   = filterStatus.value;
            const owner    = filterOwner.value;
            const range    = parseProgressRange(filterProgress.value);
            let visible    = 0;

            rows.forEach(row => {
                const name       = row.querySelector('td:first-child .font-semibold').textContent.toLowerCase();
                const desc       = row.querySelector('td:first-child .text-sm').textContent.toLowerCase();
                const rowStatus  = row.dataset.status.toLowerCase();
                const rowOwner   = row.dataset.owner.toLowerCase();
                const rowProg    = Number(row.dataset.progress);

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

        // -- Create Modal --
        const createModal = document.getElementById('createProjectModal');
        document.getElementById('openCreateProjectModal').addEventListener('click', () => {
            createModal.classList.remove('hidden'); createModal.classList.add('flex');
        });
        document.getElementById('closeCreateProjectModal').addEventListener('click', () => {
            createModal.classList.add('hidden'); createModal.classList.remove('flex');
        });
        createModal.addEventListener('click', e => {
            if (e.target === createModal) { createModal.classList.add('hidden'); createModal.classList.remove('flex'); }
        });
        @if($errors->any())
            createModal.classList.remove('hidden'); createModal.classList.add('flex');
        @endif

        // -- Edit Modal --
        const editModal = document.getElementById('editProjectModal');
        document.getElementById('closeEditProjectModal').addEventListener('click', () => {
            editModal.classList.add('hidden'); editModal.classList.remove('flex');
        });
        editModal.addEventListener('click', e => {
            if (e.target === editModal) { editModal.classList.add('hidden'); editModal.classList.remove('flex'); }
        });
    });

    function openEditProject(id, name, description, startDate, endDate, status, clientId, clientName, clientEmail) {
        const modal = document.getElementById('editProjectModal');
        document.getElementById('editProjectForm').action = '/projects/' + id;
        document.getElementById('edit_name').value        = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_start_date').value  = startDate;
        document.getElementById('edit_end_date').value    = endDate;
        document.getElementById('edit_status').value      = status;
        document.getElementById('editClientId').value     = clientId || '';
        document.getElementById('editClientSearch').value = clientId ? (clientName + ' | ' + clientEmail) : '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // Edit modal client autocomplete
    (function () {
        const searchInput = document.getElementById('editClientSearch');
        const hiddenInput = document.getElementById('editClientId');
        const dropdown    = document.getElementById('editClientDropdown');
        const searchUrl   = '{{ route("projects.clients.search") }}';
        let debounceTimer;

        searchInput.addEventListener('input', function () {
            hiddenInput.value = '';
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (!q) { dropdown.classList.add('hidden'); dropdown.innerHTML = ''; return; }
            debounceTimer = setTimeout(() => {
                fetch(searchUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(clients => {
                    dropdown.innerHTML = '';
                    if (!clients.length) {
                        dropdown.innerHTML = '<li class="px-4 py-3 text-sm text-slate-400">No clients found</li>';
                    } else {
                        clients.forEach(c => {
                            const li = document.createElement('li');
                            li.className = 'cursor-pointer px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-700';
                            li.textContent = c.name + ' | ' + c.email;
                            li.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                searchInput.value = c.name + ' | ' + c.email;
                                hiddenInput.value = c.id;
                                dropdown.classList.add('hidden');
                            });
                            dropdown.appendChild(li);
                        });
                    }
                    dropdown.classList.remove('hidden');
                });
            }, 250);
        });

        searchInput.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('hidden'), 150));
        searchInput.addEventListener('focus', () => { if (dropdown.children.length) dropdown.classList.remove('hidden'); });
    })();

    // Create modal client autocomplete
    (function () {
        const searchInput  = document.getElementById('createClientSearch');
        const hiddenInput  = document.getElementById('createClientId');
        const dropdown     = document.getElementById('createClientDropdown');
        const searchUrl    = '{{ route("projects.clients.search") }}';
        let debounceTimer;

        searchInput.addEventListener('input', function () {
            hiddenInput.value = '';
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (!q) { dropdown.classList.add('hidden'); dropdown.innerHTML = ''; return; }
            debounceTimer = setTimeout(() => {
                fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(clients => {
                    dropdown.innerHTML = '';
                    if (!clients.length) {
                        dropdown.innerHTML = '<li class="px-4 py-3 text-sm text-slate-400">No clients found</li>';
                    } else {
                        clients.forEach(c => {
                            const li = document.createElement('li');
                            li.className = 'cursor-pointer px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-700';
                            li.textContent = c.name + ' | ' + c.email;
                            li.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                searchInput.value = c.name + ' | ' + c.email;
                                hiddenInput.value = c.id;
                                dropdown.classList.add('hidden');
                            });
                            dropdown.appendChild(li);
                        });
                    }
                    dropdown.classList.remove('hidden');
                });
            }, 250);
        });

        searchInput.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('hidden'), 150));
        searchInput.addEventListener('focus', () => { if (dropdown.children.length) dropdown.classList.remove('hidden'); });
    })();
</script>
@endsection
