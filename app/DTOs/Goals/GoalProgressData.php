<?php

declare(strict_types=1);

namespace App\DTOs\Goals;

final readonly class GoalProgressData
{
    public function __construct(
        public string $deposited,
        public string $withdrawn,
        public string $currentAmount,
        public string $remainingAmount,
        public int $percentage,
        public bool $isCompleted,
        public bool $isOverdue,
    ) {}
}
