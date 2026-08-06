@extends('layouts.app')

@section('title', 'Settings — Tend')

@section('content')
    <header class="flex items-center gap-3 pb-4 pt-2">
        <a href="{{ route('tasks.index') }}" class="nav-btn" aria-label="Back">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-title text-2xl font-bold text-garden-text">Settings</h1>
    </header>

    @if (session('status'))
        <p class="mb-4 rounded-xl bg-green-50 px-4 py-3 font-sans text-base text-green-800">{{ session('status') }}</p>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" class="mb-8 space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Username</label>
            <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}" required minlength="3" maxlength="30" class="input-field w-full rounded-xl border-2 border-white/70 bg-white/90 px-4 py-3 outline-none focus:border-garden-accent">
            @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="mb-2 font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Your tree</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($treeOptions as $tree)
                    <label class="cursor-pointer">
                        <input type="radio" name="tree_emoji" value="{{ $tree }}" class="peer sr-only" @checked(auth()->user()->tree_emoji === $tree)>
                        <span class="flex items-center justify-center rounded-xl border-2 border-white/70 bg-white/80 py-4 text-3xl peer-checked:border-garden-accent peer-checked:bg-blue-50">{{ $tree }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full rounded-xl bg-garden-accent py-3.5 font-sans text-lg font-semibold text-white">Save settings</button>
    </form>

    <section class="mb-8">
        <h2 class="mb-3 font-title text-xl font-semibold text-garden-text">Categories</h2>

        @if ($categories->isNotEmpty())
            <ul class="mb-4 space-y-2">
                @foreach ($categories as $category)
                    <li class="flex items-center justify-between rounded-xl border-2 border-white/70 bg-white/80 px-4 py-2.5">
                        <span class="flex items-center gap-2 font-sans text-base">
                            <span class="inline-block h-3 w-3 rounded-full" style="background: {{ $category->color }}"></span>
                            {{ $category->name }}
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

        <form action="{{ route('categories.store') }}" method="POST" class="space-y-3">
            @csrf
            <input type="text" name="name" placeholder="Category name" required maxlength="30" class="input-field w-full rounded-xl border-2 border-white/70 bg-white/90 px-4 py-3 outline-none focus:border-garden-accent">
            <div>
                <p class="mb-2 font-sans text-sm font-semibold text-garden-muted">Color</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\TaskCategory::COLOR_OPTIONS as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($loop->first)>
                            <span class="block h-9 w-9 rounded-full border-2 border-transparent peer-checked:border-garden-text peer-checked:ring-2 peer-checked:ring-white" style="background: {{ $color }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="w-full rounded-xl border-2 border-garden-accent bg-white py-3 font-sans font-semibold text-garden-accent">Add category</button>
        </form>
    </section>

    <section>
        <h2 class="mb-3 font-title text-xl font-semibold text-garden-text">Add someone</h2>
        <form action="{{ route('connections.store') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="username" placeholder="@username" required class="input-field min-w-0 flex-1 rounded-xl border-2 border-white/70 bg-white/90 px-4 py-3 outline-none focus:border-garden-accent">
            <button type="submit" class="shrink-0 rounded-xl bg-garden-accent px-4 py-3 font-sans font-semibold text-white">Add</button>
        </form>
        @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

        @if ($connections->isNotEmpty())
            <ul class="mt-4 space-y-2">
                @foreach ($connections as $person)
                    <li class="rounded-xl border-2 border-white/70 bg-white/80 px-4 py-2.5 font-sans text-base">{{ '@'.$person->username }}</li>
                @endforeach
            </ul>
        @endif
    </section>

    <form action="{{ route('logout') }}" method="POST" class="mt-10">
        @csrf
        <button type="submit" class="w-full rounded-xl border-2 border-red-200 bg-red-50 py-3.5 font-sans text-lg font-semibold text-red-700 transition active:scale-[0.98]">
            Sign out
        </button>
    </form>
@endsection
