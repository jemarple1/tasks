<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GardenController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('garden.index', [
            'completedCount' => $user->completedTasksCount(),
            'treeSize' => $user->treeFontSize(),
            'treeEmoji' => $user->tree_emoji ?? '🌳',
        ]);
    }
}
