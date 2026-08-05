@props(['task'])

<div
    class="task-swipe-wrapper task-enter"
    data-task
    data-complete-url="{{ route('tasks.complete', $task) }}"
    data-refresh-url="{{ route('tasks.refresh', $task) }}"
>
    <div class="task-swipe-bg-left">
        <span>Complete</span>
    </div>
    <div class="task-swipe-bg-right">
        <span>+7 days</span>
    </div>
    <div class="task-swipe-content rounded-2xl border-2 border-garden-task-border bg-garden-task px-5 py-4 shadow-md">
        <p class="font-serif text-lg font-semibold leading-snug text-garden-text">{{ $task->title }}</p>
        <div class="mt-2 flex items-center justify-between font-sans text-base text-garden-muted">
            <span>{{ $task->created_at->diffForHumans() }}</span>
            <span data-expiry class="font-medium text-garden-accent">{{ $task->daysRemaining() }}d left</span>
        </div>
    </div>
</div>
