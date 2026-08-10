<?php

declare(strict_types=1);

namespace App\DTOs\Accounts;

use App\Enums\Currency;

final readonly class AccountData
{
    public function __construct(
        public string $name,
        public Currency $currency,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data['name'], Currency::from($data['currency']));
    }
}
