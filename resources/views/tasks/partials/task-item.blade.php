@props(['task'])

<div
    class="task-swipe-wrapper task-enter"
    data-task
    data-archive-url="{{ route('tasks.archive', $task) }}"
>
    <div class="task-swipe-bg">
        <span>Archive</span>
    </div>
    <div class="task-swipe-content rounded-2xl border border-blue-100/80 bg-sky-task px-4 py-3.5 shadow-sm">
        <p class="text-[15px] leading-snug font-medium text-sky-deep">{{ $task->title }}</p>
        <p class="mt-1 text-xs text-sky-muted">{{ $task->created_at->diffForHumans() }}</p>
    </div>
</div>
