<?php

namespace App\Models;

use App\Services\RecurrenceExpander;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public const RECURRENCE_OPTIONS = ['none', 'daily', 'weekly', 'monthly'];

    protected $fillable = [
        'user_id',
        'created_by_user_id',
        'title',
        'notes',
        'category',
        'archived_at',
        'expires_at',
        'recurrence',
        'recurrence_until',
        'recurrence_parent_id',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'expires_at' => 'datetime',
            'recurrence_until' => 'date',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'recurrence_parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function isFromOtherUser(): bool
    {
        return $this->created_by_user_id !== null
            && $this->created_by_user_id !== $this->user_id;
    }

    public function isRecurring(): bool
    {
        return $this->recurrence !== 'none';
    }

    public function refreshExpiry(): void
    {
        $this->update(['expires_at' => now()->addDays(7)]);
    }

    public function markComplete(): void
    {
        $recurrence = $this->recurrence;
        $recurrenceUntil = $this->recurrence_until;

        $this->update(['archived_at' => now()]);

        if ($recurrence !== 'none') {
            $this->spawnNextOccurrence($recurrence, $recurrenceUntil);
        }
    }

    protected function spawnNextOccurrence(string $recurrence, ?Carbon $recurrenceUntil): void
    {
        $nextExpiry = RecurrenceExpander::nextOccurrenceDate($this->expires_at, $recurrence);

        if ($recurrenceUntil && $nextExpiry->gt($recurrenceUntil)) {
            return;
        }

        static::create([
            'user_id' => $this->user_id,
            'created_by_user_id' => $this->created_by_user_id,
            'title' => $this->title,
            'notes' => $this->notes,
            'category' => $this->category,
            'expires_at' => $nextExpiry,
            'recurrence' => $recurrence,
            'recurrence_until' => $recurrenceUntil,
            'recurrence_parent_id' => $this->recurrence_parent_id ?? $this->id,
        ]);
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where(function (Builder $query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('created_by_user_id', auth()->id());
            })
            ->firstOrFail();
    }
}
