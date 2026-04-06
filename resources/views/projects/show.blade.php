@extends('layouts.app')

@section('content')
<div class="container">

<h2 class="mb-3">{{ $project->name }}</h2>

{{-- PROJECT PROGRESS --}}
<div class="card mb-4">
<div class="card-body">
<h5>Project Progress</h5>

@php
$total = $project->tasks->count();
$done = $project->tasks->where('progress',100)->count();
$percent = $total ? round(($done/$total)*100) : 0;
@endphp

<div class="progress">
<div class="progress-bar bg-success" style="width: {{ $percent }}%">
{{ $percent }}%
</div>
</div>

</div>
</div>



{{-- TIMELINE --}}
<div class="card mb-4">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="mb-0">Timeline</h5>

<a href="{{ route('tasks.create',$project->id) }}"
class="btn btn-primary btn-sm">
+ Add Task
</a>

</div>

<div id="gantt"></div>

</div>
</div>



{{-- TASK CHECKLIST --}}
<div class="card">
<div class="card-body">

<h5>Tasks Checklist</h5>

@foreach($project->tasks as $task)

<div class="border rounded p-3 mb-3">

<div class="form-check mb-2">
<input class="form-check-input"
type="checkbox"
onchange="toggleTask({{ $task->id }})"
{{ $task->progress == 100 ? 'checked' : '' }}>

<label class="form-check-label fw-bold">
{{ $task->title }}
</label>
</div>



{{-- COMMENTS --}}
@php
$comments = $task->comments
->whereNull('parent_id')
->sortBy('created_at');
@endphp

<div id="task-comments-{{ $task->id }}">

@foreach($comments as $comment)

<div class="border rounded p-2 mb-2">

<strong>
{{ $comment->user->name }}
({{ ucfirst($comment->user->role) }})
</strong>

<small class="text-muted">
{{ $comment->created_at->diffForHumans() }}
</small>

@if(!empty($comment->message))
<div class="mt-1">{{ $comment->message }}</div>
@endif


@if($comment->attachment)
<img src="{{ asset('storage/'.$comment->attachment) }}"
class="img-fluid mt-2"
style="max-height:150px">

<br>

<a href="{{ route('task-comments.download',$comment->id) }}"
class="btn btn-sm btn-outline-primary mt-1">
Download Attachment
</a>
@endif

</div>

@endforeach

</div>



{{-- ADD COMMENT --}}
<form method="POST"
action="{{ route('tasks.comments.store',$task->id) }}"
enctype="multipart/form-data">

@csrf

<div class="input-group mt-2">

<input type="text"
name="message"
class="form-control"
placeholder="Write comment...">

<input type="file"
name="attachment"
class="form-control">

<button class="btn btn-primary">
Send
</button>

</div>

</form>

</div>

@endforeach

</div>
</div>

</div>



<link rel="stylesheet"
href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">

<style>
/* Override Frappe Gantt bar colors based on custom_class */
.bar-wrapper.bar-green .bar {
    background-color: #28a745 !important;
    fill: #28a745 !important;
}

.bar-wrapper.bar-yellow .bar {
    background-color: #ffc107 !important;
    fill: #ffc107 !important;
}

.bar-wrapper.bar-red .bar {
    background-color: #dc3545 !important;
    fill: #dc3545 !important;
}

.bar-wrapper.bar-grey .bar {
    background-color: #6c757d !important;
    fill: #6c757d !important;
}

/* Alternative selectors for different Gantt versions */
svg .bar-green .bar {
    fill: #28a745 !important;
}

svg .bar-yellow .bar {
    fill: #ffc107 !important;
}

svg .bar-red .bar {
    fill: #dc3545 !important;
}

svg .bar-grey .bar {
    fill: #6c757d !important;
}
</style>

<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.umd.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

@php
    $commentIdsByTask = [];
    $latestCommentTimestamp = null;

    foreach ($project->tasks as $task) {
        $comments = $task->comments->whereNull('parent_id')->sortBy('created_at');
        $commentIdsByTask[$task->id] = $comments->pluck('id')->all();

        foreach ($comments as $comment) {
            if (! $latestCommentTimestamp || $comment->created_at > $latestCommentTimestamp) {
                $latestCommentTimestamp = $comment->created_at;
            }
        }
    }
@endphp

let knownCommentIds = {!! json_encode($commentIdsByTask) !!};
let lastCommentTimestamp = '{{ $latestCommentTimestamp ? $latestCommentTimestamp->toISOString() : now()->toISOString() }}';

@php
    use Carbon\Carbon;
    $today = Carbon::today();
@endphp

let tasks = [
@foreach($project->tasks as $task)
@php
    $endDate = Carbon::parse($task->end_date);
    $daysUntilDeadline = $today->diffInDays($endDate, false);
    
    if ($task->progress == 100) {
        $barClass = 'bar-green';
    } elseif ($endDate < $today) {
        $barClass = 'bar-red';
    } elseif ($task->progress < 100 && $daysUntilDeadline <= 3 && $daysUntilDeadline >= 0) {
        $barClass = 'bar-yellow';
    } else {
        $barClass = 'bar-grey';
    }
@endphp
{
id: 'task-{{ $task->id }}',
name: '{{ $task->title }}',
start: '{{ $task->start_date }}',
end: '{{ $task->end_date }}',
progress: {{ $task->progress }},
custom_class: "{{ $barClass }}"
},
@endforeach
];

const gantt = new Gantt("#gantt", tasks, {
view_mode: 'Day',
readonly: true
});

// Apply colors to bars based on custom_class
const colorMap = {
    'bar-green': '#28a745',
    'bar-yellow': '#ffc107',
    'bar-red': '#dc3545',
    'bar-grey': '#6c757d'
};

setTimeout(() => {
    // Target all bar groups/wrappers in the SVG
    const barWrappers = document.querySelectorAll('[class*="bar-green"], [class*="bar-yellow"], [class*="bar-red"], [class*="bar-grey"]');
    
    barWrappers.forEach((wrapper) => {
        const classNames = wrapper.className.baseVal || wrapper.className;
        let barColor = null;
        
        if (classNames.includes('bar-green')) {
            barColor = '#28a745';
        } else if (classNames.includes('bar-yellow')) {
            barColor = '#ffc107';
        } else if (classNames.includes('bar-red')) {
            barColor = '#dc3545';
        } else if (classNames.includes('bar-grey')) {
            barColor = '#6c757d';
        }
        
        if (barColor) {
            const barElement = wrapper.querySelector('.bar') || wrapper.querySelector('rect');
            if (barElement) {
                barElement.setAttribute('fill', barColor);
                barElement.style.fill = barColor;
            }
        }
    });
}, 100);

const appendComment = (c) => {
    let html = `
<div class="border rounded p-2 mb-2">
<strong>${c.user_name} (${c.user_role})</strong>
<small class="text-muted"> ${c.created_at}</small>
${c.message ? `<div class="mt-1">${c.message}</div>` : ''}
${c.attachment ? `
<img src="/storage/${c.attachment}" class="img-fluid mt-2" style="max-height:150px">
<br>
<a href="/task-comments/${c.id}/download" class="btn btn-sm btn-outline-primary mt-1">
Download Attachment
</a>
` : ''}
</div>
`;

    const container = document.getElementById('task-comments-' + c.task_id);
    if (container) {
        container.insertAdjacentHTML('beforeend', html);
    }
};

const pollComments = () => {
    fetch("{{ route('projects.comments.poll', $project->id) }}?after=" + encodeURIComponent(lastCommentTimestamp))
        .then((res) => res.json())
        .then((data) => {
            if (!Array.isArray(data)) {
                return;
            }

            data.forEach((c) => {
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

const projectChannel = window.Echo.channel('project.{{ $project->id }}');

const handleCommentCreated = (e) => {
    let c = e.comment;
    knownCommentIds[c.task_id] = knownCommentIds[c.task_id] ?? [];

    if (!knownCommentIds[c.task_id].includes(c.id)) {
        knownCommentIds[c.task_id].push(c.id);
        appendComment(c);
        lastCommentTimestamp = c.created_at;
    }
};

projectChannel.listen('task.comment.created', handleCommentCreated);
projectChannel.listen('.task.comment.created', handleCommentCreated);

});

function toggleTask(id)
{
fetch(`/tasks/${id}/toggle`,{
method:"POST",
headers:{
"X-CSRF-TOKEN":"{{ csrf_token() }}",
"Content-Type":"application/json"
}
}).then(()=>location.reload());
}
</script>

@endsection