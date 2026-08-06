<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'exists:users,username'],
        ]);

        $target = User::where('username', $validated['username'])->firstOrFail();

        if ($target->id === auth()->id()) {
            return back()->withErrors(['username' => 'You cannot add yourself.']);
        }

        if (auth()->user()->isConnectedTo($target)) {
            return back()->withErrors(['username' => 'Already connected.']);
        }

        UserConnection::create([
            'user_id' => auth()->id(),
            'connected_user_id' => $target->id,
        ]);

        UserConnection::firstOrCreate([
            'user_id' => $target->id,
            'connected_user_id' => auth()->id(),
        ]);

        return back()->with('status', 'Connected with @'.$target->username);
    }
}
