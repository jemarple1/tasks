<nav class="bottom-nav" aria-label="Main navigation">
    <a href="{{ route('tasks.index') }}" class="bottom-nav-item {{ request()->routeIs('tasks.index') ? 'active' : '' }}" aria-label="Tasks">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4" cy="6" r="1" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1" fill="currentColor" stroke="none"/></svg>
        <span>Tasks</span>
    </a>
    <a href="{{ route('calendar.index') }}" class="bottom-nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}" aria-label="Calendar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2.5"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>
        <span>Calendar</span>
    </a>
    <a href="{{ route('grocery.index') }}" class="bottom-nav-item {{ request()->routeIs('grocery.*') ? 'active' : '' }}" aria-label="Grocery">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12L4 3H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>
        <span>Grocery</span>
    </a>
    <button type="button" id="nav-add-task" class="bottom-nav-item bottom-nav-add" aria-label="Add task">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    </button>
</nav>
