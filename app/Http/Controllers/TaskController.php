<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $user->tasks()
            ->active()
            ->where('expires_at', '<=', now())
            ->whereNull('linked_task_id')
            ->update(['archived_at' => now()]);

        $categories = $user->taskCategories;
        $activeCategory = $request->query('category');

        if ($activeCategory && ! $categories->contains('id', (int) $activeCategory)) {
            $activeCategory = null;
        }

        $tasksQuery = $user->tasks()
            ->active()
            ->notExpired()
            ->with(['creator:id,username', 'assignedTo:id,username', 'taskCategory'])
            ->orderBy('expires_at');

        if ($activeCategory) {
            $tasksQuery->where('task_category_id', $activeCategory);
        }

        $tasks = $tasksQuery->get();

        return view('tasks.index', compact('tasks', 'categories', 'activeCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'task_category_id' => ['required', 'exists:task_categories,id'],
            'assignee_username' => ['nullable', 'string', 'exists:users,username'],
            'show_on_my_list' => ['nullable', 'boolean'],
            'recurrence' => ['required', 'in:'.implode(',', Task::RECURRENCE_OPTIONS)],
            'recurrence_until' => ['nullable', 'date', 'after:today'],
            'due_at' => ['nullable', 'date'],
        ]);

        $category = TaskCategory::where('id', $validated['task_category_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $creator = auth()->user();
        $assignee = $creator;
        if (! empty($validated['assignee_username'])) {
            $assignee = User::where('username', $validated['assignee_username'])->firstOrFail();
            abort_unless($creator->isConnectedTo($assignee), 403);
        }

        $taskData = [
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'category' => $this->legacyCategoryFor($category),
            'task_category_id' => $category->id,
            'created_by_user_id' => $creator->id,
            'expires_at' => now()->addDays(7),
            'recurrence' => $validated['recurrence'],
            'recurrence_until' => $validated['recurrence_until'] ?? null,
        ];

        $assigneeCategoryId = $this->resolveCategoryForAssignee($category, $assignee, $creator);

        $primaryTask = $assignee->tasks()->create([
            ...$taskData,
            'task_category_id' => $assigneeCategoryId,
        ]);

        if ($assignee->id !== $creator->id && $request->boolean('show_on_my_list')) {
            $creator->tasks()->create([
                ...$taskData,
                'assigned_to_user_id' => $assignee->id,
                'linked_task_id' => $primaryTask->id,
            ]);
        }

        return redirect()->route('tasks.index', array_filter([
            'category' => $request->input('filter_category'),
        ]));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->user_id === auth()->id() || $task->created_by_user_id === auth()->id(), 403);
        abort_if($task->isTrackingCopy(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'task_category_id' => ['required', 'exists:task_categories,id'],
            'recurrence' => ['required', 'in:'.implode(',', Task::RECURRENCE_OPTIONS)],
            'recurrence_until' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
        ]);

        $category = TaskCategory::where('id', $validated['task_category_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $task->update([
            ...$validated,
            'category' => $this->legacyCategoryFor($category),
        ]);

        return redirect()->route('tasks.index', array_filter([
            'category' => $request->input('filter_category'),
        ]));
    }

    public function complete(Task $task): JsonResponse
    {
        abort_unless($task->user_id === auth()->id(), 403);

        if ($task->isTrackingCopy()) {
            $task->update(['archived_at' => now()]);

            return response()->json([
                'success' => true,
                'tree_size' => auth()->user()->fresh()->treeFontSize(),
            ]);
        }

        $task->markComplete();

        if ($task->isFromOtherUser() && $task->creator) {
            $task->creator->notify(new TaskCompletedNotification($task));
        }

        return response()->json([
            'success' => true,
            'tree_size' => auth()->user()->fresh()->treeFontSize(),
        ]);
    }

    public function snooze(Task $task): JsonResponse
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $task->snoozeOneDay();

        return response()->json([
            'success' => true,
            'days_remaining' => $task->daysRemaining(),
        ]);
    }

    public function refreshExpiry(Task $task): JsonResponse
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $task->refreshExpiry();

        return response()->json([
            'success' => true,
            'days_remaining' => $task->daysRemaining(),
        ]);
    }

    public function completeIndex(): View
    {
        $completedTasks = auth()->user()->tasks()->completed()->with(['creator:id,username', 'taskCategory'])->latest('archived_at')->get();

        return view('tasks.complete', compact('completedTasks'));
    }

    private function legacyCategoryFor(TaskCategory $category): string
    {
        return str_contains(strtolower($category->name), 'long') ? 'longterm' : 'immediate';
    }

    private function resolveCategoryForAssignee(TaskCategory $creatorCategory, User $assignee, User $creator): int
    {
        if ($assignee->id === $creator->id) {
            return $creatorCategory->id;
        }

        $match = $assignee->taskCategories()
            ->where('name', $creatorCategory->name)
            ->first();

        return $match?->id ?? $assignee->taskCategories()->orderBy('sort_order')->value('id') ?? $creatorCategory->id;
    }
}
