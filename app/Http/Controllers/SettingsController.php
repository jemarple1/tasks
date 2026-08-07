<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('settings.index', [
            'connections' => $user->connectedUsers(),
            'categories' => $user->taskCategories,
            'treeOptions' => User::TREE_OPTIONS,
            'completedCount' => $user->completedTasksCount(),
            'treeSize' => $user->treeFontSize(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username,'.auth()->id()],
            'tree_emoji' => ['required', 'in:'.implode(',', User::TREE_OPTIONS)],
        ]);

        auth()->user()->update($validated);

        return back()->with('status', 'Settings saved.');
    }
}
