@extends('layouts.app')

@section('title', 'Settings — Tend')

@section('content')
    <header class="page-header flex items-center gap-3">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <x-icon name="chevron-left" />
        </a>
        <h1 class="page-title">Settings</h1>
    </header>

    @if (session('status'))
        <p class="status-banner mb-4">
            <x-icon name="check-circle" class="h-4 w-4" />
            {{ session('status') }}
        </p>
    @endif

    <section class="surface-card mb-6 p-5 text-center">
        <div class="garden-stage-compact">
            <span id="garden-tree" class="garden-tree-display" style="font-size: max(2.75rem, {{ $treeSize }});">{{ auth()->user()->tree_emoji }}</span>
        </div>
        <p class="font-sans text-[15px] font-semibold text-garden-text">{{ $completedCount }} {{ Str::plural('task', $completedCount) }} completed</p>
        <p class="hint-text mt-1">Your tree grows every time you finish something</p>
    </section>

    <form action="{{ route('settings.update') }}" method="POST" class="mb-7 space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label for="settings-username" class="field-label">Username</label>
            <input id="settings-username" type="text" name="username" value="{{ old('username', auth()->user()->username) }}" required minlength="3" maxlength="30" class="input-field">
            @error('username')<p class="mt-1 font-sans text-[13px] text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="field-label">Your tree</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($treeOptions as $tree)
                    <label class="cursor-pointer">
                        <input type="radio" name="tree_emoji" value="{{ $tree }}" class="peer sr-only" @checked(auth()->user()->tree_emoji === $tree)>
                        <span class="tree-option">{{ $tree }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Save settings</button>
    </form>

    <section class="mb-7">
        <h2 class="section-title mb-2.5">Categories</h2>

        @if ($categories->isNotEmpty())
            <ul class="mb-3 space-y-2">
                @foreach ($categories as $category)
                    <li class="list-row">
                        <span class="flex min-w-0 items-center gap-2.5 font-sans text-[15px] font-medium">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $category->color }}"></span>
                            <span class="truncate">{{ $category->name }}</span>
                        </span>
                        @if ($categories->count() > 1)
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-quiet" aria-label="Remove category">Remove</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <form action="{{ route('categories.store') }}" method="POST" class="surface-card form-stack p-4">
            @csrf
            <input type="text" name="name" placeholder="New category name" required maxlength="30" class="input-field">
            <div>
                <p class="field-label">Colour</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\TaskCategory::COLOR_OPTIONS as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($loop->first)>
                            <span class="swatch" style="background: {{ $color }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="btn-secondary w-full">
                <x-icon name="plus" class="h-4 w-4" />
                Add category
            </button>
        </form>
    </section>

    <section>
        <h2 class="section-title mb-2.5">Connections</h2>
        <p class="hint-text mb-3">Everyone you connect with shares one grocery list and one calendar. Their entries are colour-coded.</p>

        <form action="{{ route('connections.store') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="username" placeholder="@username" required class="input-field min-w-0 flex-1">
            <button type="submit" class="btn-primary shrink-0 px-5">Add</button>
        </form>
        @error('username')<p class="mt-1 font-sans text-[13px] text-rose-600">{{ $message }}</p>@enderror

        @if ($connections->isNotEmpty())
            <ul class="mt-3 space-y-2">
                @foreach ($connections as $person)
                    <li class="list-row">
                        <span class="flex min-w-0 items-center gap-2.5 font-sans text-[15px] font-medium">
                            <x-icon name="at" class="h-4 w-4 text-garden-muted" />
                            <span class="truncate">{{ $person->username }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <form action="{{ route('logout') }}" method="POST" class="mt-8">
        @csrf
        <button type="submit" class="btn-danger w-full">
            <x-icon name="logout" class="h-4 w-4" />
            Sign out
        </button>
    </form>
@endsection
