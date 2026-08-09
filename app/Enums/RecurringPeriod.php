<?php

namespace App\Enums;

enum RecurringPeriod: string
{
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Еженедельно',
            self::Biweekly => 'Каждые 2 недели',
            self::Monthly => 'Ежемесячно',
            self::Yearly => 'Ежегодно'
        };
    }
}
