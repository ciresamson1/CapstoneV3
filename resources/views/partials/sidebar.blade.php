@php
    $role = auth()->user()->role;
    $activeClass   = 'bg-slate-800 text-white shadow-lg';
    $inactiveClass = 'text-slate-300 hover:bg-slate-800';
@endphp

{{-- ═══════════════════════════════════════════════════════════════════
     SIDEBAR — fixed overlay on mobile, static column on xl+
     Toggled by #sidebarOpen / #sidebarClose / #sidebarBackdrop
     ═══════════════════════════════════════════════════════════════════ --}}
<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full overflow-y-auto bg-slate-950 text-slate-100 p-6 transition-transform duration-300 ease-in-out xl:static xl:w-80 xl:translate-x-0 xl:shrink-0"
>

    {{-- Close button (mobile only) --}}
    <div class="flex justify-end xl:hidden mb-2">
        <button
            id="sidebarClose"
            type="button"
            aria-label="Close menu"
            class="inline-flex items-center justify-center rounded-2xl bg-slate-800 p-2.5 text-slate-300 transition hover:text-white"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Brand --}}
    <div class="mb-10">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-3xl bg-white p-1.5">
                <img src="/images/sgpro-logo.webp" alt="SGpro Logo" class="h-full w-full object-contain">
            </div>
            <div>
                <h1 class="text-lg font-semibold">{{ $role === 'admin' ? 'PCMS Admin' : 'PCMS Portal' }}</h1>
                <p class="text-sm text-slate-400">Project Coordination</p>
            </div>
        </div>
        <div class="mt-6 rounded-3xl border border-slate-800 bg-slate-900 p-4">
            <div class="text-sm text-slate-400">Signed in as</div>
            <div class="mt-2 text-base font-semibold text-white">{{ auth()->user()->name }}</div>
            <div class="text-sm text-slate-500">{{ auth()->user()->role }}</div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="space-y-4">
        <div class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Navigation</div>
        <nav class="space-y-2">

            {{-- ── ADMIN ──────────────────────────────────────────────────── --}}
            @if($role === 'admin')
                @php
                    $onProjects = request()->routeIs('projects.*') || request()->routeIs('admin.dashboard') === false && str_starts_with(request()->path(), 'projects');
                @endphp
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-100' }}">🏠</span>
                    Dashboard
                </a>
                <a href="{{ route('projects.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('projects.*') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('projects.*') ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">📁</span>
                    Projects
                </a>
                <a href="{{ route('admin.tasks.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.tasks.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('admin.tasks.index') ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">✅</span>
                    Tasks
                </a>
                <a href="{{ route('admin.activity-log.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.activity-log.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('admin.activity-log.index') ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-100' }}">📋</span>
                    Activity Log
                </a>
                <a href="{{ route('admin.report.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.report.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('admin.report.index') ? 'bg-amber-500 text-white' : 'bg-slate-800 text-slate-100' }}">📊</span>
                    Reports
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.users.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('admin.users.index') ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-100' }}">👥</span>
                    Manage Users
                </a>

            {{-- ── PROJECT MANAGER ─────────────────────────────────────────── --}}
            @elseif($role === 'pm')
                @php $pmProjectsActive = request()->routeIs('pm.projects') || request()->routeIs('projects.*'); @endphp
                <a href="{{ route('pm.dashboard') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('pm.dashboard') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('pm.dashboard') ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-100' }}">🏠</span>
                    Dashboard
                </a>
                <a href="{{ route('pm.projects') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ $pmProjectsActive ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $pmProjectsActive ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">📁</span>
                    Projects
                </a>
                <a href="{{ route('pm.tasks.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('pm.tasks.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('pm.tasks.index') ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">✅</span>
                    Tasks
                </a>
                <a href="{{ route('pm.activity-log.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('pm.activity-log.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('pm.activity-log.index') ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-100' }}">📋</span>
                    Activity Log
                </a>
                <a href="{{ route('pm.report.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('pm.report.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('pm.report.index') ? 'bg-amber-500 text-white' : 'bg-slate-800 text-slate-100' }}">📊</span>
                    Reports
                </a>

            {{-- ── CLIENT ──────────────────────────────────────────────────── --}}
            @elseif($role === 'client')
                @php $clientProjectsActive = request()->routeIs('client.projects') || request()->routeIs('client.projects.show'); @endphp
                <a href="{{ route('client.dashboard') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('client.dashboard') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('client.dashboard') ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-100' }}">🏠</span>
                    Dashboard
                </a>
                <a href="{{ route('client.projects') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ $clientProjectsActive ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $clientProjectsActive ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">📁</span>
                    Projects
                </a>
                <a href="{{ route('client.tasks.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('client.tasks.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('client.tasks.index') ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">✅</span>
                    Tasks
                </a>

            {{-- ── DIGITAL MARKETER ────────────────────────────────────────── --}}
            @elseif($role === 'dm')
                @php $dmProjectsActive = request()->routeIs('dm.projects') || request()->routeIs('dm.projects.show'); @endphp
                <a href="{{ route('dm.dashboard') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('dm.dashboard') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('dm.dashboard') ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-100' }}">🏠</span>
                    Dashboard
                </a>
                <a href="{{ route('dm.projects') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ $dmProjectsActive ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $dmProjectsActive ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">📁</span>
                    Projects
                </a>
                <a href="{{ route('dm.tasks.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('dm.tasks.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('dm.tasks.index') ? 'bg-sky-500 text-white' : 'bg-slate-800 text-slate-100' }}">✅</span>
                    Tasks
                </a>
                <a href="{{ route('dm.report.index') }}"
                   class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('dm.report.index') ? $activeClass : $inactiveClass }}">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('dm.report.index') ? 'bg-amber-500 text-white' : 'bg-slate-800 text-slate-100' }}">📊</span>
                    Reports
                </a>
            @endif

        </nav>
    </div>

    {{-- Logout (non-admin roles only — admin logout lives in their page headers) --}}
    @if($role !== 'admin')
    <div class="mt-10 border-t border-slate-800 pt-6">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🚪</span>
                Logout
            </button>
        </form>
    </div>
    @endif

</aside>
