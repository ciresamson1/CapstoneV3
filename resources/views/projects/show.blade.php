@extends('layouts.app')

@section('content')

<h2 class="mb-3">{{ $project->name }}</h2>

<div class="card mb-4 p-3">
    <h5>Project Progress</h5>
    <div class="progress">
        <div class="progress-bar bg-success" style="width: {{ $project->progress }}%">
            {{ $project->progress }}%
        </div>
    </div>
</div>

<div class="card mb-4 p-3">
    <h4>Timeline</h4>
    <div id="gantt" style="width:100%; height:300px;"></div>
</div>

@foreach($project->tasks as $task)

<div class="card mb-3 p-3">

    <div class="d-flex align-items-center mb-2">
        <input type="checkbox"
               class="form-check-input me-2 task-checkbox"
               data-task="{{ $task->id }}"
               {{ $task->progress == 100 ? 'checked' : '' }}>

        <strong>{{ $task->title }}</strong>
    </div>

    <!-- COMMENTS -->
    <div class="mt-3">

        <div class="border rounded p-2 mb-2" style="max-height:200px;overflow-y:auto">

            @foreach($task->comments as $comment)

                <div class="mb-2">

                    <strong>
                        {{ $comment->user->name }}
                        ({{ ucfirst($comment->user->role) }})
                    </strong>

                    <small class="text-muted">
                        {{ $comment->created_at->diffForHumans() }}
                    </small>

                    <div>{{ $comment->message }}</div>

                  @if($comment->attachment)
                        <div class="mt-2">

                            @php
                                $file = asset('storage/'.$comment->attachment);
                                $ext = strtolower(pathinfo($comment->attachment, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            @endphp

                            {{-- IMAGE PREVIEW --}}
                            @if($isImage)
                                <div class="mb-2">
                                    <a href="{{ $file }}" target="_blank">
                                        <img src="{{ $file }}"
                                            style="max-width:200px; border-radius:8px; border:1px solid #ddd;">
                                    </a>
                                </div>
                            @endif

                            {{-- FILE NAME --}}
                            <div class="small text-muted mb-1">
                                {{ basename($comment->attachment) }}
                            </div>

                            {{-- DOWNLOAD BUTTON --}}
                            <a href="{{ $file }}"
                            download
                            class="btn btn-sm btn-outline-primary">
                                Download Attachment
                            </a>

                        </div>
                    @endif

                </div>

            @endforeach

        </div>

        <form method="POST"
              action="{{ route('tasks.comments.store',$task->id) }}"
              enctype="multipart/form-data">

            @csrf

            <div class="input-group">
                <input type="text"
                       name="message"
                       class="form-control"
                       placeholder="Write comment...">

                <input type="file"
                       name="file"
                       class="form-control">

                <button class="btn btn-primary">
                    Send
                </button>
            </div>

        </form>

    </div>

</div>

@endforeach

<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.umd.js"></script>

<style>
.bar-green rect { fill: #28a745 !important; }
.bar-yellow rect { fill: #ffc107 !important; }
.bar-red rect { fill: #dc3545 !important; }
.bar-grey rect { fill: #6c757d !important; }
</style>

<script>
window.onload = function () {

    const today = new Date();

    const tasks = [
        @foreach($project->tasks as $task)
        {
            id: 'task-{{ $task->id }}',
            name: '{{ $task->title }}',
            start: '{{ $task->start_date }}',
            end: '{{ $task->end_date }}',
            progress: {{ $task->progress }},
            custom_class: (function() {

                let end = new Date('{{ $task->end_date }}');
                let progress = {{ $task->progress }};
                let diff = (end - today) / (1000*60*60*24);

                if (progress == 100) return 'bar-green';
                if (end < today && progress < 100) return 'bar-red';
                if (diff <= 2 && diff >= 0) return 'bar-yellow';

                return 'bar-grey';
            })()
        },
        @endforeach
    ];

    if (tasks.length > 0) {
        const gantt = new Gantt("#gantt", tasks, { view_mode: 'Day' });

        document.querySelectorAll('#gantt svg').forEach(svg => {
            svg.style.pointerEvents = 'none';
        });
    }

    document.querySelectorAll('.task-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            fetch(`/tasks/${this.dataset.task}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => location.reload());
        });
    });

};
</script>

@endsection