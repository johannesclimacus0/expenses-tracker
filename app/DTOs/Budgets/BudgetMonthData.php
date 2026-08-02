<?php

namespace App\DTOs\Budgets;

use Carbon\CarbonImmutable;

final readonly class BudgetMonthData
{
    public function __construct(
        public CarbonImmutable $month,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            month: isset($data['month'])
                ? CarbonImmutable::createFromFormat('Y-m', $data['month'])->startOfMonth()
                : now()->startOfMonth(),
        );
    }
}
