<?php

namespace App\DTOs\Budgets;

use App\Models\Budget;

final readonly class BudgetUsageData
{
    public function __construct(
        public Budget $budget,
        public string $spent,
        public string $remaining,
        public int $percentage,
        public bool $warning,
        public bool $exceeded,
    ) {}
}
