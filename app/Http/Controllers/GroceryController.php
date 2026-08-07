<?php

namespace App\Http\Controllers;

use App\Models\GroceryItem;
use App\Services\CircleColorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroceryController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $circleIds = $user->circleUserIds();

        GroceryItem::query()
            ->whereIn('user_id', $circleIds)
            ->where('created_at', '<', now()->subWeek())
            ->delete();

        $items = GroceryItem::query()
            ->with('user:id,username')
            ->whereIn('user_id', $circleIds)
            ->orderBy('created_at')
            ->get();

        return view('grocery.index', [
            'items' => $items,
            'userColors' => CircleColorService::mapForUser($user),
            'connections' => $user->connectedUsers(),
        ]);
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
        abort_unless(auth()->user()->canAccessCircleUser($item->user_id), 403);

        $item->markComplete();

        return response()->json(['success' => true]);
    }

    public function destroy(GroceryItem $item): RedirectResponse
    {
        abort_unless(auth()->user()->canAccessCircleUser($item->user_id), 403);
        $item->delete();

        return back();
    }
}
