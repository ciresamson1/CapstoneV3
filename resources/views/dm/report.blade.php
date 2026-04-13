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

        {{-- DM Sidebar --}}
        @include('partials.sidebar')

        {{-- Main --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Reports</h2>
                    <p class="mt-2 text-sm text-slate-500">Client KPI summaries for projects you are assigned to.</p>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- CLIENT SECTION --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <p class="mb-4 text-xs text-slate-500 uppercase tracking-[0.2em] font-semibold">Client KPIs — {{ $clientData->count() }} client(s)</p>

            @if($clientData->isEmpty())
                <div class="rounded-3xl bg-white p-8 text-center text-sm text-slate-400 shadow-sm">No clients found for your assigned projects.</div>
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

        </main>
    </div>
</div>
@endsection
