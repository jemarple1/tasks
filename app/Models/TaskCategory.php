<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCategory extends Model
{
    public const COLOR_OPTIONS = [
        '#1d4ed8',
        '#0369a1',
        '#15803d',
        '#b45309',
        '#7c3aed',
        '#be185d',
        '#0f766e',
        '#475569',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @return array{urgent: self, later: self} */
    public static function seedDefaultsFor(User $user): array
    {
        $urgent = static::create([
            'user_id' => $user->id,
            'name' => 'Urgent',
            'color' => '#1d4ed8',
            'sort_order' => 0,
        ]);

        $later = static::create([
            'user_id' => $user->id,
            'name' => 'Later',
            'color' => '#6366f1',
            'sort_order' => 1,
        ]);

        return compact('urgent', 'later');
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }
}
