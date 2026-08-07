@extends('layouts.app')

@section('title', 'Settings — Tend')

@section('content')
    <header class="flex items-center gap-3 pb-4 pt-2">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-title text-2xl font-bold text-garden-text">Settings</h1>
    </header>

    @if (session('status'))
        <p class="status-banner mb-4">{{ session('status') }}</p>
    @endif

    <section class="surface-card mb-6 overflow-hidden p-5 text-center">
        <div class="garden-stage-compact">
            <span class="garden-tree-display" style="font-size: {{ $treeSize }};">{{ auth()->user()->tree_emoji }}</span>
        </div>
        <p class="font-sans text-sm text-garden-muted">{{ $completedCount }} tasks completed</p>
        <p class="mt-1 font-sans text-xs text-garden-muted">Your tree grows as you complete tasks</p>
    </section>

    <form action="{{ route('settings.update') }}" method="POST" class="form-stack mb-8 space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label class="field-label">Username</label>
            <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}" required minlength="3" maxlength="30" class="input-field">
            @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="field-label">Your tree</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($treeOptions as $tree)
                    <label class="cursor-pointer">
                        <input type="radio" name="tree_emoji" value="{{ $tree }}" class="peer sr-only" @checked(auth()->user()->tree_emoji === $tree)>
                        <span class="flex items-center justify-center rounded-xl border-2 border-white/70 bg-white/80 py-4 text-3xl transition peer-checked:border-garden-accent peer-checked:bg-blue-50 peer-checked:shadow-sm">{{ $tree }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Save settings</button>
    </form>

    <section class="mb-8">
        <h2 class="section-title mb-3">Categories</h2>

        @if ($categories->isNotEmpty())
            <ul class="mb-4 space-y-2">
                @foreach ($categories as $category)
                    <li class="list-row">
                        <span class="flex min-w-0 items-center gap-2 font-sans text-base">
                            <span class="inline-block h-3 w-3 shrink-0 rounded-full" style="background: {{ $category->color }}"></span>
                            <span class="truncate">{{ $category->name }}</span>
                        </span>
                        @if ($categories->count() > 1)
                            <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-sans text-sm text-red-600">Remove</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <form action="{{ route('categories.store') }}" method="POST" class="form-stack space-y-3">
            @csrf
            <input type="text" name="name" placeholder="Category name" required maxlength="30" class="input-field">
            <div>
                <p class="field-label">Color</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\TaskCategory::COLOR_OPTIONS as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($loop->first)>
                            <span class="block h-9 w-9 rounded-full border-2 border-transparent peer-checked:border-garden-text peer-checked:ring-2 peer-checked:ring-white" style="background: {{ $color }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="btn-secondary w-full">Add category</button>
        </form>
    </section>

    <section>
        <h2 class="section-title mb-3">Connections</h2>
        <p class="mb-3 font-sans text-sm text-garden-muted">Connected people share your grocery list and calendar automatically.</p>
        <form action="{{ route('connections.store') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="username" placeholder="@username" required class="input-field min-w-0 flex-1">
            <button type="submit" class="btn-primary shrink-0 px-4">Add</button>
        </form>
        @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

        @if ($connections->isNotEmpty())
            <ul class="mt-4 space-y-2">
                @foreach ($connections as $person)
                    <li class="list-row font-sans text-base">{{ '@'.$person->username }}</li>
                @endforeach
            </ul>
        @endif
    </section>

    <form action="{{ route('logout') }}" method="POST" class="mt-10">
        @csrf
        <button type="submit" class="btn-danger w-full">
            Sign out
        </button>
    </form>
@endsection
