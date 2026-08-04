@extends('layouts.app')

@section('title', 'Sign in — Sky Ledger')

@section('content')
    <div class="flex flex-1 flex-col justify-center py-8">
        <div class="mb-10 text-center">
            <span class="text-5xl" role="img" aria-label="Sun behind cloud">🌤</span>
            <h1 class="mt-4 text-2xl font-semibold tracking-tight">Welcome back</h1>
            <p class="mt-1 text-sm text-sky-muted">Sign in to your ledger</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl bg-red-100/80 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email"
                    required
                    autocomplete="email"
                    class="w-full rounded-xl border border-white/50 bg-white/70 px-4 py-3.5 text-[15px] text-sky-deep outline-none ring-sky-accent/30 placeholder:text-sky-muted/70 focus:border-sky-accent/40 focus:ring-2"
                >
            </div>

            <div>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-white/50 bg-white/70 px-4 py-3.5 text-[15px] text-sky-deep outline-none ring-sky-accent/30 placeholder:text-sky-muted/70 focus:border-sky-accent/40 focus:ring-2"
                >
            </div>

            <label class="flex items-center gap-2 text-sm text-sky-deep/80">
                <input type="checkbox" name="remember" class="rounded border-blue-300 text-sky-card focus:ring-sky-accent">
                Remember me
            </label>

            <button
                type="submit"
                class="w-full rounded-xl bg-sky-accent py-3.5 text-sm font-semibold text-white shadow-md transition active:scale-[0.98]"
            >
                Sign in
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-sky-deep/80">
            No account?
            <a href="{{ route('register') }}" class="font-medium text-sky-accent">Create one</a>
        </p>
    </div>
@endsection
