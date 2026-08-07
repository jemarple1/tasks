<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'tree_emoji'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const TREE_OPTIONS = ['🌳', '🌲', '🌴'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by_user_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function groceryItems(): HasMany
    {
        return $this->hasMany(GroceryItem::class);
    }

    public function taskCategories(): HasMany
    {
        return $this->hasMany(TaskCategory::class)->orderBy('sort_order');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(UserConnection::class);
    }

    public function connectedUsers(): Collection
    {
        $outgoing = UserConnection::where('user_id', $this->id)->pluck('connected_user_id');
        $incoming = UserConnection::where('connected_user_id', $this->id)->pluck('user_id');

        return User::whereIn('id', $outgoing->merge($incoming)->unique())->orderBy('username')->get();
    }

    /** @return list<int> */
    public function circleUserIds(): array
    {
        return $this->connectedUsers()
            ->pluck('id')
            ->push($this->id)
            ->unique()
            ->values()
            ->all();
    }

    public function canAccessCircleUser(int $userId): bool
    {
        return in_array($userId, $this->circleUserIds(), true);
    }

    public function isConnectedTo(User $user): bool
    {
        if ($this->id === $user->id) {
            return true;
        }

        return UserConnection::query()
            ->where(function (Builder $q) use ($user) {
                $q->where('user_id', $this->id)->where('connected_user_id', $user->id);
            })
            ->orWhere(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)->where('connected_user_id', $this->id);
            })
            ->exists();
    }

    public function completedTasksCount(): int
    {
        return $this->tasks()->completed()->count();
    }

    public function treeFontSize(): string
    {
        $count = $this->completedTasksCount();
        $size = min(100, 3 + ($count * 0.24));

        return round($size, 2).'vmin';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
