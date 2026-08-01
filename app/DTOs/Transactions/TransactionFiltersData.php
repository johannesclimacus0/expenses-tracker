<?php

namespace App\DTOs\Transactions;

use App\Enums\TransactionType;
use Carbon\CarbonImmutable;

final readonly class TransactionFiltersData
{
    public function __construct(
        public ?TransactionType $type,
        public ?string $categoryUuid,
        public ?CarbonImmutable $from,
        public ?CarbonImmutable $to,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: isset($data['type'])
                ? TransactionType::from($data['type'])
                : null,
            categoryUuid: $data['category'] ?? null,
            from: isset($data['from'])
                ? CarbonImmutable::parse($data['from'])->startOfDay()
                : null,
            to: isset($data['to'])
                ? CarbonImmutable::parse($data['to'])->endOfDay()
                : null,
        );
    }
}
