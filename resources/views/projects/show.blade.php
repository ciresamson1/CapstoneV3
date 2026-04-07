@extends('layouts.admin')

@section('content')

@php
    use Carbon\Carbon;
    $today     = Carbon::today();
    $total     = $project->tasks->count();
    $done      = $project->tasks->where('progress', 100)->count();
    $percent   = $total ? round(($done / $total) * 100) : 0;
    $overdue   = $project->tasks->filter(fn($t) => $t->end_date && Carbon::parse($t->end_date)->lt($today) && $t->progress < 100)->count();
    $inProg    = $project->tasks->whereBetween('progress', [1, 99])->count();
@endphp

<div class="min-h-screen overflow-x-hidden bg-slate-100">
    <div class="flex min-h-screen flex-col xl:flex-row">

        {{-- Sidebar --}}
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
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pm.dashboard') }}" class="flex items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">🏠</span>
                        Dashboard
                    </a>
                    <a href="{{ route('projects.index') }}" class="flex items-center gap-3 rounded-3xl bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-500 text-white">📁</span>
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
                    @if(auth()->user()->role === 'admin')
                    <button id="openAssignRoleModal" type="button" class="flex w-full items-center gap-3 rounded-3xl px-4 py-3 text-sm font-medium transition hover:bg-slate-800 text-slate-300">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-slate-100">👥</span>
                        Assign Role
                    </button>
                    @endif
                </nav>
            </div>
        </aside>

        {{-- Main --}}
        <main class="flex-1 min-w-0 p-6 xl:p-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <a href="{{ route('projects.index') }}" class="mb-2 inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-700">
                        ← Back to Projects
                    </a>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ $project->name }}</h2>
                    @if($project->description)
                        <p class="mt-1 text-sm text-slate-500">{{ $project->description }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span>{{ $project->start_date ? Carbon::parse($project->start_date)->format('M d, Y') : '—' }} → {{ $project->end_date ? Carbon::parse($project->end_date)->format('M d, Y') : '—' }}</span>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                            {{ $project->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($project->status === 'on_hold' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                        <span class="text-slate-400">Owner: {{ $project->creator?->name ?? 'Unassigned' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button id="openCreateTaskModal" type="button" class="rounded-3xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">+ Add Task</button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Logout</button>
                    </form>
                </div>
            </div>

            {{-- Summary cards --}}
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-3xl bg-white p-5 shadow-sm border border-slate-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Progress</p>
                    <p class="mt-3 text-3xl font-bold {{ $percent >= 75 ? 'text-emerald-600' : ($percent >= 40 ? 'text-amber-600' : 'text-slate-900') }}">{{ $percent }}%</p>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full {{ $percent >= 75 ? 'bg-emerald-500' : ($percent >= 40 ? 'bg-amber-500' : 'bg-sky-500') }}" style="width:{{ $percent }}%"></div>
                    </div>
                </div>
                <div class="rounded-3xl bg-white p-5 shadow-sm border border-slate-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total Tasks</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $total }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $done }} completed</p>
                </div>
                <div class="rounded-3xl bg-white p-5 shadow-sm border border-slate-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">In Progress</p>
                    <p class="mt-3 text-3xl font-bold text-sky-600">{{ $inProg }}</p>
                    <p class="mt-1 text-xs text-slate-400">active tasks</p>
                </div>
                <div class="rounded-3xl bg-white p-5 shadow-sm border border-slate-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Overdue</p>
                    <p class="mt-3 text-3xl font-bold {{ $overdue > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $overdue }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $overdue > 0 ? 'past due date' : 'all on track' }}</p>
                </div>
            </div>

            {{-- Gantt timeline --}}
            <div class="mb-6 rounded-3xl bg-white p-6 shadow-sm">
                @if(session('task_created'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('task_created') }}</div>
                @endif
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Timeline</h3>
                        <p class="text-sm text-slate-500">Gantt view of all tasks.</p>
                    </div>
                </div>
                <div id="gantt" class="overflow-x-auto"></div>
            </div>

            {{-- Task checklist --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 text-lg font-semibold text-slate-900">Tasks &amp; Comments</h3>

                @forelse($project->tasks as $task)
                    @php
                        $taskProgress  = $task->progress;
                        $taskStatus    = $taskProgress >= 100 ? 'completed' : ($taskProgress > 0 ? 'in-progress' : 'not-started');
                        $taskEndDate   = $task->end_date ? Carbon::parse($task->end_date) : null;
                        $taskOverdue   = $taskEndDate && $taskEndDate->lt($today) && $taskProgress < 100;
                        $taskComments  = $task->comments->whereNull('parent_id')->sortBy('created_at');
                        $lastComment   = $taskComments->last();
                        $hasRecentComment = $lastComment && $lastComment->created_at->gt(now()->subHours(24));
                        $headerBg = $taskOverdue ? 'bg-rose-50 border-rose-200' : ($taskProgress >= 100 ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-slate-200');
                    @endphp

                    <div id="task-wrapper-{{ $task->id }}" class="mb-5 overflow-hidden rounded-2xl shadow-sm border {{ $taskOverdue ? 'border-rose-200' : ($taskProgress >= 100 ? 'border-emerald-200' : 'border-slate-200') }}">

                        {{-- ── LIGHT HEADER ── --}}
                        <div id="task-header-{{ $task->id }}" class="{{ $headerBg }} p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-start gap-3">
                                    {{-- Checkbox --}}
                                    <button id="task-checkbox-{{ $task->id }}" onclick="toggleTask({{ $task->id }})"
                                        class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition
                                            {{ $taskProgress >= 100 ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white hover:border-emerald-500' }}">
                                        @if($taskProgress >= 100)
                                            <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none">
                                                <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @endif
                                    </button>
                                    <div class="min-w-0">
                                            <div id="task-title-{{ $task->id }}" class="font-semibold text-slate-900 {{ $taskProgress >= 100 ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</div>
                                        @if($task->description)
                                            <p class="mt-1 text-xs text-slate-500">{{ $task->description }}</p>
                                        @endif
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            @if($task->assignedTo)
                                                <span class="flex items-center gap-1 text-xs text-slate-500">
                                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">{{ strtoupper(substr($task->assignedTo->name, 0, 1)) }}</span>
                                                    {{ $task->assignedTo->name }}
                                                </span>
                                            @endif
                                            @if($taskEndDate)
                                                <span class="text-xs {{ $taskOverdue ? 'font-semibold text-rose-600' : 'text-slate-500' }}">
                                                    Due {{ $taskEndDate->format('M d, Y') }}{{ $taskOverdue ? ' · Overdue' : '' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    {{-- Pulsing red dot for recent comment --}}
                                    @if($hasRecentComment)
                                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                        </span>
                                    @endif

                                    {{-- Status badge --}}
                                    @php
                                        // Progress takes priority — a checked task is always "Completed"
                                        $taskDbStatus = $task->progress >= 100 ? 'completed' : ($task->status ?? 'pending');
                                        [$badgeBg, $badgeText, $badgeLabel] = match($taskDbStatus) {
                                            'completed'   => ['bg-emerald-100', 'text-emerald-700', 'Completed'],
                                            'in_progress' => ['bg-sky-100',     'text-sky-700',     'In Progress'],
                                            default       => $taskOverdue
                                                ? ['bg-rose-100', 'text-rose-700', 'Overdue']
                                                : ['bg-slate-100', 'text-slate-600', 'Pending'],
                                        };
                                    @endphp
                                    <span id="status-badge-{{ $task->id }}" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeBg }} {{ $badgeText }}">{{ $badgeLabel }}</span>

                                    {{-- Toggle comments button --}}
                                    <button type="button" onclick="toggleComments({{ $task->id }})"
                                        id="toggle-btn-{{ $task->id }}"
                                        class="flex items-center gap-1.5 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                                        <svg id="toggle-icon-{{ $task->id }}" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 16 16">
                                            <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span id="toggle-label-{{ $task->id }}">{{ $taskComments->count() }} comment{{ $taskComments->count() !== 1 ? 's' : '' }}</span>
                                    </button>

                                    {{-- Edit task button --}}
                                    <button type="button"
                                        onclick="openEditTask(
                                            {{ $task->id }},
                                            '{{ addslashes($task->title) }}',
                                            '{{ addslashes($task->description ?? '') }}',
                                            '{{ $task->assigned_to ?? '' }}',
                                            '{{ $task->start_date }}',
                                            '{{ $task->end_date }}',
                                            {{ $task->progress }},
                                            '{{ $task->status ?? 'pending' }}'
                                        )"
                                        class="flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-500">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 16 16">
                                            <path d="M11.5 2.5a1.414 1.414 0 012 2L5 13H3v-2L11.5 2.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Edit
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ── CHAT SECTION ── --}}
                        <div id="comments-section-{{ $task->id }}" class="border-t border-slate-100 bg-slate-50">

                            {{-- Message bubbles --}}
                            <div id="task-comments-{{ $task->id }}" class="max-h-80 space-y-3 overflow-y-auto px-4 pb-2 pt-4">
                                @forelse($taskComments as $comment)
                                    @php
                                        $isMe     = $comment->user_id === auth()->id();
                                        $ups      = $comment->reactions->where('type', 'up')->count();
                                        $downs    = $comment->reactions->where('type', 'down')->count();
                                        $myReact  = $comment->reactions->where('user_id', auth()->id())->first()?->type;
                                    @endphp
                                    <div class="comment-bubble flex {{ $isMe ? 'justify-end' : 'justify-start' }} {{ !$loop->last ? 'older-comment' : '' }}"
                                         style="{{ !$loop->last ? 'display:none' : '' }}"
                                         data-task="{{ $task->id }}">
                                        @if(!$isMe)
                                            <span class="mr-2 mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600">
                                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <div class="max-w-[75%]">
                                            <div class="mb-1 flex items-center gap-2 {{ $isMe ? 'justify-end' : '' }}">
                                                <span class="text-xs font-semibold {{ $isMe ? 'text-emerald-600' : 'text-slate-700' }}">{{ $comment->user->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $comment->created_at->format('M d · h:i A') }}</span>
                                            </div>
                                            <div class="rounded-2xl px-4 py-2.5 text-sm {{ $isMe ? 'rounded-tr-sm bg-emerald-600 text-white' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-800' }}">
                                                @if($comment->message)
                                                    <p>{{ $comment->message }}</p>
                                                @endif
                                                @if($comment->attachment)
                                                    <div class="{{ $comment->message ? 'mt-2' : '' }}">
                                                        <img src="{{ asset('storage/' . $comment->attachment) }}" class="max-h-40 rounded-xl object-cover" alt="attachment">
                                                        <a href="{{ route('task-comments.download', $comment->id) }}"
                                                            class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold underline {{ $isMe ? 'text-emerald-200' : 'text-slate-500' }} hover:no-underline">
                                                            ↓ Download
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- Reaction buttons --}}
                                            <div class="mt-1.5 flex items-center gap-2 {{ $isMe ? 'justify-end' : '' }}"
                                                 id="reactions-{{ $comment->id }}">
                                                <button type="button"
                                                    onclick="reactComment({{ $comment->id }}, 'up')"
                                                    id="btn-up-{{ $comment->id }}"
                                                    class="reaction-btn inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs transition
                                                        {{ $myReact === 'up' ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'bg-slate-100 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                                                    👍 <span id="up-count-{{ $comment->id }}">{{ $ups > 0 ? $ups : '' }}</span>
                                                </button>
                                                <button type="button"
                                                    onclick="reactComment({{ $comment->id }}, 'down')"
                                                    id="btn-down-{{ $comment->id }}"
                                                    class="reaction-btn inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs transition
                                                        {{ $myReact === 'down' ? 'bg-rose-100 text-rose-700 font-semibold' : 'bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600' }}">
                                                    👎 <span id="down-count-{{ $comment->id }}">{{ $downs > 0 ? $downs : '' }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        @if($isMe)
                                            <span class="ml-2 mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">
                                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="py-4 text-center text-xs text-slate-400" id="no-comments-{{ $task->id }}">No comments yet. Start the conversation.</p>
                                @endforelse
                            </div>

                            {{-- Input bar --}}
                            <form id="comment-form-{{ $task->id }}" data-task-id="{{ $task->id }}" method="POST" action="{{ route('tasks.comments.store', $task->id) }}" enctype="multipart/form-data"
                                class="comment-form-ajax border-t border-slate-100 px-4 py-3">
                                @csrf
                                <div class="flex items-center gap-2">
                                    <input type="text" name="message" placeholder="Write a message…"
                                        class="flex-1 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    <label class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50" title="Attach file">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <input type="file" name="attachment" class="hidden" onchange="updateFileLabel(this, {{ $task->id }})">
                                    </label>
                                    <span id="file-name-{{ $task->id }}" class="hidden max-w-[80px] truncate text-[11px] text-slate-500"></span>
                                    <button type="submit" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm transition hover:bg-emerald-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">No tasks yet. Click <strong>+ Add Task</strong> to get started.</p>
                @endforelse
            </div>

        </main>
    </div>
</div>

{{-- Assign Role Modal --}}
@if(auth()->user()->role === 'admin')
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

{{-- Create Task Modal --}}
<div id="createTaskModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-900">Add a task</h3>
                <p class="mt-1 text-sm text-slate-500">Adding to <span class="font-semibold">{{ $project->name }}</span></p>
            </div>
            <button id="closeCreateTaskModal" type="button" class="rounded-3xl border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>

        @if($errors->hasAny(['title','start_date','end_date']))
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tasks.store', $project->id) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Task title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Keyword research" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="3" placeholder="Optional details…" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 resize-none">{{ old('description') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Assign to</label>
                <select name="assigned_to" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Task Status</label>
                <select name="status" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="pending"     {{ old('status', 'pending') === 'pending'     ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ old('status') === 'in_progress'            ? 'selected' : '' }}>In Progress</option>
                    <option value="completed"   {{ old('status') === 'completed'              ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Start date <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">End date <span class="text-rose-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full rounded-3xl bg-emerald-500 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">Create Task</button>
            </div>
        </form>
    </div>
</div>

{{-- Gantt CSS --}}
<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">
<style>
    #gantt svg { border-radius: 1rem; }
    .bar-wrapper.bar-green .bar { fill: #10b981 !important; }
    .bar-wrapper.bar-yellow .bar { fill: #f59e0b !important; }
    .bar-wrapper.bar-red    .bar { fill: #ef4444 !important; }
    .bar-wrapper.bar-grey   .bar { fill: #94a3b8 !important; }
    svg .bar-green .bar { fill: #10b981 !important; }
    svg .bar-yellow .bar { fill: #f59e0b !important; }
    svg .bar-red    .bar { fill: #ef4444 !important; }
    svg .bar-grey   .bar { fill: #94a3b8 !important; }
</style>

<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.umd.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    @php
        $commentIdsByTask = [];
        $latestCommentTimestamp = null;
        foreach ($project->tasks as $task) {
            $comments = $task->comments->whereNull('parent_id')->sortBy('created_at');
            $commentIdsByTask[$task->id] = $comments->pluck('id')->all();
            foreach ($comments as $comment) {
                if (!$latestCommentTimestamp || $comment->created_at > $latestCommentTimestamp) {
                    $latestCommentTimestamp = $comment->created_at;
                }
            }
        }
    @endphp

    let knownCommentIds      = {!! json_encode($commentIdsByTask) !!};
    let lastCommentTimestamp = '{{ $latestCommentTimestamp ? $latestCommentTimestamp->toISOString() : now()->toISOString() }}';

    // ── Gantt ──────────────────────────────────────────────────────────────
    let tasks = [
        @foreach($project->tasks as $task)
        @php
            $endDate           = Carbon::parse($task->end_date);
            $daysUntilDeadline = $today->diffInDays($endDate, false);
            if ($task->progress == 100)                                       $barClass = 'bar-green';
            elseif ($endDate < $today)                                        $barClass = 'bar-red';
            elseif ($task->progress < 100 && $daysUntilDeadline <= 3 && $daysUntilDeadline >= 0) $barClass = 'bar-yellow';
            else                                                              $barClass = 'bar-grey';
        @endphp
        { id: 'task-{{ $task->id }}', name: '{{ addslashes($task->title) }}', start: '{{ $task->start_date }}', end: '{{ $task->end_date }}', progress: {{ $task->progress }}, custom_class: '{{ $barClass }}' },
        @endforeach
    ];

    if (tasks.length) {
        const gantt = new Gantt('#gantt', tasks, { view_mode: 'Day', readonly: true });

        setTimeout(() => {
            const colorMap = { 'bar-green': '#10b981', 'bar-yellow': '#f59e0b', 'bar-red': '#ef4444', 'bar-grey': '#94a3b8' };
            document.querySelectorAll('[class*="bar-green"],[class*="bar-yellow"],[class*="bar-red"],[class*="bar-grey"]').forEach(w => {
                const cls = w.className.baseVal || w.className;
                const color = Object.entries(colorMap).find(([k]) => cls.includes(k))?.[1];
                if (color) {
                    const el = w.querySelector('.bar') || w.querySelector('rect');
                    if (el) { el.setAttribute('fill', color); el.style.fill = color; }
                }
            });
        }, 100);
    } else {
        document.getElementById('gantt').innerHTML = '<p class="py-4 text-center text-sm text-slate-400">No tasks to display yet.</p>';
    }

    // ── Toggle comments show/hide ─────────────────────────────────────────
    const toggleStates = {};
    window.toggleComments = function(taskId) {
        const older  = document.querySelectorAll(`.older-comment[data-task="${taskId}"]`);
        const icon   = document.getElementById(`toggle-icon-${taskId}`);
        const label  = document.getElementById(`toggle-label-${taskId}`);
        toggleStates[taskId] = !toggleStates[taskId];
        if (toggleStates[taskId]) {
            older.forEach(el => el.style.display = '');
            if (icon)  icon.style.transform = 'rotate(180deg)';
            if (label) label.textContent = 'Hide older';
            // scroll to bottom
            const c = document.getElementById(`task-comments-${taskId}`);
            if (c) c.scrollTop = c.scrollHeight;
        } else {
            older.forEach(el => el.style.display = 'none');
            if (icon)  icon.style.transform = '';
            const total = document.querySelectorAll(`#task-comments-${taskId} .comment-bubble`).length;
            if (label) label.textContent = `${total} comment${total !== 1 ? 's' : ''}`;
        }
    };

    // ── Attach file label ─────────────────────────────────────────────────
    window.updateFileLabel = function(input, taskId) {
        const span = document.getElementById(`file-name-${taskId}`);
        if (!span) return;
        if (input.files[0]) {
            span.textContent = input.files[0].name;
            span.classList.remove('hidden');
        } else {
            span.textContent = '';
            span.classList.add('hidden');
        }
    };

    // ── Comment append helper ─────────────────────────────────────────────
    const appendComment = (c) => {
        const container = document.getElementById('task-comments-' + c.task_id);
        if (!container) return;

        // Remove empty-state placeholder
        const placeholder = document.getElementById('no-comments-' + c.task_id);
        if (placeholder) placeholder.remove();

        const isMe = c.user_id === {{ auth()->id() }};
        const expanded = !!toggleStates[c.task_id];

        // Mark previous last bubble as older (hide if not expanded)
        const prev = container.querySelector('.comment-bubble:last-child');
        if (prev) {
            prev.classList.add('older-comment');
            prev.dataset.task = c.task_id;
            if (!expanded) prev.style.display = 'none';
        }

        const div = document.createElement('div');
        div.className = `comment-bubble flex ${isMe ? 'justify-end' : 'justify-start'}`;
        div.dataset.task = c.task_id;
        div.innerHTML = `
            ${!isMe ? `<span class="mr-2 mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600">${c.user_name.charAt(0).toUpperCase()}</span>` : ''}
            <div class="max-w-[75%]">
                <div class="mb-1 flex items-center gap-2 ${isMe ? 'justify-end' : ''}">
                    <span class="text-xs font-semibold ${isMe ? 'text-emerald-600' : 'text-slate-700'}">${c.user_name}</span>
                    <span class="text-[10px] text-slate-400">${c.created_at}</span>
                </div>
                <div class="rounded-2xl px-4 py-2.5 text-sm ${isMe ? 'rounded-tr-sm bg-emerald-600 text-white' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-800'}">
                    ${c.message ? `<p>${c.message}</p>` : ''}
                    ${c.attachment ? `<div class="${c.message ? 'mt-2' : ''}">
                        <img src="/storage/${c.attachment}" class="max-h-40 rounded-xl object-cover">
                        <a href="/task-comments/${c.id}/download" class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold underline ${isMe ? 'text-emerald-200' : 'text-slate-500'} hover:no-underline">↓ Download</a>
                    </div>` : ''}
                </div>
            </div>
            ${isMe ? `<span class="ml-2 mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">${c.user_name.charAt(0).toUpperCase()}</span>` : ''}
        `;
        container.appendChild(div);

        // Update toggle label count
        if (!expanded) {
            const total = container.querySelectorAll('.comment-bubble').length;
            const lbl = document.getElementById(`toggle-label-${c.task_id}`);
            if (lbl) lbl.textContent = `${total} comment${total !== 1 ? 's' : ''}`;
        }

        // Scroll to latest
        container.scrollTop = container.scrollHeight;
    };

    // ── Polling ───────────────────────────────────────────────────────────
    const pollComments = () => {
        fetch("{{ route('projects.comments.poll', $project->id) }}?after=" + encodeURIComponent(lastCommentTimestamp))
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data)) return;
                data.forEach(c => {
                    knownCommentIds[c.task_id] = knownCommentIds[c.task_id] ?? [];
                    if (!knownCommentIds[c.task_id].includes(c.id)) {
                        knownCommentIds[c.task_id].push(c.id);
                        appendComment(c);
                        lastCommentTimestamp = c.created_at;
                    }
                });
            });
    };
    setInterval(pollComments, 5000);

    // ── WebSocket ─────────────────────────────────────────────────────────
    const ch = window.Echo?.channel('project.{{ $project->id }}');
    if (ch) {
        const handleNew = (e) => {
            let c = e.comment;
            knownCommentIds[c.task_id] = knownCommentIds[c.task_id] ?? [];
            if (!knownCommentIds[c.task_id].includes(c.id)) {
                knownCommentIds[c.task_id].push(c.id);
                appendComment(c);
                lastCommentTimestamp = c.created_at;
            }
        };
        ch.listen('task.comment.created', handleNew);
        ch.listen('.task.comment.created', handleNew);
    }

    // ── Assign Role modal ─────────────────────────────────────────────────
    const modal = document.getElementById('assignRoleModal');
    if (modal) {
        document.getElementById('openAssignRoleModal').addEventListener('click', () => modal.classList.remove('hidden'));
        document.getElementById('closeAssignRoleModal').addEventListener('click', () => modal.classList.add('hidden'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
    }

    // ── Create Task modal ─────────────────────────────────────────────────
    const taskModal = document.getElementById('createTaskModal');
    document.getElementById('openCreateTaskModal').addEventListener('click', () => {
        taskModal.classList.remove('hidden');
        taskModal.classList.add('flex');
    });
    document.getElementById('closeCreateTaskModal').addEventListener('click', () => {
        taskModal.classList.add('hidden');
        taskModal.classList.remove('flex');
    });
    taskModal.addEventListener('click', e => {
        if (e.target === taskModal) {
            taskModal.classList.add('hidden');
            taskModal.classList.remove('flex');
        }
    });
    // Auto-reopen on validation error
    @if($errors->hasAny(['title','start_date','end_date']))
        taskModal.classList.remove('hidden');
        taskModal.classList.add('flex');
    @endif

    // ── Attach file label ─────────────────────────────────────────────────
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function () {
            const label = this.closest('label')?.querySelector('span');
            if (label) label.textContent = this.files[0]?.name ?? 'Attach';
        });
    });

    // ── Comment form AJAX submit (no full-page reload) ────────────────────
    document.querySelectorAll('.comment-form-ajax').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const taskId = this.dataset.taskId;
            const fd     = new FormData(this);
            const btn    = this.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.style.opacity = '0.6'; }

            fetch(this.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd,
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(c => {
                appendComment(c);
                knownCommentIds[c.task_id] = knownCommentIds[c.task_id] ?? [];
                knownCommentIds[c.task_id].push(c.id);
                lastCommentTimestamp = c.created_at;
                this.reset();
                const fileSpan = document.getElementById(`file-name-${taskId}`);
                if (fileSpan) { fileSpan.textContent = ''; fileSpan.classList.add('hidden'); }
            })
            .catch(() => { this.submit(); })
            .finally(() => { if (btn) { btn.disabled = false; btn.style.opacity = ''; } });
        });
    });
});

function toggleTask(id) {
    fetch(`/tasks/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    }).then(r => r.json()).then(data => {
        const completed = data.progress >= 100;

        // Checkbox button
        const cb = document.getElementById(`task-checkbox-${id}`);
        if (cb) {
            cb.className = `mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition ${
                completed ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white hover:border-emerald-500'
            }`;
            cb.innerHTML = completed
                ? '<svg class="h-3 w-3" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                : '';
        }

        // Status badge
        const badge = document.getElementById(`status-badge-${id}`);
        if (badge) {
            badge.className = `inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'
            }`;
            badge.textContent = completed ? 'Completed' : 'Pending';
        }

        // Title strikethrough
        const titleEl = document.getElementById(`task-title-${id}`);
        if (titleEl) {
            titleEl.classList.toggle('line-through', completed);
            titleEl.classList.toggle('text-slate-400', completed);
            titleEl.classList.toggle('text-slate-900', !completed);
        }

        // Header background
        const header = document.getElementById(`task-header-${id}`);
        if (header) {
            header.className = `p-5 ${completed ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-slate-200'}`;
        }

        // Wrapper border
        const wrapper = document.getElementById(`task-wrapper-${id}`);
        if (wrapper) {
            wrapper.className = `mb-5 overflow-hidden rounded-2xl shadow-sm border ${
                completed ? 'border-emerald-200' : 'border-slate-200'
            }`;
        }
    });
}

// ── Comment reactions ──────────────────────────────────────────────────────
window.reactComment = function(commentId, type) {
    fetch(`/task-comments/${commentId}/react`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ type }),
    })
    .then(r => r.json())
    .then(data => {
        // Update counts
        const upSpan   = document.getElementById(`up-count-${commentId}`);
        const downSpan = document.getElementById(`down-count-${commentId}`);
        if (upSpan)   upSpan.textContent   = data.ups   > 0 ? data.ups   : '';
        if (downSpan) downSpan.textContent = data.downs > 0 ? data.downs : '';

        // Update active styling
        const btnUp   = document.getElementById(`btn-up-${commentId}`);
        const btnDown = document.getElementById(`btn-down-${commentId}`);
        if (btnUp) {
            btnUp.className = btnUp.className
                .replace(/bg-emerald-100\s?text-emerald-700\s?font-semibold/g, '')
                .replace(/bg-slate-100\s?text-slate-500\s?hover:bg-emerald-50\s?hover:text-emerald-600/g, '')
                .trim();
            btnUp.classList.add(...(data.mine === 'up'
                ? ['bg-emerald-100', 'text-emerald-700', 'font-semibold']
                : ['bg-slate-100', 'text-slate-500', 'hover:bg-emerald-50', 'hover:text-emerald-600']));
        }
        if (btnDown) {
            btnDown.className = btnDown.className
                .replace(/bg-rose-100\s?text-rose-700\s?font-semibold/g, '')
                .replace(/bg-slate-100\s?text-slate-500\s?hover:bg-rose-50\s?hover:text-rose-600/g, '')
                .trim();
            btnDown.classList.add(...(data.mine === 'down'
                ? ['bg-rose-100', 'text-rose-700', 'font-semibold']
                : ['bg-slate-100', 'text-slate-500', 'hover:bg-rose-50', 'hover:text-rose-600']));
        }
    });
};
</script>

{{-- ── Edit Task Modal ────────────────────────────────────────────────────── --}}
<div id="editTaskModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-900">Edit task</h3>
                <p class="mt-1 text-sm text-slate-500">Update the task details below.</p>
            </div>
            <button id="closeEditTaskModal" type="button"
                class="rounded-3xl border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>

        <form id="editTaskForm" method="POST" action="" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Title <span class="text-rose-500">*</span></label>
                <input type="text" id="etask_title" name="title"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                <textarea id="etask_description" name="description" rows="3"
                    class="w-full resize-none rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Assigned to</label>
                <select id="etask_assigned_to" name="assigned_to"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="">— Unassigned —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Task Status</label>
                <select id="etask_status" name="status"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Start date <span class="text-rose-500">*</span></label>
                <input type="date" id="etask_start_date" name="start_date"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">End date <span class="text-rose-500">*</span></label>
                <input type="date" id="etask_end_date" name="end_date"
                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
            </div>

            <input type="hidden" id="etask_progress" name="progress" value="0">

            <div class="sm:col-span-2">
                <button type="submit"
                    class="w-full rounded-3xl bg-amber-400 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-500">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    const editTaskModal = document.getElementById('editTaskModal');
    document.getElementById('closeEditTaskModal').addEventListener('click', () => {
        editTaskModal.classList.add('hidden');
        editTaskModal.classList.remove('flex');
    });
    editTaskModal.addEventListener('click', e => {
        if (e.target === editTaskModal) {
            editTaskModal.classList.add('hidden');
            editTaskModal.classList.remove('flex');
        }
    });

    function openEditTask(id, title, description, assignedTo, startDate, endDate, progress, status) {
        document.getElementById('editTaskForm').action = `/tasks/${id}`;
        document.getElementById('etask_title').value       = title;
        document.getElementById('etask_description').value = description;
        document.getElementById('etask_assigned_to').value = assignedTo;
        document.getElementById('etask_start_date').value  = startDate;
        document.getElementById('etask_end_date').value    = endDate;
        document.getElementById('etask_progress').value = progress;
        document.getElementById('etask_status').value     = status || 'pending';
        editTaskModal.classList.remove('hidden');
        editTaskModal.classList.add('flex');
    }
</script>

@endsection
