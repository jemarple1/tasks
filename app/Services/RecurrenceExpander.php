<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurrenceExpander
{
    /**
     * @param  Collection<int, object{starts_at: Carbon, ends_at: ?Carbon, recurrence: string, recurrence_until: ?Carbon, id: mixed}>  $items
     * @return Collection<int, array{occurrence_at: Carbon, ends_at: ?Carbon, source: object}>
     */
    public static function expand(Collection $items, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        $occurrences = collect();

        foreach ($items as $item) {
            $startsAt = Carbon::parse($item->starts_at);
            $endsAt = $item->ends_at ? Carbon::parse($item->ends_at) : null;
            $durationMinutes = $endsAt ? $startsAt->diffInMinutes($endsAt) : 0;
            $recurrence = $item->recurrence ?? 'none';
            $until = isset($item->recurrence_until) && $item->recurrence_until
                ? Carbon::parse($item->recurrence_until)->endOfDay()
                : $rangeEnd->copy()->addYear();

            if ($recurrence === 'none') {
                if ($startsAt->between($rangeStart, $rangeEnd)) {
                    $occurrences->push(self::occurrence($item, $startsAt, $durationMinutes));
                }
                continue;
            }

            $cursor = $startsAt->copy();
            $guard = 0;

            while ($cursor->lte($rangeEnd) && $cursor->lte($until) && $guard < 500) {
                if ($cursor->gte($rangeStart->copy()->startOfDay())) {
                    $occurrences->push(self::occurrence($item, $cursor, $durationMinutes));
                }

                $cursor = match ($recurrence) {
                    'daily' => $cursor->addDay(),
                    'weekly' => $cursor->addWeek(),
                    'monthly' => $cursor->addMonth(),
                    default => $rangeEnd->copy()->addDay(),
                };
                $guard++;
            }
        }

        return $occurrences->sortBy('occurrence_at')->values();
    }

    private static function occurrence(object $item, Carbon $at, int $durationMinutes): array
    {
        return [
            'occurrence_at' => $at->copy(),
            'ends_at' => $durationMinutes > 0 ? $at->copy()->addMinutes($durationMinutes) : null,
            'source' => $item,
        ];
    }

    public static function nextOccurrenceDate(Carbon $from, string $recurrence): Carbon
    {
        return match ($recurrence) {
            'daily' => $from->copy()->addDay(),
            'weekly' => $from->copy()->addWeek(),
            'monthly' => $from->copy()->addMonth(),
            default => $from->copy(),
        };
    }
}
