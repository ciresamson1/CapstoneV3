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
        @include('partials.sidebar')

        <main class="flex-1 min-w-0 p-6 xl:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Admin Dashboard</h2>
                    <p class="mt-2 text-sm text-slate-500">Transforming raw project data into actionable insights for decision-making and risk monitoring.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <form method="POST" action="{{ route('logout') }}" class="sm:mt-0">
                        @csrf
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                    </form>
                </div>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($kpiCards as $card)
                <a href="{{ $card['url'] }}" class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $card['title'] }}</p>
                            <p class="mt-4 text-3xl font-bold text-slate-900" data-kpi="{{ $card['title'] }}">{{ $card['value'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-3xl text-lg" style="background-color: {{ $card['color'] === 'red' ? 'rgba(254, 226, 226, 0.65)' : ($card['color'] === 'yellow' ? 'rgba(254, 240, 138, 0.65)' : ($card['color'] === 'green' ? 'rgba(220, 252, 231, 0.65)' : 'rgba(191, 219, 254, 0.65)')) }}; color: {{ $card['color'] === 'red' ? '#dc2626' : ($card['color'] === 'yellow' ? '#f59e0b' : ($card['color'] === 'green' ? '#16a34a' : '#0284c7')) }};">
                            @if($card['color'] === 'red') ⚠️ @elseif($card['color'] === 'yellow') ⏳ @elseif($card['color'] === 'green') ✅ @else 💬 @endif
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ $card['note'] }}</p>
                </a>
                @endforeach
            </section>

            <section class="mt-6">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Smart Alerts</h3>
                            <p class="text-sm text-slate-500">Critical updates and risk signals sorted by priority.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600">Real-time</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($alerts as $alert)
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900">{{ $alert['headline'] }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ $alert['details'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $alert['color'] === 'red' ? 'bg-rose-100 text-rose-700' : ($alert['color'] === 'yellow' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700') }}">{{ $alert['label'] }}</span>
                                </div>
                                @if(isset($alert['items']) && is_iterable($alert['items']))
                                    <div class="mt-4 max-h-64 overflow-y-auto space-y-2 pr-1">
                                        @foreach($alert['items'] as $item)
                                            @if(isset($item['task_id']))
                                                <div class="rounded-2xl bg-white p-3 shadow-sm">
                                                    <p class="text-xs font-semibold text-slate-900 truncate">{{ $item['title'] }}</p>
                                                    <p class="mt-0.5 text-xs text-brand-500">{{ $item['project'] }}</p>
                                                    <a href="{{ route('projects.show', $item['project_id']) }}#task-wrapper-{{ $item['task_id'] }}"
                                                       class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        View Task
                                                    </a>
                                                </div>
                                            @else
                                                <div class="rounded-2xl bg-white p-3 text-sm text-slate-600 shadow-sm">
                                                    <p class="font-semibold text-slate-900">{{ $item['project'] }}</p>
                                                    <p>{{ $item['count'] }} overdue task{{ $item['count'] === 1 ? '' : 's' }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Project Health</h3>
                            <p class="text-sm text-slate-500">Live performance overview for active and at-risk projects.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm text-emerald-700">On Track</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-700">At Risk</span>
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-sm text-rose-700">Delayed</span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto px-6 py-6">
                    <table id="projectHealthTable" class="min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">
                                    <div class="flex items-center gap-1">Project Name
                                        <button onclick="sortTable(0,'asc')" class="rounded p-0.5 hover:bg-slate-100" title="A→Z">▲</button>
                                        <button onclick="sortTable(0,'desc')" class="rounded p-0.5 hover:bg-slate-100" title="Z→A">▼</button>
                                    </div>
                                </th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">
                                    <div class="flex items-center gap-1">Progress
                                        <button onclick="sortTable(1,'asc')" class="rounded p-0.5 hover:bg-slate-100" title="Low→High">▲</button>
                                        <button onclick="sortTable(1,'desc')" class="rounded p-0.5 hover:bg-slate-100" title="High→Low">▼</button>
                                    </div>
                                </th>
                                <th class="pb-4 pr-8 font-semibold text-slate-900">
                                    <div class="flex items-center gap-1">Status
                                        <button onclick="sortTable(2,'asc')" class="rounded p-0.5 hover:bg-slate-100" title="A→Z">▲</button>
                                        <button onclick="sortTable(2,'desc')" class="rounded p-0.5 hover:bg-slate-100" title="Z→A">▼</button>
                                    </div>
                                </th>
                                <th class="pb-4 font-semibold text-slate-900">
                                    <div class="flex items-center gap-1">Team Load
                                        <button onclick="sortTable(3,'asc')" class="rounded p-0.5 hover:bg-slate-100" title="Low→High">▲</button>
                                        <button onclick="sortTable(3,'desc')" class="rounded p-0.5 hover:bg-slate-100" title="High→Low">▼</button>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @if(is_iterable($projectHealth))
                                @foreach($projectHealth as $project)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-5 pr-8 font-medium text-slate-900">{{ $project['name'] }}</td>
                                        <td class="py-5 pr-8">
                                            <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100 progress-bar-inner">
                                                @if($project['progress'] > 0)
                                                    <div class="absolute inset-y-0 left-0 rounded-full bg-violet-500 transition-all" style="width: {{ $project['progress'] }}%"></div>
                                                @endif
                                                <span class="absolute inset-0 flex items-center justify-center text-[11px] font-semibold text-slate-700">{{ $project['progress'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="py-5 pr-8 text-slate-700">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $project['status'] === 'On Track' ? 'bg-emerald-100 text-emerald-700' : ($project['status'] === 'At Risk' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">{{ $project['status'] }}</span>
                                        </td>
                                        <td class="py-5 text-slate-700">{{ $project['load'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-sm text-slate-500">Project health data is unavailable.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="mt-6 grid gap-6 xl:grid-cols-[0.75fr_0.5fr]">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm min-w-0">
                    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Interactive Gantt Timeline</h3>
                            <p class="text-sm text-slate-500">Live timeline view of task windows, deadlines and project ownership.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Project search --}}
                            <div class="relative" id="projectSearchWrap">
                                <input type="text" id="projectSearchInput"
                                    placeholder="Search project…"
                                    autocomplete="off"
                                    class="w-52 rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <input type="hidden" id="projectFilter" value="">
                                <ul id="projectSuggestions"
                                    class="absolute left-0 top-full z-30 mt-1 hidden w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                                    @foreach(collect($ganttData)->pluck('project')->unique()->values() as $projectName)
                                        <li data-value="{{ $projectName }}"
                                            class="cursor-pointer px-4 py-2.5 text-sm text-slate-700 hover:bg-violet-50 hover:text-violet-700">
                                            {{ $projectName }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <select id="userFilter" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option value="all">All Users</option>
                                @foreach(collect($ganttData)->pluck('assigned_to')->unique() as $userName)
                                    <option value="{{ $userName }}">{{ $userName }}</option>
                                @endforeach
                            </select>
                            <select id="zoomLevel" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option value="1">Day</option>
                                <option value="7">Week</option>
                                <option value="30">Month</option>
                            </select>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Gantt Timeline</p>
                            <p id="ganttProjectTitle" class="mt-3 text-lg font-semibold text-slate-900">All Projects</p>
                            <p id="ganttProjectDescription" class="mt-2 text-sm text-slate-500">Timeline across all projects.</p>
                        </div>
                        <div class="h-[420px] min-h-[420px] overflow-hidden rounded-3xl bg-white">
                            <canvas id="ganttChart" class="h-full w-full block" style="min-height:420px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 min-w-0">
                    <div class="rounded-3xl bg-white p-6 shadow-sm min-w-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Team Performance</h3>
                                <p class="text-sm text-slate-500">Completed and delayed tasks per user.</p>
                            </div>
                        </div>
                        <div class="mt-6 h-[320px] min-w-0">
                            <canvas id="teamPerformanceChart" class="h-full w-full"></canvas>
                        </div>
                    </div>
                    <div id="client-activity" class="rounded-3xl bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Client Activity</h3>
                                <p class="text-sm text-slate-500">Pending approvals and recent feedback.</p>
                            </div>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-slate-500">Pending approvals</p>
                                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">{{ $clientActivity['pendingApprovals'] }}</span>
                                </div>
                                <p class="mt-3 text-sm text-slate-600">Currently awaiting client review and confirmation.</p>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <h4 class="text-sm font-semibold text-slate-900">Recent client comments</h4>
                                <div class="mt-4 space-y-3">
                                @if(isset($clientActivity['recentComments']) && is_iterable($clientActivity['recentComments']))
                                    @forelse($clientActivity['recentComments'] as $comment)
                                        <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-semibold text-slate-900">{{ $comment['user'] }}</p>
                                                    <p class="text-sm text-brand-500">{{ $comment['project'] }}</p>
                                                </div>
                                                <span class="text-xs text-slate-400 shrink-0">{{ $comment['time'] }}</span>
                                            </div>
                                            <p class="mt-3 text-sm text-slate-600">{{ $comment['message'] }}</p>
                                            @if(!empty($comment['project_id']) && !empty($comment['task_id']))
                                            <div class="mt-3">
                                                <a href="{{ route('projects.show', $comment['project_id']) }}#task-wrapper-{{ $comment['task_id'] }}"
                                                   class="inline-flex items-center gap-1.5 rounded-full bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View Task
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-500">No recent client comments available.</p>
                                    @endforelse
                                @else
                                    <p class="text-sm text-slate-500">No recent client comments available.</p>
                                @endif
                            </div>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <h4 class="text-sm font-semibold text-slate-900">Revision cycles</h4>
                                <div class="mt-3 space-y-2">
                                    @foreach($clientActivity['revisionCycles'] as $cycle)
                                        <div class="flex items-center justify-between rounded-3xl bg-white p-3 text-sm text-slate-600">
                                            <span>{{ $cycle['project'] }}</span>
                                            <span class="font-semibold text-slate-900">{{ $cycle['cycles'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ganttData = @json($ganttData);
    const teamPerformance = @json($teamPerformance);

    const ganttCtx = document.getElementById('ganttChart').getContext('2d');
    const teamCtx = document.getElementById('teamPerformanceChart').getContext('2d');

    let ganttChart;
    let teamChart;

    function formatDateOffset(offset) {
        const date = new Date();
        date.setDate(date.getDate() + Number(offset));
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    function getZoomLabel(zoomDays) {
        if (zoomDays === 1) return 'Daily timeline';
        if (zoomDays === 7) return 'Weekly timeline';
        return 'Monthly timeline';
    }

    function createGanttChart(data, zoomDays) {
        const filtered = data;
        const maxSpan = Math.max(...filtered.map(item => item.startOffset + item.duration), 7);
        const stepSize = zoomDays;

        const offsetDataset = {
            label: 'Start offset',
            backgroundColor: 'rgba(203,213,225,0.3)',
            stack: 'combined',
            data: filtered.map(item => item.startOffset),
            borderRadius: 8,
            borderSkipped: false,
            maxBarThickness: 28,
        };

        const durationDataset = {
            label: 'Duration',
            backgroundColor: filtered.map(item => item.color),
            stack: 'combined',
            data: filtered.map(item => item.duration),
            borderRadius: 8,
            borderSkipped: false,
            maxBarThickness: 28,
        };

        if (ganttChart) {
            ganttChart.destroy();
        }

        ganttChart = new Chart(ganttCtx, {
            type: 'bar',
            data: {
                labels: filtered.map(item => item.title),
                datasets: [offsetDataset, durationDataset],
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        type: 'linear',
                        stacked: true,
                        min: 0,
                        max: Math.max(maxSpan, 7),
                        ticks: {
                            stepSize: stepSize,
                            callback: function(value) {
                                return formatDateOffset(value);
                            },
                            color: '#475569',
                        },
                        title: {
                            display: true,
                            text: getZoomLabel(zoomDays),
                            color: '#475569',
                        },
                        grid: { color: 'rgba(15, 23, 42, 0.08)' },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { color: '#475569' },
                        grid: { display: false },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(contexts) {
                                const item = data[contexts[0].dataIndex];
                                return item ? item.project : '';
                            },
                            label: function(context) {
                                const item = data[context.dataIndex];
                                if (context.dataset.label === 'Start offset') return null;
                                return [
                                    item ? '📌 ' + item.title : '',
                                    'Duration: ' + context.formattedValue + ' days',
                                    '👆 Click to view task',
                                ];
                            }
                        }
                    },
                    legend: { display: false },
                },
                onClick: function(e, elements) {
                    if (!elements.length) return;
                    const item = data[elements[0].index];
                    if (item && item.project_id && item.id) {
                        window.location.href = '/projects/' + item.project_id + '#task-wrapper-' + item.id;
                    }
                },
                onHover: function(e, elements) {
                    e.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                },
            }
        });
    }

    function createTeamChart() {
        if (teamChart) {
            teamChart.destroy();
        }

        teamChart = new Chart(teamCtx, {
            type: 'bar',
            data: {
                labels: teamPerformance.labels,
                datasets: [
                    {
                        label: 'Completed Tasks',
                        data: teamPerformance.completed,
                        backgroundColor: '#22c55e',
                        borderRadius: 12,
                    },
                    {
                        label: 'Delayed Tasks',
                        data: teamPerformance.delayed,
                        backgroundColor: '#f59e0b',
                        borderRadius: 12,
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
    }

    const ganttProjectTitleEl = document.getElementById('ganttProjectTitle');
    const ganttProjectDescriptionEl = document.getElementById('ganttProjectDescription');

    function getUniqueGanttProject() {
        const unique = Object.values(ganttData.reduce((acc, item) => {
            if (!acc[item.project]) {
                acc[item.project] = item;
            }
            return acc;
        }, {}));
        return unique.length === 1 ? unique[0] : null;
    }

    function updateGanttProjectInfo(selectedProject) {
        const projectItem = (!selectedProject || selectedProject === 'all')
            ? getUniqueGanttProject()
            : ganttData.find(item => item.project === selectedProject);

        if (!projectItem) {
            ganttProjectTitleEl.textContent = selectedProject && selectedProject !== 'all' ? selectedProject : '';
            ganttProjectDescriptionEl.textContent = '';
            return;
        }

        ganttProjectTitleEl.textContent = projectItem.project;
        ganttProjectDescriptionEl.textContent = projectItem.project_description || 'Timeline for the selected project.';
    }

    function filterGantt() {
        const project = document.getElementById('projectFilter').value;
        const user    = document.getElementById('userFilter').value;
        const zoom    = parseInt(document.getElementById('zoomLevel').value, 10);

        const filtered = ganttData.filter(item => {
            const projectMatch = !project || item.project === project;
            const userMatch    = user === 'all' || item.assigned_to === user;
            return projectMatch && userMatch;
        });

        updateGanttProjectInfo(project || 'all');
        createGanttChart(filtered, zoom);
    }

    function refreshKpiCards() {
        fetch('{{ route('admin.dashboard.metrics') }}')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('[data-kpi]').forEach(el => {
                    const key = el.dataset.kpi;
                    const card = data.kpiCards.find(item => item.title === key);
                    if (card) {
                        el.innerText = card.value;
                    }
                });
            });
    }

    // Project suggestive search
    const projectSearchInput = document.getElementById('projectSearchInput');
    const projectFilterHidden = document.getElementById('projectFilter');
    const projectSuggestions = document.getElementById('projectSuggestions');
    const projectItems = projectSuggestions.querySelectorAll('li');

    projectSearchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let hasVisible = false;
        projectItems.forEach(li => {
            const match = li.dataset.value.toLowerCase().includes(q);
            li.style.display = match ? '' : 'none';
            if (match) hasVisible = true;
        });
        projectSuggestions.classList.toggle('hidden', !q || !hasVisible);
        // Clear selection if user clears the input
        if (!q) {
            projectFilterHidden.value = '';
            filterGantt();
        }
    });

    projectItems.forEach(li => {
        li.addEventListener('click', function () {
            projectSearchInput.value  = this.dataset.value;
            projectFilterHidden.value = this.dataset.value;
            projectSuggestions.classList.add('hidden');
            filterGantt();
        });
    });

    document.addEventListener('click', e => {
        if (!document.getElementById('projectSearchWrap').contains(e.target)) {
            projectSuggestions.classList.add('hidden');
        }
    });

    document.getElementById('userFilter').addEventListener('change', filterGantt);
    document.getElementById('zoomLevel').addEventListener('change', filterGantt);

    updateGanttProjectInfo('all');
    createGanttChart(ganttData, 7);

    // Auto-select the most recently created project
    if (ganttData.length > 0) {
        const mostRecent = ganttData.reduce((a, b) =>
            (a.project_created_at ?? 0) >= (b.project_created_at ?? 0) ? a : b
        );
        if (mostRecent.project) {
            projectSearchInput.value  = mostRecent.project;
            projectFilterHidden.value = mostRecent.project;
            filterGantt();
        }
    }

    createTeamChart();

    // Refresh KPI cards every 45 seconds
    setInterval(refreshKpiCards, 45000);
</script>

<script>
function sortTable(colIndex, direction) {
    const table = document.getElementById('projectHealthTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
        const aCell = a.cells[colIndex];
        const bCell = b.cells[colIndex];
        if (!aCell || !bCell) return 0;
        // For progress column read the numeric % value
        const aText = aCell.querySelector('[style*="width"]')
            ? parseFloat(aCell.querySelector('span').textContent)
            : aCell.textContent.trim();
        const bText = bCell.querySelector('[style*="width"]')
            ? parseFloat(bCell.querySelector('span').textContent)
            : bCell.textContent.trim();
        const aVal = isNaN(aText) ? aText.toString().toLowerCase() : parseFloat(aText);
        const bVal = isNaN(bText) ? bText.toString().toLowerCase() : parseFloat(bText);
        if (aVal < bVal) return direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return direction === 'asc' ? 1 : -1;
        return 0;
    });
    rows.forEach(row => tbody.appendChild(row));
}
</script>
@endsection