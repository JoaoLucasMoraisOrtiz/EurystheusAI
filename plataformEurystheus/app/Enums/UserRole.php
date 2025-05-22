<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case FREE_USER = 'free_user';
    case PAYED_USER = 'payed_user';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::FREE_USER => 'Free User',
            self::PAYED_USER => 'Payed User',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isPayed(): bool
    {
        return $this === self::PAYED_USER;
    }

    public function isFree(): bool
    {
        return $this === self::FREE_USER;
    }
}
