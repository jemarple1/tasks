@extends('layouts.app')

@section('title', 'Create account — Tend')

@section('content')
    <div class="flex flex-1 flex-col justify-center py-8">
        <div class="mb-9 text-center">
            <span class="text-6xl leading-none" role="img" aria-label="{{ $weather['label'] ?? 'Weather' }}">{{ $weather['emoji'] ?? '🌤' }}</span>
            <h1 class="page-title mt-5 text-4xl">Get started</h1>
            <p class="page-subtitle text-[15px]">Create your Tend account</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="form-stack space-y-3">
            @csrf

            @if ($errors->any())
                <div class="rounded-[var(--radius-field)] border border-rose-200 bg-rose-50 px-4 py-3 font-sans text-[15px] text-rose-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input type="text" name="name" value="{{ old('name') }}" placeholder="Name" required autocomplete="name" class="input-field">
            <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" required minlength="3" maxlength="30" autocomplete="username" class="input-field">
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="email" class="input-field">
            <input type="password" name="password" placeholder="Password (min. 8 characters)" required autocomplete="new-password" class="input-field">
            <input type="password" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password" class="input-field">

            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>

        <p class="mt-7 text-center font-sans text-[15px] text-garden-muted">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-garden-accent">Sign in</a>
        </p>
    </div>
@endsection
