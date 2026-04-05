@extends('layouts.app')

@section('content')
<h2>{{ $project->name }}</h2>

@foreach($project->tasks as $task)
    <div class="card mb-3">
        <h4>{{ $task->title }}</h4>

        <div class="progress mb-2">
            <div class="progress-bar" style="width: {{ $task->progress }}%">
                {{ $task->progress }}%
            </div>
        </div>

        @foreach($task->subTasks as $sub)
            <div class="form-check">
                <input 
                    type="checkbox" 
                    class="form-check-input subtask-checkbox" 
                    data-id="{{ $sub->id }}"
                    {{ $sub->is_completed ? 'checked' : '' }}
                >
                <label>{{ $sub->title }}</label>
            </div>
        @endforeach
    </div>
@endforeach

<script>
document.querySelectorAll('.subtask-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        fetch(`/subtasks/${this.dataset.id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    });
});
</script>
@endsection