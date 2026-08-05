<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'notes',
        'category',
        'archived_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function refreshExpiry(): void
    {
        $this->update(['expires_at' => now()->addDays(7)]);
    }

    public function markComplete(): void
    {
        $this->update([
            'archived_at' => now(),
        ]);
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }
}
