<?php

namespace App\Services;

use App\Models\User;

class CircleColorService
{
    public const PALETTE = [
        ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#2563eb'],
        ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#059669'],
        ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#d97706'],
        ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#7c3aed'],
        ['bg' => '#fce7f3', 'text' => '#9d174d', 'border' => '#db2777'],
        ['bg' => '#ffedd5', 'text' => '#9a3412', 'border' => '#ea580c'],
    ];

    /** @return array<int, array{bg: string, text: string, border: string, username: string, is_self: bool}> */
    public static function mapForUser(User $user): array
    {
        $circle = collect([$user])->merge($user->connectedUsers());
        $map = [];

        foreach ($circle->values() as $index => $member) {
            $colors = self::PALETTE[$index % count(self::PALETTE)];
            $map[$member->id] = [
                ...$colors,
                'username' => $member->username,
                'is_self' => $member->id === $user->id,
            ];
        }

        return $map;
    }

    /** @param array{bg: string, text: string} $color */
    public static function pillStyle(array $color): string
    {
        return "background: {$color['bg']}; color: {$color['text']};";
    }

    /** @param array{border: string} $color */
    public static function accentStyle(array $color): string
    {
        return "border-color: {$color['border']}; color: {$color['text']};";
    }
}
