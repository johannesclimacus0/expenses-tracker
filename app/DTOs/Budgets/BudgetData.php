<?php

namespace App\DTOs\Budgets;

use Carbon\CarbonImmutable;

final readonly class BudgetData
{
    public function __construct(
        public string $amount,
        public CarbonImmutable $month,
        public ?int $categoryId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            month: CarbonImmutable::createFromFormat('Y-m', $data['month'])->startOfMonth(),
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
        );
    }
}
