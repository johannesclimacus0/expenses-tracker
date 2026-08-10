<?php

namespace App\Enums;

enum AccountRole: string
{
    case Owner = 'owner';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Member => 'Member',
        };
    }
}
