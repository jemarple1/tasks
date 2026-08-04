<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $immediateTasks = auth()->user()->tasks()->active()->category('immediate')->latest()->get();
        $longtermTasks = auth()->user()->tasks()->active()->category('longterm')->latest()->get();

        return view('tasks.index', compact('immediateTasks', 'longtermTasks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:immediate,longterm'],
        ]);

        auth()->user()->tasks()->create($validated);

        return redirect()->route('tasks.index');
    }

    public function archive(Task $task): JsonResponse
    {
        $task->update(['archived_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function archiveIndex(): View
    {
        $archivedTasks = auth()->user()->tasks()->archived()->latest('archived_at')->get();

        return view('tasks.archive', compact('archivedTasks'));
    }
}
