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
    data-update-url="{{ route('tasks.update', $task) }}"
    data-task-title="{{ e($task->title) }}"
    data-task-notes="{{ e($task->notes ?? '') }}"
    data-task-category-id="{{ $task->task_category_id }}"
    data-task-recurrence="{{ $task->recurrence }}"
    data-task-recurrence-until="{{ $task->recurrence_until?->toDateString() ?? '' }}"
>
    <div class="task-swipe-bg-left"><span>Remind tomorrow</span></div>
    <div class="task-card {{ $task->isFromOtherUser() ? 'task-card-assigned' : '' }}">
        <button type="button" class="task-edit-area" aria-label="Edit task">
            <div class="task-card-top">
                <h3 class="task-card-title">{{ $task->title }}</h3>
                <span class="task-card-date">{{ $task->expires_at->format('M j') }}</span>
                <span class="task-card-category" style="color: {{ $categoryColor }}">{{ $categoryName }}</span>
            </div>
            <div class="task-card-meta">
                @if ($task->isRecurring())
                    <span>↻ {{ $task->recurrence }}</span>
                @endif
                @if ($task->isFromOtherUser())
                    @if ($task->isRecurring())<span class="task-meta-dot">•</span>@endif
                    <span>from {{ '@'.$task->creator->username }}</span>
                @endif
                @if ($task->notes)
                    @if ($task->isRecurring() || $task->isFromOtherUser())<span class="task-meta-dot">•</span>@endif
                    <span class="task-card-notes">{{ $task->notes }}</span>
                @endif
                @if (!$task->isRecurring() && !$task->isFromOtherUser() && !$task->notes)
                    <span class="task-card-subtle">{{ $task->daysRemaining() }} day{{ $task->daysRemaining() === 1 ? '' : 's' }} left</span>
                @endif
            </div>
        </button>
        <button type="button" class="task-complete-btn" aria-label="Complete task">
            <span class="task-complete-ring"></span>
        </button>
    </div>
</div>
