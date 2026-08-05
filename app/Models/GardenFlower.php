<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GardenFlower extends Model
{
    public const FLOWERS = ['🌸', '🌺', '🌼', '🌻', '🌷', '💐'];

    protected $fillable = [
        'user_id',
        'task_id',
        'emoji',
        'position_x',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public static function spawnForUser(User $user, ?Task $task = null, int $count = 4): array
    {
        $flowers = [];

        for ($i = 0; $i < $count; $i++) {
            $flowers[] = $user->gardenFlowers()->create([
                'task_id' => $task?->id,
                'emoji' => self::FLOWERS[array_rand(self::FLOWERS)],
                'position_x' => random_int(8, 92),
                'expires_at' => now()->addWeek(),
            ]);
        }

        return $flowers;
    }
}
