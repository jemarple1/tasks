<?php

namespace App\Http\Controllers;

use App\Models\GroceryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroceryController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $user->groceryItems()
            ->where('created_at', '<', now()->subWeek())
            ->delete();

        $items = $user->groceryItems()->orderBy('created_at')->get();

        return view('grocery.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'recurrence' => ['required', 'in:'.implode(',', GroceryItem::RECURRENCE_OPTIONS)],
        ]);

        auth()->user()->groceryItems()->create($validated);

        return back();
    }

    public function complete(GroceryItem $item): JsonResponse
    {
        abort_unless($item->user_id === auth()->id(), 403);

        $item->markComplete();

        return response()->json(['success' => true]);
    }

    public function destroy(GroceryItem $item): RedirectResponse
    {
        abort_unless($item->user_id === auth()->id(), 403);
        $item->delete();

        return back();
    }
}
