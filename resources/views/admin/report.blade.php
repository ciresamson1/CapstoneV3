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

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Reports</h2>
                    <p class="mt-2 text-sm text-slate-500">Role-based KPI summaries computed from live project and task data.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                </form>
            </div>

            {{-- Tab bar --}}
            <div class="mb-6 flex gap-2 border-b border-slate-200">
                <button data-tab="pm"     class="tab-btn rounded-t-2xl px-5 py-3 text-sm font-semibold transition active-tab">Project Managers</button>
                <button data-tab="dm"     class="tab-btn rounded-t-2xl px-5 py-3 text-sm font-semibold transition">Digital Marketers</button>
                <button data-tab="client" class="tab-btn rounded-t-2xl px-5 py-3 text-sm font-semibold transition">Clients</button>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- PROJECT MANAGER SECTION --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div id="tab-pm" class="tab-panel">
                <p class="mb-4 text-xs text-slate-500 uppercase tracking-[0.2em] font-semibold">Project Manager KPIs — {{ $pmData->count() }} member(s)</p>

                @if($pmData->isEmpty())
                    <div class="rounded-3xl bg-white p-8 text-center text-sm text-slate-400 shadow-sm">No project managers found.</div>
                @else
                    <div class="grid gap-5 lg:grid-cols-2">
                        @foreach($pmData as $row)
                            @php
                                $riskColor = $row['riskScore'] >= 7 ? 'rose' : ($row['riskScore'] >= 4 ? 'amber' : 'emerald');
                                $riskBg    = "bg-{$riskColor}-50 border-{$riskColor}-200";
                                $riskText  = "text-{$riskColor}-700";
                            @endphp
                            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                                {{-- Name row --}}
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-700">
                                            {{ strtoupper(substr($row['pm']->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $row['pm']->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $row['pm']->position ?? 'Project Manager' }}</div>
                                        </div>
                                    </div>
                                    {{-- Risk score badge --}}
                                    <div class="rounded-2xl border px-3 py-2 text-center min-w-[72px]
                                        @if($row['riskScore'] >= 7) border-rose-200 bg-rose-50
                                        @elseif($row['riskScore'] >= 4) border-amber-200 bg-amber-50
                                        @else border-emerald-200 bg-emerald-50 @endif">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider
                                            @if($row['riskScore'] >= 7) text-rose-500
                                            @elseif($row['riskScore'] >= 4) text-amber-500
                                            @else text-emerald-500 @endif">Risk</div>
                                        <div class="text-lg font-bold
                                            @if($row['riskScore'] >= 7) text-rose-700
                                            @elseif($row['riskScore'] >= 4) text-amber-700
                                            @else text-emerald-700 @endif">{{ $row['riskScore'] }}<span class="text-xs font-normal">/10</span></div>
                                    </div>
                                </div>

                                {{-- KPI grid --}}
                                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Total Projects</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['totalProjects'] }}</p>
                                        @if($row['overdueProjects'] > 0)
                                            <p class="text-[11px] text-rose-500 mt-0.5">{{ $row['overdueProjects'] }} overdue</p>
                                        @else
                                            <p class="text-[11px] text-emerald-500 mt-0.5">All on track</p>
                                        @endif
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">On-Time Delivery</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['onTimeRate'] >= 80 ? 'text-emerald-700' : ($row['onTimeRate'] >= 50 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $row['onTimeRate'] }}%
                                        </p>
                                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-1.5 rounded-full {{ $row['onTimeRate'] >= 80 ? 'bg-emerald-500' : ($row['onTimeRate'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                 style="width:{{ $row['onTimeRate'] }}%"></div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Task Delay Rate</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['taskDelayRate'] <= 20 ? 'text-emerald-700' : ($row['taskDelayRate'] <= 50 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $row['taskDelayRate'] }}%
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $row['overdueTasks'] }} of {{ $row['totalTasks'] }} tasks</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Timeline Changes</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['timelineChanges'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">date shifts logged</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Total Tasks</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['totalTasks'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">across all projects</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Overdue Tasks</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['overdueTasks'] === 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                            {{ $row['overdueTasks'] }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">past due date</p>
                                    </div>
                                </div>
                                {{-- Download button --}}
                                <div class="mt-5 flex justify-end border-t border-slate-100 pt-4">
                                    <a href="{{ route('admin.report.pdf', $row['pm']->id) }}" target="_blank"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"/></svg>
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- DIGITAL MARKETER SECTION --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div id="tab-dm" class="tab-panel hidden">
                <p class="mb-4 text-xs text-slate-500 uppercase tracking-[0.2em] font-semibold">Digital Marketer KPIs — {{ $dmData->count() }} member(s)</p>

                @if($dmData->isEmpty())
                    <div class="rounded-3xl bg-white p-8 text-center text-sm text-slate-400 shadow-sm">No digital marketers found.</div>
                @else
                    <div class="grid gap-5 lg:grid-cols-2">
                        @foreach($dmData as $row)
                            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                                {{-- Name row --}}
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-700">
                                            {{ strtoupper(substr($row['dm']->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $row['dm']->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $row['dm']->position ?? 'Digital Marketer' }}</div>
                                        </div>
                                    </div>
                                    {{-- Quality score --}}
                                    <div class="rounded-2xl border px-3 py-2 text-center min-w-[80px]
                                        @if($row['qualityScore'] >= 70) border-emerald-200 bg-emerald-50
                                        @elseif($row['qualityScore'] >= 40) border-amber-200 bg-amber-50
                                        @else border-rose-200 bg-rose-50 @endif">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider
                                            @if($row['qualityScore'] >= 70) text-emerald-500
                                            @elseif($row['qualityScore'] >= 40) text-amber-500
                                            @else text-rose-500 @endif">Quality</div>
                                        <div class="text-lg font-bold
                                            @if($row['qualityScore'] >= 70) text-emerald-700
                                            @elseif($row['qualityScore'] >= 40) text-amber-700
                                            @else text-rose-700 @endif">{{ $row['qualityScore'] }}<span class="text-xs font-normal">%</span></div>
                                    </div>
                                </div>

                                {{-- KPI grid --}}
                                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Completion Rate</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['completionRate'] >= 70 ? 'text-emerald-700' : ($row['completionRate'] >= 40 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $row['completionRate'] }}%
                                        </p>
                                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-1.5 rounded-full {{ $row['completionRate'] >= 70 ? 'bg-emerald-500' : ($row['completionRate'] >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                 style="width:{{ $row['completionRate'] }}%"></div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Tasks Assigned</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['totalTasks'] }}</p>
                                        <p class="text-[11px] text-emerald-600 mt-0.5">{{ $row['completed'] }} completed</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Overdue Tasks</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['overdueTasks'] === 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                            {{ $row['overdueTasks'] }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">past due date</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">30-day Output</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['recentCompleted'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">tasks done this month</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Comments Made</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['totalComments'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $row['totalReplies'] }} are replies</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Revision Rate</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['revisionRate'] <= 20 ? 'text-emerald-700' : ($row['revisionRate'] <= 50 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $row['revisionRate'] }}%
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">replies / total comments</p>
                                    </div>
                                </div>
                                {{-- Download button --}}
                                <div class="mt-5 flex justify-end border-t border-slate-100 pt-4">
                                    <a href="{{ route('admin.report.pdf', $row['dm']->id) }}" target="_blank"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"/></svg>
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- CLIENT SECTION --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div id="tab-client" class="tab-panel hidden">
                <p class="mb-4 text-xs text-slate-500 uppercase tracking-[0.2em] font-semibold">Client KPIs — {{ $clientData->count() }} client(s)</p>

                @if($clientData->isEmpty())
                    <div class="rounded-3xl bg-white p-8 text-center text-sm text-slate-400 shadow-sm">No clients found.</div>
                @else
                    <div class="grid gap-5 lg:grid-cols-2">
                        @foreach($clientData as $row)
                            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                                {{-- Name row --}}
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700">
                                            {{ strtoupper(substr($row['client']->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $row['client']->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $row['client']->company ?? 'Client' }}</div>
                                        </div>
                                    </div>
                                    {{-- Friction score --}}
                                    <div class="rounded-2xl border px-3 py-2 text-center min-w-[80px]
                                        @if($row['frictionScore'] >= 7) border-rose-200 bg-rose-50
                                        @elseif($row['frictionScore'] >= 4) border-amber-200 bg-amber-50
                                        @else border-emerald-200 bg-emerald-50 @endif">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider
                                            @if($row['frictionScore'] >= 7) text-rose-500
                                            @elseif($row['frictionScore'] >= 4) text-amber-500
                                            @else text-emerald-500 @endif">Friction</div>
                                        <div class="text-lg font-bold
                                            @if($row['frictionScore'] >= 7) text-rose-700
                                            @elseif($row['frictionScore'] >= 4) text-amber-700
                                            @else text-emerald-700 @endif">{{ $row['frictionScore'] }}<span class="text-xs font-normal">/10</span></div>
                                    </div>
                                </div>

                                {{-- KPI grid --}}
                                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-sky-50 border border-sky-100 p-3">
                                        <p class="text-[11px] text-sky-600">Total Projects</p>
                                        <p class="mt-1 text-2xl font-bold text-sky-700">{{ $row['totalProjects'] }}</p>
                                        <p class="text-[11px] text-sky-400 mt-0.5">assigned projects</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Total Comments</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['totalComments'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">messages sent</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Replies Made</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['totalReplies'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">responses in threads</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Engagement Rate</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['engagementRate'] >= 40 ? 'text-emerald-700' : ($row['engagementRate'] >= 20 ? 'text-amber-700' : 'text-slate-700') }}">
                                            {{ $row['engagementRate'] }}%
                                        </p>
                                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-1.5 rounded-full {{ $row['engagementRate'] >= 40 ? 'bg-emerald-500' : ($row['engagementRate'] >= 20 ? 'bg-amber-500' : 'bg-sky-400') }}"
                                                 style="width:{{ $row['engagementRate'] }}%"></div>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">New Threads Started</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $row['rootComments'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">topics initiated</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Revision Requests</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['revisionRequests'] === 0 ? 'text-emerald-700' : ($row['revisionRequests'] <= 3 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $row['revisionRequests'] }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">distinct threads replied</p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-[11px] text-slate-500">Friction Score</p>
                                        <p class="mt-1 text-2xl font-bold {{ $row['frictionScore'] <= 3 ? 'text-emerald-700' : ($row['frictionScore'] <= 6 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $row['frictionScore'] }}<span class="text-base font-normal text-slate-400">/10</span>
                                        </p>
                                        <p class="text-[11px]
                                            {{ $row['frictionScore'] <= 3 ? 'text-emerald-500' : ($row['frictionScore'] <= 6 ? 'text-amber-500' : 'text-rose-500') }} mt-0.5">
                                            {{ $row['frictionScore'] <= 3 ? 'Low friction' : ($row['frictionScore'] <= 6 ? 'Moderate' : 'High friction') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Feedback Reactions --}}
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                                        <div class="text-2xl leading-none">👍</div>
                                        <div class="mt-1 text-xl font-bold text-emerald-700">{{ $row['thumbsUp'] }}</div>
                                        <div class="mt-0.5 text-[11px] text-emerald-600">Positive reactions</div>
                                    </div>
                                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-center">
                                        <div class="text-2xl leading-none">👎</div>
                                        <div class="mt-1 text-xl font-bold text-rose-700">{{ $row['thumbsDown'] }}</div>
                                        <div class="mt-0.5 text-[11px] text-rose-600">Negative reactions</div>
                                    </div>
                                </div>

                                {{-- Download button --}}
                                <div class="mt-5 flex justify-end border-t border-slate-100 pt-4">
                                    <a href="{{ route('admin.report.pdf', $row['client']->id) }}" target="_blank"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"/></svg>
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </main>
    </div>
</div>

@if(auth()->user()->role === 'admin')
{{-- Assign Role Modal --}}
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
@endif

<style>
    .tab-btn { color: #64748b; }
    .tab-btn.active-tab { color: #0f172a; border-bottom: 2px solid #f59e0b; background: #fffbeb; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    const buttons = document.querySelectorAll('.tab-btn');
    const panels  = document.querySelectorAll('.tab-panel');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            buttons.forEach(b => b.classList.remove('active-tab'));
            panels.forEach(p => p.classList.add('hidden'));

            btn.classList.add('active-tab');
            document.getElementById('tab-' + target).classList.remove('hidden');
        });
    });

    // Assign role modal
    const modal = document.getElementById('assignRoleModal');
    if (modal) {
        document.getElementById('openAssignRoleModal').addEventListener('click', () => modal.classList.remove('hidden'));
        document.getElementById('closeAssignRoleModal').addEventListener('click', () => modal.classList.add('hidden'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
    }
});
</script>
@endsection
