<?php

declare(strict_types=1);

namespace App\DTOs\Goals;

use App\Enums\GoalContributionType;
use Carbon\CarbonImmutable;

final readonly class GoalContributionData
{
    public function __construct(
        public GoalContributionType $type,
        public string $amount,
        public CarbonImmutable $contributedAt,
        public ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: GoalContributionType::from($data['type']),
            amount: (string) $data['amount'],
            contributedAt: CarbonImmutable::parse($data['contributed_at']),
            note: $data['note'] ?? null,
        );
    }
}
