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
                    <a href="{{ route('client.projects') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-500 text-white">📁</span>
                        Projects
                    </a>
                    <a href="{{ route('client.tasks.index') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">✅</span>
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
                                <th class="px-6 py-4 font-semibold text-slate-900">Project</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Company</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Progress</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Status</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Tasks</th>
                                <th class="px-6 py-4 font-semibold text-slate-900">Ends</th>
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
                                        <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100">
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
                                        <a href="{{ route('client.projects.show', $project->id) }}"
                                           class="inline-flex items-center rounded-3xl bg-violet-100 px-4 py-2 text-xs font-semibold text-violet-700 transition hover:bg-violet-200">
                                            View
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
</script>
@endsection
