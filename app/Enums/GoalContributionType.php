<?php

declare(strict_types=1);

namespace App\Enums;

enum GoalContributionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Пополнение',
            self::Withdrawal => 'Снятие',
        };
    }
}
