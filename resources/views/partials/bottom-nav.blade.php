<nav class="bottom-nav" aria-label="Main navigation">
    <a href="{{ route('tasks.index') }}" class="bottom-nav-item {{ request()->routeIs('tasks.index') ? 'active' : '' }}" aria-label="Tasks">
        <x-icon name="tasks" />
        <span>Tasks</span>
    </a>
    <a href="{{ route('calendar.index') }}" class="bottom-nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}" aria-label="Calendar">
        <x-icon name="calendar" />
        <span>Calendar</span>
    </a>
    <a href="{{ route('grocery.index') }}" class="bottom-nav-item {{ request()->routeIs('grocery.*') ? 'active' : '' }}" aria-label="Grocery">
        <x-icon name="cart" />
        <span>Grocery</span>
    </a>
    <button type="button" id="nav-add-task" class="bottom-nav-item bottom-nav-add" aria-label="Add task">
        <x-icon name="plus" :stroke="2" />
    </button>
</nav>
