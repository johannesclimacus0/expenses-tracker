<?php

namespace App\DTOs\RecurringTransactions;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use Carbon\CarbonImmutable;

final readonly class RecurringTransactionData
{
    public function __construct(
        public TransactionType $type,
        public string $amount,
        public ?int $categoryId,
        public ?string $description,
        public RecurringPeriod $period,
        public CarbonImmutable $startsAt,
        public bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: TransactionType::from($data['type']),
            amount: (string) $data['amount'],
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            description: $data['description'] ?? null,
            period: RecurringPeriod::from($data['period']),
            startsAt: CarbonImmutable::parse($data['starts_at']),
            isActive: (bool) $data['is_active'],
        );
    }
}
