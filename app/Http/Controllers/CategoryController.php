<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'color' => ['required', 'in:'.implode(',', TaskCategory::COLOR_OPTIONS)],
        ]);

        auth()->user()->taskCategories()->create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'sort_order' => auth()->user()->taskCategories()->count(),
        ]);

        return back()->with('status', 'Category added.');
    }

    public function destroy(TaskCategory $category): RedirectResponse
    {
        abort_unless($category->user_id === auth()->id(), 403);

        $fallback = auth()->user()->taskCategories()
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')
            ->first();

        if ($fallback) {
            $category->tasks()->update(['task_category_id' => $fallback->id]);
        }

        $category->delete();

        return back()->with('status', 'Category removed.');
    }
}
