<?php

namespace App\Enums;

enum Currency: string
{
    case Rub = 'RUB';
    case Usd = 'USD';
    case Eur = 'EUR';

    public function label(): string
    {
        return match ($this) {
            self::Rub => 'Рубль',
            self::Usd => 'Доллар',
            self::Eur => 'Евро',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Rub => '₽',
            self::Usd => '$',
            self::Eur => '€',
        };
    }
}
