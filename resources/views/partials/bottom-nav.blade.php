<nav class="bottom-nav" aria-label="Main navigation">
    <a
        href="{{ route('tasks.index') }}"
        class="bottom-nav-item {{ request()->routeIs('tasks.index') ? 'active' : '' }}"
        aria-label="Tasks"
        @if(request()->routeIs('tasks.index')) aria-current="page" @endif
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
    </a>
    <a
        href="{{ route('calendar.index') }}"
        class="bottom-nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}"
        aria-label="Calendar"
        @if(request()->routeIs('calendar.*')) aria-current="page" @endif
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </a>
    <button type="button" id="nav-add-task" class="bottom-nav-item bottom-nav-add" aria-label="Add task">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    </button>
</nav>
