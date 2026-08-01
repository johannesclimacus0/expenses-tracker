<?php

namespace App\DTOs\Transactions;

use App\Enums\TransactionType;
use Carbon\CarbonImmutable;

final readonly class TransactionData
{
    public function __construct(
        public TransactionType $type,
        public string $amount,
        public ?int $categoryId,
        public ?string $description,
        public CarbonImmutable $occurredAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: TransactionType::from($data['type']),
            amount: $data['amount'],
            categoryId: $data['category_id'] ?? null,
            description: $data['description'] ?? null,
            occurredAt: CarbonImmutable::parse($data['occurred_at']),
        );
    }
}
