@props(['task'])

<div
    class="task-swipe-wrapper task-enter"
    data-task
    data-complete-url="{{ route('tasks.complete', $task) }}"
    data-refresh-url="{{ route('tasks.refresh', $task) }}"
    data-update-url="{{ route('tasks.update', $task) }}"
    data-task-title="{{ e($task->title) }}"
    data-task-notes="{{ e($task->notes ?? '') }}"
    data-task-category="{{ $task->category }}"
>
    <div class="task-swipe-bg-left">
        <span>Complete</span>
    </div>
    <div class="task-swipe-bg-right">
        <span>+7 days</span>
    </div>
    <button
        type="button"
        class="task-swipe-content task-tap-target w-full rounded-xl border-2 border-garden-task-border bg-garden-task px-3.5 py-2 text-left shadow-sm"
    >
        <div class="flex items-center justify-between gap-2">
            <p class="min-w-0 flex-1 font-serif text-base font-semibold leading-tight text-garden-text">{{ $task->title }}</p>
            <span data-expiry class="shrink-0 font-sans text-sm font-semibold text-garden-accent">{{ $task->daysRemaining() }}d</span>
        </div>
        @if ($task->notes)
            <p class="mt-0.5 line-clamp-1 font-sans text-sm leading-snug text-garden-muted">{{ $task->notes }}</p>
        @endif
    </button>
</div>
