<nav class="bottom-nav" aria-label="Main navigation">
    <a href="{{ route('tasks.index') }}" class="bottom-nav-item {{ request()->routeIs('tasks.index') ? 'active' : '' }}" aria-label="Tasks">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        <span>Tasks</span>
    </a>
    <a href="{{ route('calendar.index') }}" class="bottom-nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}" aria-label="Calendar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <span>Calendar</span>
    </a>
    <a href="{{ route('grocery.index') }}" class="bottom-nav-item {{ request()->routeIs('grocery.*') ? 'active' : '' }}" aria-label="Grocery">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6l-1-3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
        <span>Grocery</span>
    </a>
    <a href="{{ route('garden.index') }}" class="bottom-nav-item {{ request()->routeIs('garden.*') ? 'active' : '' }}" aria-label="Garden">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V12"/><path d="M12 12C12 8 8 4 4 4c0 4 4 8 8 8z"/><path d="M12 12c0-4 4-8 8-8 0 4-4 8-8 8z"/></svg>
        <span>Garden</span>
    </a>
    <button type="button" id="nav-add-task" class="bottom-nav-item bottom-nav-add" aria-label="Add task">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    </button>
</nav>
