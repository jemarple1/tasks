<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\User;
use App\Services\CircleColorService;
use App\Services\RecurrenceExpander;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $view = in_array($request->query('view'), ['month', 'week', 'day'], true)
            ? $request->query('view')
            : 'month';
        $date = Carbon::parse($request->query('date', now()->toDateString()));

        [$rangeStart, $rangeEnd, $label] = $this->resolveRange($view, $date);
        $user = auth()->user();
        $circleIds = $user->circleUserIds();
        $userColors = CircleColorService::mapForUser($user);

        $rawEvents = CalendarEvent::forCircleCalendar($circleIds, $rangeStart, $rangeEnd);

        $occurrences = RecurrenceExpander::expand($rawEvents, $rangeStart, $rangeEnd)
            ->groupBy(fn (array $o) => $o['occurrence_at']->toDateString());

        return view('calendar.index', [
            'view' => $view,
            'date' => $date,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'label' => $label,
            'occurrences' => $occurrences,
            'connections' => $user->connectedUsers(),
            'userColors' => $userColors,
            'calendarParams' => fn (Carbon $d, ?string $overrideView = null) => $this->calendarUrl($d, $overrideView ?? $view),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'recurrence' => ['required', 'in:'.implode(',', CalendarEvent::RECURRENCE_OPTIONS)],
            'recurrence_until' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'tagged_usernames' => ['nullable', 'array'],
            'tagged_usernames.*' => ['string', 'exists:users,username'],
        ]);

        $event = auth()->user()->calendarEvents()->create([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'recurrence' => $validated['recurrence'],
            'recurrence_until' => $validated['recurrence_until'] ?? null,
        ]);

        $this->syncTags($event, $validated['tagged_usernames'] ?? []);

        return redirect()->route('calendar.index', [
            'date' => Carbon::parse($validated['starts_at'])->toDateString(),
            'view' => 'day',
        ]);
    }

    public function destroy(CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === auth()->id(), 403);
        $event->delete();

        return back();
    }

    public function update(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'recurrence' => ['required', 'in:'.implode(',', CalendarEvent::RECURRENCE_OPTIONS)],
            'recurrence_until' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'tagged_usernames' => ['nullable', 'array'],
            'tagged_usernames.*' => ['string', 'exists:users,username'],
        ]);

        $event->update([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'recurrence' => $validated['recurrence'],
            'recurrence_until' => $validated['recurrence_until'] ?? null,
        ]);

        $this->syncTags($event, $validated['tagged_usernames'] ?? []);

        return redirect()->route('calendar.index', [
            'date' => Carbon::parse($validated['starts_at'])->toDateString(),
            'view' => 'day',
        ]);
    }

    private function resolveRange(string $view, Carbon $date): array
    {
        return match ($view) {
            'week' => [
                $date->copy()->startOfWeek(Carbon::SUNDAY),
                $date->copy()->endOfWeek(Carbon::SATURDAY),
                'Week of '.$date->copy()->startOfWeek(Carbon::SUNDAY)->format('M j'),
            ],
            'day' => [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
                $date->format('l, M j'),
            ],
            default => [
                $date->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY),
                $date->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY),
                $date->format('F Y'),
            ],
        };
    }

    private function calendarUrl(Carbon $date, string $view): array
    {
        return [
            'date' => $date->toDateString(),
            'view' => $view,
        ];
    }

    private function syncTags(CalendarEvent $event, array $usernames): void
    {
        $ids = collect($usernames)
            ->map(fn ($username) => User::where('username', $username)->first())
            ->filter(fn (?User $u) => $u && auth()->user()->isConnectedTo($u))
            ->pluck('id');

        $event->taggedUsers()->sync($ids);
    }
}
