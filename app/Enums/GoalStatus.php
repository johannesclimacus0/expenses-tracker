<?php

declare(strict_types=1);

namespace App\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активна',
            self::Paused => 'Приостановлена',
            self::Cancelled => 'Отменена',
            self::Completed => 'Завершена',
        };
    }
}
