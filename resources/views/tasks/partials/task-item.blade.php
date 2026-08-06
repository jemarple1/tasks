@props(['task'])

@php
    $category = $task->taskCategory;
    $categoryColor = $category?->color ?? '#1d4ed8';
    $categoryName = $category?->name ?? 'General';
@endphp

<div
    class="task-swipe-wrapper task-enter"
    data-task
    data-complete-url="{{ route('tasks.complete', $task) }}"
    data-snooze-url="{{ route('tasks.snooze', $task) }}"
    data-refresh-url="{{ route('tasks.refresh', $task) }}"
    data-update-url="{{ route('tasks.update', $task) }}"
    data-task-title="{{ e($task->title) }}"
    data-task-notes="{{ e($task->notes ?? '') }}"
    data-task-category-id="{{ $task->task_category_id }}"
    data-task-due-at="{{ $task->due_at?->toDateString() ?? '' }}"
    data-task-recurrence="{{ $task->recurrence }}"
    data-task-recurrence-until="{{ $task->recurrence_until?->toDateString() ?? '' }}"
>
    <div class="task-swipe-bg-left"><span>Remind tomorrow</span></div>
    <div class="task-swipe-bg-right"><span>+7 days</span></div>
    <div class="task-card {{ $task->isFromOtherUser() ? 'task-card-assigned' : '' }}">
        <button type="button" class="task-edit-area" aria-label="Edit task">
            <h3 class="task-card-title">{{ $task->title }}</h3>
            <div class="task-card-meta">
                <span class="task-card-category" style="color: {{ $categoryColor }}">{{ $categoryName }}</span>
                @if ($task->due_at)
                    <span class="task-meta-dot">•</span>
                    <span class="task-card-due">{{ $task->due_at->format('M j') }}</span>
                @endif
                <span class="task-meta-dot">•</span>
                <span class="task-card-meta-days">{{ $task->daysRemaining() }}d left</span>
                @if ($task->isRecurring())
                    <span class="task-meta-dot">•</span>
                    <span>↻ {{ $task->recurrence }}</span>
                @endif
                @if ($task->isFromOtherUser())
                    <span class="task-meta-dot">•</span>
                    <span>from {{ '@'.$task->creator->username }}</span>
                @endif
            </div>
            @if ($task->notes)
                <p class="task-card-notes">{{ $task->notes }}</p>
            @endif
        </button>
        <button type="button" class="task-complete-btn" aria-label="Complete task">
            <span class="task-complete-ring"></span>
        </button>
    </div>
</div>
