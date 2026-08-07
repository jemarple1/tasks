@extends('layouts.app')

@section('title', 'Garden — Tend')

@section('content')
    <header class="page-header">
        <h1 class="font-title text-3xl font-bold text-garden-text">Your garden</h1>
        <p class="mt-1 font-sans text-sm text-garden-muted">{{ $completedCount }} tasks completed</p>
    </header>

    <div class="garden-stage">
        <span id="garden-tree" class="garden-tree-display" style="font-size: {{ $treeSize }};">{{ $treeEmoji }}</span>
    </div>

    <p class="mt-6 text-center font-sans text-sm leading-relaxed text-garden-muted">
        Complete tasks to help your tree grow. Choose a different tree in Settings.
    </p>
@endsection
