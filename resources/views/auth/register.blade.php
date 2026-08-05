@extends('layouts.app')

@section('title', 'Create account — Task Garden')

@section('content')
    <div class="flex flex-1 flex-col justify-center py-8">
        <div class="mb-10 text-center">
            <span class="text-6xl" role="img" aria-label="{{ $weather['label'] ?? 'Weather' }}">{{ $weather['emoji'] ?? '🌤' }}</span>
            <h1 class="mt-5 font-serif text-3xl font-bold italic text-garden-text">Get started</h1>
            <p class="mt-2 font-sans text-lg text-garden-muted">Create your Task Garden account</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="rounded-2xl border-2 border-red-200 bg-red-50 px-5 py-4 font-sans text-base text-red-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Name"
                required
                autocomplete="name"
                class="input-field w-full rounded-2xl border-2 border-white/70 bg-white/90 px-5 py-4 text-garden-text outline-none placeholder:text-garden-muted/70 focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20"
            >

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Email"
                required
                autocomplete="email"
                class="input-field w-full rounded-2xl border-2 border-white/70 bg-white/90 px-5 py-4 text-garden-text outline-none placeholder:text-garden-muted/70 focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20"
            >

            <input
                type="password"
                name="password"
                placeholder="Password (min. 8 characters)"
                required
                autocomplete="new-password"
                class="input-field w-full rounded-2xl border-2 border-white/70 bg-white/90 px-5 py-4 text-garden-text outline-none placeholder:text-garden-muted/70 focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20"
            >

            <input
                type="password"
                name="password_confirmation"
                placeholder="Confirm password"
                required
                autocomplete="new-password"
                class="input-field w-full rounded-2xl border-2 border-white/70 bg-white/90 px-5 py-4 text-garden-text outline-none placeholder:text-garden-muted/70 focus:border-garden-accent focus:ring-4 focus:ring-garden-accent/20"
            >

            <button
                type="submit"
                class="w-full rounded-2xl bg-garden-accent py-4 font-sans text-lg font-semibold text-white shadow-lg transition active:scale-[0.98]"
            >
                Create account
            </button>
        </form>

        <p class="mt-8 text-center font-sans text-base text-garden-muted">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-garden-accent">Sign in</a>
        </p>
    </div>
@endsection
