<?php

namespace App\Models;

use App\Services\RecurrenceExpander;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CalendarEvent extends Model
{
    public const RECURRENCE_OPTIONS = ['none', 'daily', 'weekly', 'monthly'];

    protected $fillable = [
        'user_id',
        'title',
        'notes',
        'starts_at',
        'ends_at',
        'recurrence',
        'recurrence_until',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'recurrence_until' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taggedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calendar_event_user');
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public static function forCircleCalendar(array $userIds, Carbon $rangeStart, Carbon $rangeEnd)
    {
        if ($userIds === []) {
            return collect();
        }

        return static::query()
            ->with(['user:id,username', 'taggedUsers:id,username'])
            ->whereIn('user_id', $userIds)
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($inner) use ($rangeStart, $rangeEnd) {
                        $inner->whereNotNull('ends_at')
                            ->where('starts_at', '<=', $rangeEnd)
                            ->where('ends_at', '>=', $rangeStart);
                    })
                    ->orWhere('recurrence', '!=', 'none');
            })
            ->get();
    }
}
