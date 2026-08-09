<?php

declare(strict_types=1);

namespace App\DTOs\Goals;

use App\Enums\GoalStatus;
use Carbon\CarbonImmutable;

final readonly class GoalData
{
    public function __construct(
        public string $name,
        public string $targetAmount,
        public ?CarbonImmutable $deadline,
        public GoalStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            targetAmount: (string) $data['target_amount'],
            deadline: isset($data['deadline'])
                ? CarbonImmutable::parse($data['deadline'])
                : null,
            status: GoalStatus::from($data['status']),
        );
    }
}
