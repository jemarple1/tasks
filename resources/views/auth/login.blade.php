@extends('layouts.app')

@section('title', 'Sign in — Tend')

@section('content')
    <div class="flex flex-1 flex-col justify-center py-8">
        <div class="mb-9 text-center">
            <span class="text-6xl leading-none" role="img" aria-label="{{ $weather['label'] ?? 'Weather' }}">{{ $weather['emoji'] ?? '🌤' }}</span>
            <h1 class="page-title mt-5 text-4xl">Welcome back</h1>
            <p class="page-subtitle text-[15px]">Sign in to Tend</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="form-stack space-y-3">
            @csrf

            @if ($errors->any())
                <div class="rounded-[var(--radius-field)] border border-rose-200 bg-rose-50 px-4 py-3 font-sans text-[15px] text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="email" class="input-field">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password" class="input-field">

            <button type="submit" class="btn-primary w-full">Sign in</button>
        </form>

        <p class="mt-7 text-center font-sans text-[15px] text-garden-muted">
            No account?
            <a href="{{ route('register') }}" class="font-semibold text-garden-accent">Create one</a>
        </p>
    </div>
@endsection
