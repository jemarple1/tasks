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

    public static function forPersonalCalendar(int $userId, Carbon $rangeStart, Carbon $rangeEnd)
    {
        return static::query()
            ->with(['user:id,username', 'taggedUsers:id,username'])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('taggedUsers', fn ($q) => $q->where('users.id', $userId));
            })
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere('recurrence', '!=', 'none');
            })
            ->get();
    }

    public static function forSharedCalendar(int $userId, array $connectedIds, Carbon $rangeStart, Carbon $rangeEnd)
    {
        $others = array_values(array_filter($connectedIds, fn ($id) => $id !== $userId));

        if (empty($others)) {
            return collect();
        }

        return static::query()
            ->with(['user:id,username', 'taggedUsers:id,username'])
            ->whereIn('user_id', $others)
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere('recurrence', '!=', 'none');
            })
            ->get();
    }
}
