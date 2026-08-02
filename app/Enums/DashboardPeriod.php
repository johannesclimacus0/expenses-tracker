<?php

namespace App\Enums;

enum DashboardPeriod: string
{
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'Месяц',
            self::Quarter => '3 месяца',
            self::Year => 'Год',
            self::All => 'Всё время',
        };
    }
}
