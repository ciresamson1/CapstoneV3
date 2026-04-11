@php
    use Carbon\Carbon;
    $today            = Carbon::today();
    $taskProgress     = $task->progress;
    $taskEndDate      = $task->end_date ? Carbon::parse($task->end_date) : null;
    $taskOverdue      = $taskEndDate && $taskEndDate->lt($today) && $taskProgress < 100;
    $taskComments     = $task->comments->whereNull('parent_id')->sortBy('created_at');
    $lastComment      = $taskComments->last();
    $hasRecentComment = $lastComment && $lastComment->created_at->gt(now()->subHours(24));
    $headerBg         = $taskOverdue ? 'bg-rose-50 border-rose-200' : ($taskProgress >= 100 ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-slate-200');
@endphp

<div id="task-wrapper-{{ $task->id }}" class="mb-5 overflow-hidden rounded-2xl shadow-sm border {{ $taskOverdue ? 'border-rose-200' : ($taskProgress >= 100 ? 'border-emerald-200' : 'border-slate-200') }}">

    {{-- ── LIGHT HEADER ── --}}
    <div id="task-header-{{ $task->id }}" class="{{ $headerBg }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-start gap-3">
                {{-- Checkbox (hidden for clients) --}}
                @if(auth()->user()->role !== 'client' && auth()->user()->role !== 'dm')
                <button id="task-checkbox-{{ $task->id }}" onclick="toggleTask({{ $task->id }})"
                    class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition
                        {{ $taskProgress >= 100 ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 bg-white hover:border-emerald-500' }}">
                    @if($taskProgress >= 100)
                        <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none">
                            <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @endif
                </button>
                @endif
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
                @if(auth()->user()->role !== 'client' && auth()->user()->role !== 'dm')
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
                @endif
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
                <div class="comment-thread {{ !$loop->last ? 'older-comment' : '' }}"
                     style="{{ !$loop->last ? 'display:none' : '' }}"
                     data-task="{{ $task->id }}"
                     data-comment-id="{{ $comment->id }}">
                    <div class="comment-bubble flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        @if(!$isMe)
                            <span class="mr-2 mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600">
                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                            </span>
                        @endif
                        <div class="max-w-[85%]">
                            <div class="mb-1 flex items-center gap-2 {{ $isMe ? 'justify-end' : '' }}">
                                <span class="text-xs font-semibold {{ $isMe ? 'text-emerald-600' : 'text-slate-700' }}">{{ $comment->user->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $comment->created_at->format('M d · h:i A') }}</span>
                            </div>
                            <div class="rounded-2xl px-4 py-2.5 text-sm {{ $isMe ? 'rounded-tr-sm bg-emerald-600 text-white' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-800' }}">
                                @if($comment->message)
                                    <p class="whitespace-pre-line">{{ $comment->message }}</p>
                                @endif
                                @if($comment->link_url)
                                    <a href="{{ $comment->link_url }}" target="_blank" rel="noopener noreferrer"
                                       class="{{ $comment->message ? 'mt-3' : '' }} inline-flex max-w-full items-center gap-2 rounded-2xl border px-3 py-2 text-xs font-semibold {{ $isMe ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-50' : 'border-sky-200 bg-sky-50 text-sky-700' }} hover:opacity-90">
                                        <span class="truncate">{{ $comment->link_url }}</span>
                                        <span aria-hidden="true">↗</span>
                                    </a>
                                @endif
                                @if($comment->attachment)
                                    <div class="{{ $comment->message || $comment->link_url ? 'mt-3' : '' }} space-y-2">
                                        <img src="{{ asset('storage/' . $comment->attachment) }}" class="max-h-40 rounded-xl object-cover" alt="legacy attachment">
                                        <p class="text-[11px] {{ $isMe ? 'text-emerald-100' : 'text-slate-400' }}">Legacy file preview</p>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 {{ $isMe ? 'justify-end' : '' }}" id="reactions-{{ $comment->id }}">
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
                                <button type="button"
                                    onclick="toggleReplyForm({{ $comment->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-200">
                                    Reply
                                </button>
                            </div>
                            <div id="replies-{{ $comment->id }}" class="mt-3 space-y-3 border-l border-slate-200/80 pl-4 sm:pl-6">
                                @foreach($comment->replies as $reply)
                                    @php
                                        $replyIsMe    = $reply->user_id === auth()->id();
                                        $replyUps     = $reply->reactions->where('type', 'up')->count();
                                        $replyDowns   = $reply->reactions->where('type', 'down')->count();
                                        $replyMyReact = $reply->reactions->where('user_id', auth()->id())->first()?->type;
                                    @endphp
                                    <div class="comment-reply flex {{ $replyIsMe ? 'justify-end' : 'justify-start' }}" data-comment-id="{{ $reply->id }}">
                                        @if(!$replyIsMe)
                                            <span class="mr-2 mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">
                                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <div class="max-w-[82%]">
                                            <div class="mb-1 flex items-center gap-2 {{ $replyIsMe ? 'justify-end' : '' }}">
                                                <span class="text-[11px] font-semibold {{ $replyIsMe ? 'text-emerald-600' : 'text-slate-700' }}">{{ $reply->user->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $reply->created_at->format('M d · h:i A') }}</span>
                                            </div>
                                            <div class="rounded-2xl px-3.5 py-2.5 text-sm {{ $replyIsMe ? 'rounded-tr-sm bg-emerald-100 text-emerald-950' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-800' }}">
                                                @if($reply->message)
                                                    <p class="whitespace-pre-line">{{ $reply->message }}</p>
                                                @endif
                                                @if($reply->link_url)
                                                    <a href="{{ $reply->link_url }}" target="_blank" rel="noopener noreferrer"
                                                       class="{{ $reply->message ? 'mt-3' : '' }} inline-flex max-w-full items-center gap-2 rounded-2xl border px-3 py-2 text-xs font-semibold {{ $replyIsMe ? 'border-emerald-300 bg-white/60 text-emerald-800' : 'border-sky-200 bg-sky-50 text-sky-700' }} hover:opacity-90">
                                                        <span class="truncate">{{ $reply->link_url }}</span>
                                                        <span aria-hidden="true">↗</span>
                                                    </a>
                                                @endif
                                                @if($reply->attachment)
                                                    <div class="{{ $reply->message || $reply->link_url ? 'mt-3' : '' }} space-y-2">
                                                        <img src="{{ asset('storage/' . $reply->attachment) }}" class="max-h-36 rounded-xl object-cover" alt="legacy attachment">
                                                        <p class="text-[11px] {{ $replyIsMe ? 'text-emerald-700' : 'text-slate-400' }}">Legacy file preview</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="mt-1.5 flex flex-wrap items-center gap-2 {{ $replyIsMe ? 'justify-end' : '' }}" id="reactions-{{ $reply->id }}">
                                                <button type="button"
                                                    onclick="reactComment({{ $reply->id }}, 'up')"
                                                    id="btn-up-{{ $reply->id }}"
                                                    class="reaction-btn inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs transition
                                                        {{ $replyMyReact === 'up' ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'bg-slate-100 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                                                    👍 <span id="up-count-{{ $reply->id }}">{{ $replyUps > 0 ? $replyUps : '' }}</span>
                                                </button>
                                                <button type="button"
                                                    onclick="reactComment({{ $reply->id }}, 'down')"
                                                    id="btn-down-{{ $reply->id }}"
                                                    class="reaction-btn inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs transition
                                                        {{ $replyMyReact === 'down' ? 'bg-rose-100 text-rose-700 font-semibold' : 'bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600' }}">
                                                    👎 <span id="down-count-{{ $reply->id }}">{{ $replyDowns > 0 ? $replyDowns : '' }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        @if($replyIsMe)
                                            <span class="ml-2 mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white">
                                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <form id="reply-form-{{ $comment->id }}"
                                data-task-id="{{ $task->id }}"
                                data-parent-id="{{ $comment->id }}"
                                method="POST"
                                action="{{ route('tasks.comments.store', $task->id) }}"
                                class="comment-form-ajax mt-3 hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <div class="space-y-2">
                                    <input type="text" name="message" placeholder="Write a reply…"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="link_url" placeholder="Paste a link (optional)"
                                            class="flex-1 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                        <button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                                            Send
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @if($isMe)
                            <span class="ml-2 mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">
                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-4 text-center text-xs text-slate-400" id="no-comments-{{ $task->id }}">No comments yet. Start the conversation.</p>
            @endforelse
        </div>

        {{-- Input bar --}}
        <form id="comment-form-{{ $task->id }}" data-task-id="{{ $task->id }}" method="POST" action="{{ route('tasks.comments.store', $task->id) }}"
            class="comment-form-ajax border-t border-slate-100 px-4 py-3">
            @csrf
            <div class="space-y-2">
                <input type="text" name="message" placeholder="Write a message…"
                    class="w-full rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                <div class="flex items-center gap-2">
                    <input type="text" name="link_url" placeholder="Paste a link (optional)"
                        class="flex-1 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <button type="submit" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm transition hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
