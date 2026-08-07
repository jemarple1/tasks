<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroceryItem extends Model
{
    public const RECURRENCE_OPTIONS = ['none', 'weekly', 'biweekly', 'monthly'];

    protected $fillable = [
        'user_id',
        'name',
        'recurrence',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRecurring(): bool
    {
        return $this->recurrence !== 'none';
    }

    public function markComplete(): void
    {
        $recurrence = $this->recurrence;

        $this->delete();

        if ($recurrence !== 'none') {
            static::create([
                'user_id' => $this->user_id,
                'name' => $this->name,
                'recurrence' => $recurrence,
            ]);
        }
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        $circleIds = auth()->user()->circleUserIds();

        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->whereIn('user_id', $circleIds)
            ->firstOrFail();
    }
}
