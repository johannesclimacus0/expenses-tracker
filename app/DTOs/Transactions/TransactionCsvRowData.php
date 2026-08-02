<?php

namespace App\DTOs\Transactions;

use App\Enums\TransactionType;
use Carbon\CarbonImmutable;

final readonly class TransactionCsvRowData
{
    public function __construct(
        public TransactionType $type,
        public string $amount,
        public ?int $categoryId,
        public ?string $description,
        public CarbonImmutable $occurredAt,
    ) {}
}
