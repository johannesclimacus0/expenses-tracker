<?php

namespace App\DTOs\Categories;

use App\Enums\TransactionType;

final readonly class CategoryData
{
    public function __construct(
        public string $name,
        public TransactionType $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: TransactionType::from($data['type']),
        );
    }
}
