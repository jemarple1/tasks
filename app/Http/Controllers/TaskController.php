<?php

namespace App\Http\Controllers;

use App\Models\GardenFlower;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $user->tasks()
            ->active()
            ->where('expires_at', '<=', now())
            ->update(['archived_at' => now()]);

        $user->gardenFlowers()->where('expires_at', '<=', now())->delete();

        $immediateTasks = $user->tasks()->active()->notExpired()->category('immediate')->latest()->get();
        $longtermTasks = $user->tasks()->active()->notExpired()->category('longterm')->latest()->get();

        return view('tasks.index', compact('immediateTasks', 'longtermTasks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:immediate,longterm'],
        ]);

        auth()->user()->tasks()->create([
            ...$validated,
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()->route('tasks.index');
    }

    public function complete(Task $task): JsonResponse
    {
        $task->markComplete();

        $flowers = GardenFlower::spawnForUser(auth()->user(), $task);

        return response()->json([
            'success' => true,
            'flowers' => collect($flowers)->map(fn (GardenFlower $f) => [
                'id' => $f->id,
                'emoji' => $f->emoji,
                'position_x' => $f->position_x,
            ])->values(),
        ]);
    }

    public function refreshExpiry(Task $task): JsonResponse
    {
        $task->refreshExpiry();

        return response()->json([
            'success' => true,
            'expires_at' => $task->expires_at->toIso8601String(),
            'days_remaining' => $task->daysRemaining(),
        ]);
    }

    public function completeIndex(): View
    {
        $completedTasks = auth()->user()->tasks()->completed()->latest('archived_at')->get();

        return view('tasks.complete', compact('completedTasks'));
    }
}
